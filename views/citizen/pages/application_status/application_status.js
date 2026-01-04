let applications = [];

export async function init(userData) {
  const listContainer = document.getElementById("applicationList");
  const modal = document.getElementById("detailModal");
  const closeBtn = document.getElementById("closeDetailModal");
  const closeActionBtn = document.getElementById("closeDetailBtn");

  if (!userData || !listContainer || !modal) return;

  // Close modal logic (same pattern as your submit modal)
  const closeModal = () => modal.classList.remove("active");
  if (closeBtn) closeBtn.addEventListener("click", closeModal);
  if (closeActionBtn) closeActionBtn.addEventListener("click", closeModal);

  // Optional: click overlay to close
  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  try {
    const response = await fetch(
      `http://localhost/welfare/welfare/server/fetch_welfare.php?user_id=${userData.user_id}`
    );

    const result = await response.json();

    if (result.status !== "success") {
      listContainer.innerHTML = "<p>Failed to load applications.</p>";
      return;
    }

    if (result.data.length === 0) {
      listContainer.innerHTML = "<p>No applications found.</p>";
      return;
    }

    applications = result.data;

    listContainer.innerHTML = applications
      .map((app) => {
        const date = new Date(app.created_at).toLocaleDateString();

        return `
          <div class="list-item">
            <span>APP-${app.welfare_id}</span>
            <span>${formatAidType(app.aid_type)}</span>
            <span>${date}</span>
            <span class="status ${app.status.toLowerCase()}">
              ${app.status}
            </span>
            <div class="btn-container">
              <button class="view-btn" data-id="${app.welfare_id}">
                View
              </button>
            </div>
          </div>
        `;
      })
      .join("");

    // Event delegation for view buttons
    listContainer.addEventListener("click", (e) => {
      const btn = e.target.closest(".view-btn");
      if (!btn) return;

      const id = btn.dataset.id;
      const app = applications.find((a) => a.welfare_id.toString() === id);

      if (!app) return;

      openModal(app, modal);
    });
  } catch (err) {
    console.error(err);
    listContainer.innerHTML = "<p>Server error.</p>";
  }
}

function openModal(app, modal) {
  // Scope ALL queries to modal
  modal.querySelector("#detailAidType").textContent = formatAidType(
    app.aid_type
  );

  modal.querySelector("#detailCategory").textContent = formatAidType(
    app.welfare_category
  );

  modal.querySelector("#detailDate").textContent = new Date(
    app.created_at
  ).toLocaleString();

  const statusEl = modal.querySelector("#detailStatus");
  statusEl.textContent = app.status;
  statusEl.className = `status-pill ${app.status.toLowerCase()}`;

  modal.querySelector("#detailRemarks").textContent = app.remarks || "-";

  modal.classList.add("active");
}

function formatAidType(type) {
  return (
    type?.replace("-", " ").replace(/\b\w/g, (c) => c.toUpperCase()) || "-"
  );
}
