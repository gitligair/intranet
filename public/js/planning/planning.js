/***
 * PLANNING — version complète, stable et optimisée
 * - Plage 8h → 18h
 * - Empilement vertical (une ligne par tâche)
 * - Insertion / suppression DOM immédiate
 * - Hover + modal "view" en délégation
 * - Normalisation de date (YYYY-MM-DD) + gestion semaine affichée
 * - Affichage date dans le modal (#modalTaskDateInfo)
 * - Rechargement des utilisateurs au changement de pôle (création & édition)
 * - 🔒 Date fiable après changement de semaine (LAST_TARGET_DATE + alias clés)
 */

(function () {
  if (!window.PLANNING_CFG) {
    console.error("PLANNING_CFG manquant.");
    return;
  }

  // --- CONSTANTES JOUR 8→18 ---------------------------------------
  const DAY_START_MIN = 8 * 60;
  const DAY_END_MIN = 18 * 60;
  const DAY_SPAN_MIN = DAY_END_MIN - DAY_START_MIN;

  function clamp(v, a, b) {
    return Math.min(b, Math.max(a, v));
  }

  function timeToMin(hhmm) {
    const [h, m] = (hhmm || "00:00")
      .split(":")
      .map((x) => parseInt(x, 10) || 0);
    return h * 60 + m;
  }
  function toRelative(absMin) {
    return clamp(absMin - DAY_START_MIN, 0, DAY_SPAN_MIN);
  }
  function computeLeftWidth(hD, hF) {
    const sAbs = timeToMin(hD || "08:00");
    const eAbs = timeToMin(hF || hD || "08:00");
    const s = clamp(Math.min(sAbs, eAbs), DAY_START_MIN, DAY_END_MIN);
    const e = clamp(Math.max(sAbs, eAbs), DAY_START_MIN, DAY_END_MIN);
    const rs = toRelative(s);
    const re = toRelative(e);
    return {
      leftPct: (rs / DAY_SPAN_MIN) * 100,
      widthPct: (Math.max(5, re - rs) / DAY_SPAN_MIN) * 100,
    };
  }

  // --- LAYOUT VERTICAL (une ligne par tâche) ----------------------
  const ROW_H = 26; // px
  const ROW_G = 4; // px

  function parseAbsMin(hhmm) {
    const [h, m] = (hhmm || "00:00")
      .split(":")
      .map((v) => parseInt(v, 10) || 0);
    return h * 60 + m;
  }
  function relFromHHMM(hhmm) {
    return clamp(parseAbsMin(hhmm) - DAY_START_MIN, 0, DAY_SPAN_MIN);
  }
  function layoutCellRows(cell) {
    const track = cell.querySelector(".timeline-track") || cell;
    const items = Array.from(track.querySelectorAll(".task-block-h"));
    if (items.length === 0) {
      track.style.height = "";
      return;
    }

    const intervals = items
      .map((el) => {
        const d = el.dataset.debut || "08:00";
        const f = el.dataset.fin || d;
        const s = relFromHHMM(d);
        const e = Math.max(s, relFromHHMM(f));
        return { el, start: s, end: e, row: -1 };
      })
      .sort((a, b) => a.start - b.start || a.end - b.end);

    const rows = [];
    for (const it of intervals) {
      let placed = false;
      for (let r = 0; r < rows.length; r++) {
        if (it.start >= rows[r]) {
          it.row = r;
          rows[r] = it.end;
          placed = true;
          break;
        }
      }
      if (!placed) {
        it.row = rows.length;
        rows.push(it.end);
      }
    }

    intervals.forEach(({ el, row }) => {
      el.style.top = row * (ROW_H + ROW_G) + "px";
      el.style.height = ROW_H + "px";
    });

    const totalH = rows.length * ROW_H + Math.max(0, rows.length - 1) * ROW_G;
    track.style.position = "relative";
    track.style.height = totalH + "px";
  }
  function layoutAllCells() {
    document.querySelectorAll(".timeline-cell").forEach(layoutCellRows);
  }

  // --- DATES : normalisation + semaine visible --------------------
  function normalizeDateYMD(d) {
    if (!d) return "";
    if (/^\d{4}-\d{2}-\d{2}$/.test(d)) return d;
    if (d.includes("T")) return d.split("T")[0];
    const parts = d.replace(/[.]/g, "/").split(/[\/\-]/);
    if (parts.length === 3) {
      if (parts[0].length === 4) {
        const [Y, M, D] = parts;
        return `${Y.padStart(4, "0")}-${M.padStart(2, "0")}-${D.padStart(
          2,
          "0"
        )}`;
      } else {
        const [D, M, Y] = parts;
        return `${String(Y).padStart(4, "0")}-${String(M).padStart(
          2,
          "0"
        )}-${String(D).padStart(2, "0")}`;
      }
    }
    return d;
  }
  function formatDateFr(ymd) {
    if (!ymd) return "—";
    const [Y, M, D] = ymd.split("-");
    return `${D}/${M}/${Y}`;
  }

  let VISIBLE_DATES = new Set();
  function rebuildVisibleDates() {
    VISIBLE_DATES = new Set(
      Array.from(document.querySelectorAll(".timeline-cell"))
        .map((c) => normalizeDateYMD(c.dataset.date))
        .filter(Boolean)
    );
  }

  // 🔒 Date ciblée (YYYY-MM-DD) mémorisée lors de l’ouverture du modal
  let LAST_TARGET_DATE = "";

  // --- CONFIG ------------------------------------------------------
  const CFG = window.PLANNING_CFG;
  const routes = CFG.routes || {};
  const CURRENT_USER_ID = parseInt(CFG.CURRENT_USER_ID ?? 0, 10);
  const poleColors = CFG.poleColors || {};
  const hasCSRF = !!CFG.csrfToken;

  // --- MODALS ------------------------------------------------------
  let taskModal = null;
  let viewModal = null;

  function getTaskModal() {
    if (!taskModal) {
      const el = document.getElementById("taskModal");
      if (el) taskModal = bootstrap.Modal.getOrCreateInstance(el);
    }
    return taskModal;
  }
  function getViewModal() {
    if (!viewModal) {
      const el = document.getElementById("taskViewModal");
      if (el) viewModal = bootstrap.Modal.getOrCreateInstance(el);
    }
    return viewModal;
  }

  // Maj du petit affichage de date dans le modal d’édition/création
  function updateModalDateInfo(ymd) {
    const span = document.getElementById("modalTaskDateInfo");
    if (span) span.textContent = formatDateFr(ymd);
  }

  // --- ROUTES & ALERTES -------------------------------------------
  const routeUpdate = (id) => (routes.updateTmpl || "").replace("REPLACE", id);
  const routeDelete = (id) => (routes.deleteTmpl || "").replace("REPLACE", id);

  function showAlert(msg, type = "success") {
    const c = document.getElementById("alert-container");
    if (!c) return;
    const el = document.createElement("div");
    el.className = `alert alert-${type} alert-dismissible fade show shadow-lg`;
    el.innerHTML = `
      ${msg}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    c.appendChild(el);
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(el).close(), 4000);
  }

  // --- DOM helpers : upsert/remove --------------------------------
  function buildTaskElement(task) {
    const { leftPct, widthPct } = computeLeftWidth(
      task.heureDebut,
      task.heureFin
    );
    const el = document.createElement("div");
    el.className = "task-block-h";

    el.dataset.id = String(task.id);
    el.dataset.titre = task.titre || "";
    el.dataset.description = task.description || "";
    el.dataset.pole = task.pole || "";
    el.dataset.importance = task.importance || "";
    el.dataset.deadline = task.deadline || "";
    el.dataset.debut = task.heureDebut || "";
    el.dataset.fin = task.heureFin || "";
    el.dataset.usersnames = task.usersnames || "";
    el.dataset.createdby = String(task.createdby ?? CURRENT_USER_ID);

    el.style.position = "absolute";
    el.style.left = leftPct + "%";
    el.style.width = widthPct + "%";

    // Couleur immédiate (selon pole)
    let bg = task.color || task.poleColor;
    if (!bg && task.pole_id && poleColors[task.pole_id])
      bg = poleColors[task.pole_id];
    if (!bg && task.pole && poleColors[task.pole]) bg = poleColors[task.pole];
    if (bg) el.style.background = bg;

    el.innerHTML = `<div class="task-inner px-1 text-truncate"><strong>${task.titre}</strong></div>`;
    return el;
  }

  function upsertTaskDOM(task) {
    const ymd = normalizeDateYMD(task.date);
    if (!ymd) return;

    if (VISIBLE_DATES.size && !VISIBLE_DATES.has(ymd)) {
      console.warn(
        "[planning] Tâche hors semaine affichée :",
        ymd,
        "— insertion DOM ignorée."
      );
      return;
    }

    document
      .querySelectorAll(`.task-block-h[data-id="${task.id}"]`)
      .forEach((n) => n.remove());

    const userIds = Array.isArray(task.assignes)
      ? task.assignes
      : typeof task.assignes === "string"
      ? task.assignes.split(",").filter(Boolean)
      : [];

    if (userIds.length === 0) return;

    userIds.forEach((uid) => {
      const cell = document.querySelector(
        `.timeline-cell[data-date="${ymd}"][data-user="${uid}"]`
      );
      if (!cell) return;

      let track = cell.querySelector(".timeline-track");
      if (!track) {
        track = document.createElement("div");
        track.className = "timeline-track";
        track.style.position = "relative";
        track.style.width = "100%";
        cell.appendChild(track);
      }

      // Rapatrier d'éventuelles tâches existantes dans la piste
      cell.querySelectorAll(".task-block-h").forEach((el) => {
        if (el.parentNode !== track) track.appendChild(el);
      });

      const el = buildTaskElement(task);
      track.appendChild(el);

      layoutCellRows(cell);
    });
  }

  function removeTaskFromDOM(taskId) {
    const impacted = new Set();
    document
      .querySelectorAll(`.task-block-h[data-id="${taskId}"]`)
      .forEach((n) => {
        const cell = n.closest(".timeline-cell");
        if (cell) impacted.add(cell);
        n.remove();
      });
    impacted.forEach((cell) => layoutCellRows(cell));
  }

  // --- HOVER (DELEGATION) -----------------------------------------
  function initHoverDelegation() {
    if (document.__hoverBound) return;
    document.__hoverBound = true;

    let tip = null,
      target = null;

    document.addEventListener("mouseover", (e) => {
      const t = e.target.closest(".task-block-h");
      if (!t) return;
      target = t;
      tip = document.createElement("div");
      tip.className = "task-tooltip";
      tip.innerHTML = `
        <strong>${t.dataset.titre || ""}</strong><br>
        ${t.dataset.debut} → ${t.dataset.fin}<br>
        <small>Pôle : ${t.dataset.pole}</small><br>
        <small>Importance : ${t.dataset.importance}</small><br>
        <small>Assignés : ${t.dataset.usersnames || "—"}</small>
      `;
      document.body.appendChild(tip);
    });

    document.addEventListener("mousemove", (e) => {
      if (!tip) return;
      tip.style.left = e.pageX + 15 + "px";
      tip.style.top = e.pageY + 15 + "px";
    });

    document.addEventListener("mouseout", (e) => {
      const leaving = e.target.closest(".task-block-h");
      if (leaving && leaving === target) {
        tip?.remove();
        tip = null;
        target = null;
      }
    });
  }

  // --- MODAL VIEW (DELEGATION) ------------------------------------
  function initTaskViewModal() {
    if (document.__viewBound) return;
    document.__viewBound = true;

    document.addEventListener("click", (e) => {
      const t = e.target.closest(".task-block-h");
      if (!t) return;

      getViewModal();

      const safe = (id) =>
        document.getElementById(id) ?? { textContent: "", innerHTML: "" };
      safe("viewTitre").textContent = t.dataset.titre || "";
      safe("viewDescription").textContent = t.dataset.description || "";
      safe("viewPole").textContent = t.dataset.pole || "";
      safe("viewHoraire").textContent = `${t.dataset.debut} → ${t.dataset.fin}`;
      safe("viewDeadline").textContent = t.dataset.deadline || "Aucune";
      safe("viewImportance").textContent = (
        t.dataset.importance || ""
      ).toUpperCase();

      const names = t.dataset.usersnames || "";
      safe("viewAssignes").innerHTML = names
        .split(",")
        .filter(Boolean)
        .map((n) => `<span class="badge bg-secondary me-1">${n}</span>`)
        .join("");

      const createdBy = parseInt(t.dataset.createdby || "0", 10);
      const editBtn = document.getElementById("viewEditBtn");
      const delBtn = document.getElementById("viewDeleteBtn");

      if (createdBy === (window.PLANNING_CFG?.CURRENT_USER_ID | 0)) {
        editBtn.classList.remove("d-none");
        delBtn.classList.remove("d-none");
      } else {
        editBtn.classList.add("d-none");
        delBtn.classList.add("d-none");
      }

      editBtn.onclick = () => {
        getViewModal()?.hide();

        const taskData = {
          id: t.dataset.id,
          titre: t.dataset.titre,
          description: t.dataset.description,
          date: t.dataset.dateexecution, // attention : il te faut data-dateexecution
          heureDebut: t.dataset.debut,
          heureFin: t.dataset.fin,
          poleId: t.dataset.poleid,
          importance: t.dataset.importance,
          assignes: t.dataset.users?.split(",") || [],
        };

        openEditModal(taskData);
      };
      delBtn.onclick = () => {
        getViewModal()?.hide();
        document
          .querySelector(`.delete-task-btn[data-id="${t.dataset.id}"]`)
          ?.click();
      };

      getViewModal()?.show();
    });
  }

  // --- PLANNING EVENTS --------------------------------------------
  function initPlanningEvents() {
    const form = document.getElementById("taskForm");
    const modalEl = document.getElementById("taskModal");
    if (!modalEl || !form) return;

    getTaskModal();

    const modalTitle = document.getElementById("modalTitle");
    const taskIdInput = document.getElementById("taskId");
    const taskDateInput = document.getElementById("taskDate");
    const assignSelect = document.getElementById("assignesSelect");
    const poleSelect = form.querySelector('[name="pole"]');
    const warning = document.getElementById("assignWarning");

    // --- Charger utilisateurs du pôle (avec pré-sélection éventuelle)
    async function loadUsersForPole(poleId, selectedIds = []) {
      const headers = { "Content-Type": "application/json" };
      if (hasCSRF) headers["X-CSRF-TOKEN"] = CFG.csrfToken;

      const res = await fetch(routes.usersByPole, {
        method: "POST",
        headers,
        body: JSON.stringify({ pole_id: poleId }),
      });

      const json = await res.json();
      assignSelect.innerHTML = "";

      const toSelect = new Set(
        Array.isArray(selectedIds)
          ? selectedIds.map(String)
          : String(selectedIds || "")
              .split(",")
              .filter(Boolean)
      );

      if (json.users) {
        json.users.forEach((u) => {
          const opt = document.createElement("option");
          opt.value = String(u.id);
          opt.textContent = u.name;
          if (toSelect.has(opt.value)) opt.selected = true;
          assignSelect.appendChild(opt);
        });
      }
    }

    // --- Dispos
    async function checkDisponibilites() {
      const date = taskDateInput.value;
      const heureDebut = form.querySelector('[name="heureDebut"]').value;
      const heureFin = form.querySelector('[name="heureFin"]').value;
      const assignes = Array.from(assignSelect.selectedOptions).map(
        (o) => o.value
      );
      if (!date || !heureDebut || !heureFin || assignes.length === 0) return;

      const headers = { "Content-Type": "application/json" };
      if (hasCSRF) headers["X-CSRF-TOKEN"] = CFG.csrfToken;

      const res = await fetch(routes.checkDispo, {
        method: "POST",
        headers,
        body: JSON.stringify({ date, heureDebut, heureFin, assignes }),
      });
      const json = await res.json();

      assignSelect.querySelectorAll("option").forEach((o) => {
        o.style.color = "";
        o.style.backgroundColor = "";
      });

      if (json.conflicts?.length > 0) {
        warning?.classList.remove("d-none");
        if (warning)
          warning.innerHTML = `⚠️ Conflits : ${json.conflicts
            .map((c) => c.user)
            .join(", ")}`;
        assignSelect.classList.add("is-invalid");
        json.conflicts.forEach((c) => {
          const o = assignSelect.querySelector(`option[value="${c.user_id}"]`);
          if (o) {
            o.style.color = "white";
            o.style.backgroundColor = "#dc3545";
          }
        });
      } else {
        warning?.classList.add("d-none");
        if (warning) warning.innerHTML = "";
        assignSelect.classList.remove("is-invalid");
      }
    }

    // --- Changement de pôle dans le modal → recharge la liste
    if (!poleSelect.dataset.boundChange) {
      poleSelect.dataset.boundChange = "1";
      poleSelect.addEventListener("change", async () => {
        await loadUsersForPole(poleSelect.value);
        // Optionnel : check dispos
        // await checkDisponibilites();
      });
    }

    // CLIC SUR CASE VIDE → créer
    document.querySelectorAll(".timeline-cell").forEach((cell) => {
      if (cell.dataset.boundClick === "1") return;
      cell.dataset.boundClick = "1";

      cell.addEventListener("click", async (e) => {
        if (e.target.closest(".task-block-h")) return;

        const rawDate = cell.dataset.date;
        const ymd = normalizeDateYMD(rawDate);
        LAST_TARGET_DATE = ymd; // 🔒 mémorise la date cible

        const userId = cell.dataset.user;

        const track = cell.querySelector(".timeline-track") || cell;
        const rect = track.getBoundingClientRect();
        const px = e.clientX - rect.left;
        const pct = clamp(px / rect.width, 0, 1);
        const mins = pct * DAY_SPAN_MIN;
        let h = 8 + Math.floor(mins / 60);
        let m = Math.floor(mins % 60);
        if (h < 8) h = 8;
        if (h > 18) {
          h = 18;
          m = 0;
        }
        const debut = `${String(h).padStart(2, "0")}:${String(m).padStart(
          2,
          "0"
        )}`;

        form.reset();
        taskIdInput.value = "";
        taskDateInput.value = ymd;
        updateModalDateInfo(ymd);
        form.querySelector('[name="heureDebut"]').value = debut;

        await loadUsersForPole(poleSelect.value, [userId]);

        modalTitle.textContent = "Nouvelle tâche";
        getTaskModal()?.show();
      });
    });

    // AJOUTER via bouton
    document.querySelectorAll(".add-task-btn").forEach((btn) => {
      if (btn.dataset.boundClick === "1") return;
      btn.dataset.boundClick = "1";

      btn.addEventListener("click", async () => {
        const ymd = normalizeDateYMD(btn.dataset.date);
        LAST_TARGET_DATE = ymd; // 🔒 mémorise

        modalTitle.textContent = "Nouvelle tâche";
        form.reset();
        taskIdInput.value = "";
        taskDateInput.value = ymd;
        updateModalDateInfo(ymd);
        await loadUsersForPole(poleSelect.value);
        getTaskModal()?.show();
      });
    });

    // MODIFIER
    document.querySelectorAll(".edit-task-btn").forEach((btn) => {
      if (btn.dataset.boundClick === "1") return;
      btn.dataset.boundClick = "1";

      btn.addEventListener("click", async () => {
        modalTitle.textContent = "Modifier la tâche";

        const ymd = normalizeDateYMD(btn.dataset.dateexecution);
        LAST_TARGET_DATE = ymd; // 🔒 mémorise

        taskIdInput.value = btn.dataset.id;
        taskDateInput.value = ymd;
        updateModalDateInfo(ymd);

        form.querySelector('[name="titre"]').value = btn.dataset.titre || "";
        form.querySelector('[name="description"]').value =
          btn.dataset.description || "";
        form.querySelector('[name="importance"]').value =
          btn.dataset.importance || "";
        form.querySelector('[name="deadline"]').value =
          btn.dataset.deadline || "";
        form.querySelector('[name="heureDebut"]').value =
          btn.dataset.heuredebut || "";
        form.querySelector('[name="heureFin"]').value =
          btn.dataset.heurefin || "";

        const poleId = btn.dataset.pole;
        const assigned =
          btn.dataset.assignera?.split(",").filter(Boolean) || [];
        await loadUsersForPole(poleId, assigned);
        const poleSelectEl = form.querySelector('[name="pole"]');
        if (poleSelectEl) poleSelectEl.value = poleId;

        getTaskModal()?.show();
      });
    });

    // SUPPRIMER
    document.querySelectorAll(".delete-task-btn").forEach((btn) => {
      if (btn.dataset.boundClick === "1") return;
      btn.dataset.boundClick = "1";

      btn.addEventListener("click", async () => {
        if (!confirm("Supprimer cette tâche ?")) return;
        const id = btn.dataset.id;

        const headers = {};
        if (hasCSRF) headers["X-CSRF-TOKEN"] = CFG.csrfToken;

        const res = await fetch(routeDelete(id), { method: "DELETE", headers });
        const json = await res.json();

        if (res.ok) {
          showAlert("Tâche supprimée avec succès");
          window.dispatchEvent(new CustomEvent("notifications:refresh"));

          removeTaskFromDOM(id);
        } else {
          showAlert(json.message || "Erreur inconnue", "danger");
        }
      });
    });

    // Écouteurs dispo
    ["change", "blur"].forEach((evt) => {
      assignSelect.addEventListener(evt, checkDisponibilites);
      form
        .querySelector('[name="heureDebut"]')
        .addEventListener(evt, checkDisponibilites);
      form
        .querySelector('[name="heureFin"]')
        .addEventListener(evt, checkDisponibilites);
    });

    // SUBMIT (AJOUT/MODIF)
    form.onsubmit = async (e) => {
      e.preventDefault();

      const id = taskIdInput.value;

      // 🔒 Date fiable : input normalisé OU dernière date ciblée
      const formDate =
        normalizeDateYMD(taskDateInput.value) || LAST_TARGET_DATE;

      const data = {
        pole_id: form.querySelector('[name="pole"]').value,
        titre: form.querySelector('[name="titre"]').value,
        description: form.querySelector('[name="description"]').value,
        importance: form.querySelector('[name="importance"]').value,
        deadline: form.querySelector('[name="deadline"]').value,

        // 👉 on envoie tous les alias possibles pour le backend
        date: formDate,
        dateExecution: formDate,
        date_execution: formDate,

        assignes: Array.from(assignSelect.selectedOptions).map((o) => o.value),
        heureDebut: form.querySelector('[name="heureDebut"]').value,
        heureFin: form.querySelector('[name="heureFin"]').value,
      };

      const headers = { "Content-Type": "application/json" };
      if (hasCSRF) headers["X-CSRF-TOKEN"] = CFG.csrfToken;

      const res = await fetch(id ? routeUpdate(id) : routes.save, {
        method: "POST",
        headers,
        body: JSON.stringify(data),
      });
      const json = await res.json();

      if (res.ok) {
        let task = json.task || {
          id: json.id || id || Date.now(),
          titre: data.titre,
          description: data.description,
          pole: data.pole_id,
          pole_id: data.pole_id,
          importance: data.importance,
          deadline: data.deadline,
          date: formDate, // 👈 garde la date du form
          assignes: data.assignes,
          heureDebut: data.heureDebut,
          heureFin: data.heureFin,
          usersnames: Array.from(assignSelect.selectedOptions)
            .map((o) => o.textContent)
            .join(","),
          createdby: CURRENT_USER_ID,
        };
        if (typeof task.assignes === "string")
          task.assignes = task.assignes.split(",").filter(Boolean);
        task.date = formDate; // 👈 force ici aussi

        getTaskModal()?.hide();
        showAlert(id ? "Tâche mise à jour" : "Tâche ajoutée");
        window.dispatchEvent(new CustomEvent("notifications:refresh"));

        upsertTaskDOM(task);
        // hover & modal view → déjà en délégation
      } else {
        getTaskModal()?.hide();
        showAlert(json.message || "Erreur inconnue", "danger");
      }
    };
  }

  // --- PARTIELS & SEMAINE -----------------------------------------
  async function reloadPlanning() {
    const container = document.getElementById("planning_container");
    if (!container) return;
    const res = await fetch(routes.partial, { cache: "no-store" });
    const html = await res.text();
    container.innerHTML = html;
    rebuildVisibleDates();
    rebind();
  }

  window.changeWeek = async function (delta) {
    const inp = document.getElementById("weekOffset");
    if (!inp) return;
    const newOffset = parseInt(inp.value || "0", 10) + delta;
    inp.value = newOffset;

    const url =
      routes.timelineWeek + "?week_offset=" + newOffset + "&_ts=" + Date.now();
    const res = await fetch(url, { cache: "no-store" });
    const html = await res.text();

    const cont = document.getElementById("timeline_container");
    if (cont) {
      cont.innerHTML = html;
      rebuildVisibleDates();
      rebind();
    }
  };

  function openEditModal(task) {
    const form = document.getElementById("taskForm");

    // Active le mode édition
    form.querySelector('[name="id"]').value = task.id;
    form.querySelector('[name="titre"]').value = task.titre;
    form.querySelector('[name="description"]').value = task.description;
    form.querySelector('[name="dateExecution"]').value = task.date;
    form.querySelector('[name="heureDebut"]').value = task.heureDebut;
    form.querySelector('[name="heureFin"]').value = task.heureFin;
    form.querySelector('[name="pole"]').value = task.poleId;
    form.querySelector('[name="importance"]').value = task.importance;

    // Pour assigner les utilisateurs
    const select = form.querySelector('[name="assignerA[]"]');
    const assignes = task.assignes || [];
    for (const opt of select.options) {
      opt.selected = assignes.includes(opt.value);
    }

    // Modifier le titre du modal
    document.getElementById("formModalTitle").textContent = "Modifier la tâche";

    // Afficher le modal
    const modal = new bootstrap.Modal(document.getElementById("taskFormModal"));
    modal.show();
  }

  // --- REBIND GLOBAL ----------------------------------------------
  function rebind() {
    initPlanningEvents();
    layoutAllCells();
  }

  /* ===================== CLÔCHE NOTIFICATIONS (VANILLA) ===================== */
  /* Dépendances : Bootstrap déjà chargé par EasyAdmin (pour le dropdown)      */
  /* HTML attendu dans ton layout :                                             *
<div class="dropdown">
  <button id="ea-notif-button" class="btn btn-link position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
    <!-- ton icône ici -->
    <span id="ea-notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"></span>
  </button>
  <ul id="ea-notif-list" class="dropdown-menu dropdown-menu-end" style="min-width:320px;max-height:60vh;overflow:auto;">
    <li class="dropdown-item text-muted">Chargement…</li>
    <li><hr class="dropdown-divider"></li>
    <li><button id="ea-notif-readall" class="dropdown-item text-start">Tout marquer comme lu</button></li>
  </ul>
</div>
* ************************************************************************** */

  // --- BOOT --------------------------------------------------------
  document.addEventListener("DOMContentLoaded", () => {
    rebuildVisibleDates();
    rebind();
    initHoverDelegation();
    initTaskViewModal();
  });
})();
