(() => {
  const API_LIST = "/api/notifications";
  const API_READ_ALL = "/api/notifications/read-all";

  function escapeHtml(str) {
    return String(str).replace(
      /[&<>"']/g,
      (s) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#039;",
        }[s])
    );
  }

  async function fetchJSON(url, init) {
    const res = await fetch(url, {
      credentials: "same-origin",
      cache: "no-store",
      ...(init || {}),
    });
    if (!res.ok) throw new Error("HTTP " + res.status);
    return res.json();
  }

  function renderItem(n) {
    const dateStr = (() => {
      try {
        const date = new Date(n.createdAt);
        return date.toLocaleDateString(undefined, {
          year: "numeric",
          month: "short",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        });
      } catch {
        return "";
      }
    })();

    return `
    <li class="list-group-item list-group-item-action ${
      n.read ? "" : "bg-light"
    }">
      <a href="${n.link || "#"}" class="text-decoration-none text-dark d-block">
        <div class="small text-muted mb-1">${dateStr}</div>
        <div class="fw-semibold">${escapeHtml(n.title || "")}</div>
        ${
          n.body
            ? `<div class="text-muted small">${escapeHtml(n.body)}</div>`
            : ""
        }
      </a>
    </li>
  `;
  }

  async function refreshOneBell(wrapper) {
    const badge = wrapper.querySelector(".ea-notif-badge");
    const list = wrapper.querySelector(".ea-notif-list");
    if (!badge || !list) return;

    try {
      const json = await fetchJSON(API_LIST);
      const items = json.items || [];
      const unread = Number(json.unread || 0);

      badge.textContent = unread > 0 ? String(unread) : "";

      const footer = `
        <li><hr class="dropdown-divider"></li>
        <li><button class="dropdown-item text-start ea-notif-readall">Tout marquer comme lu</button></li>
      `;

      list.innerHTML = items.length
        ? items.map(renderItem).join("") + footer
        : `<li class="dropdown-item text-muted">Aucune notification</li>${footer}`;

      const btn = wrapper.querySelector(".ea-notif-readall");
      if (btn && !btn.dataset.bound) {
        btn.dataset.bound = "1";
        btn.addEventListener("click", async () => {
          try {
            await fetchJSON(API_READ_ALL, {
              method: "POST",
              headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            await refreshOneBell(wrapper);
          } catch (e) {
            console.error("[notif] read-all failed", e);
          }
        });
      }
    } catch (e) {
      console.error("[notif] fetch failed", e);
      list.innerHTML = `<li class="dropdown-item text-danger">Erreur de chargement</li>`;
      badge.textContent = "";
    }
  }

  function refreshAllBells() {
    document.querySelectorAll("[data-notif].ea-notif").forEach(refreshOneBell);
  }

  document.addEventListener("DOMContentLoaded", () => {
    // rafraîchir au chargement
    refreshAllBells();

    // rafraîchir quand on ouvre un dropdown (évite anciens contenus)
    document
      .querySelectorAll("[data-notif].ea-notif .ea-notif-button")
      .forEach((btn) =>
        btn.addEventListener("show.bs.dropdown", (e) => {
          const wrap = e.target.closest("[data-notif].ea-notif");
          if (wrap) refreshOneBell(wrap);
        })
      );

    // au focus + toutes les 60s
    window.addEventListener("focus", refreshAllBells);
    setInterval(refreshAllBells, 60000);
  });

  // Hook global depuis ton planning après CRUD
  window.addEventListener("notifications:refresh", refreshAllBells);
})();
