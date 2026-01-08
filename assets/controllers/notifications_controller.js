import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    this.badge = document.querySelector("#ea-notif-badge");
    this.list = document.querySelector("#ea-notif-list");

    this.refresh = this.refresh.bind(this);
    this.startPolling();

    window.addEventListener("focus", this.refresh);
    window.addEventListener("notifications:refresh", this.refresh);

    this.refresh();
  }

  disconnect() {
    this.stopPolling();
    window.removeEventListener("focus", this.refresh);
    window.removeEventListener("notifications:refresh", this.refresh);
  }

  startPolling() {
    if (!this.poll) this.poll = setInterval(this.refresh, 15000);
  }
  stopPolling() {
    if (this.poll) clearInterval(this.poll), (this.poll = null);
  }

  async refresh() {
    try {
      const res = await fetch("/api/notifications", {
        credentials: "same-origin",
      });
      const json = await res.json();
      if (this.badge) this.badge.textContent = json.unread || "";
      if (this.list) {
        this.list.innerHTML =
          (json.items || []).map(this.renderItem).join("") ||
          '<li class="dropdown-item text-muted">Aucune notification</li>';
      }
    } catch {}
  }

  renderItem(n) {
    const when = new Date(n.createdAt).toLocaleString();
    return `
      <li>
        <a class="dropdown-item ${n.read ? "" : "fw-bold"}" href="${
      n.link || "#"
    }">
          <div class="small text-muted">${when}</div>
          <div>${n.title}</div>
          ${n.body ? `<div class="text-muted small">${n.body}</div>` : ""}
        </a>
      </li>`;
  }
}
