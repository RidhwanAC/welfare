let applications = [];
let currentAppId = null;

export async function init(userData) {
  const listContainer = document.getElementById("applicationList");
  const modal = document.getElementById("detailModal");
  const closeBtn = document.getElementById("closeDetailModal");
  const closeActionBtn = document.getElementById("closeDetailBtn");
  const saveBtn = document.getElementById("saveStatusBtn");

  if (!userData || !listContainer) return;

  // --- MODAL CLOSING LOGIC ---
  const closeModal = () => modal.classList.remove("active");
  if (closeBtn) closeBtn.onclick = closeModal;
  if (closeActionBtn) closeActionBtn.onclick = closeModal;
  modal.onclick = (e) => {
    if (e.target === modal) closeModal();
  };

  const loadData = async () => {
    try {
      const url = `/welfare/welfare/server/fetch_welfare.php?user_id=${userData.user_id}&privilege=${userData.privilege}&district=${userData.district}&sub_district=${userData.sub_district}`;
      const response = await fetch(url);
      const result = await response.json();
      if (result.status === "success") {
        applications = result.data;
        renderList(listContainer);
      }
    } catch (err) {
      console.error(err);
    }
  };

  saveBtn.onclick = async () => {
    const newStatus = document.getElementById("updateStatusSelect").value;
    try {
      const res = await fetch("/welfare/welfare/server/update_welfare.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ welfare_id: currentAppId, status: newStatus }),
      });
      const data = await res.json();
      if (data.status === "success") {
        alert("Status Updated!");
        closeModal();
        loadData();
      }
    } catch (e) {
      alert("Update failed");
    }
  };

  loadData();

  listContainer.onclick = (e) => {
    const btn = e.target.closest(".view-btn");
    if (!btn) return;
    const app = applications.find((a) => a.welfare_id == btn.dataset.id);
    if (app) openModal(app, modal);
  };
}

// Inside renderList: Add APP- prefix
function renderList(container) {
  container.innerHTML = applications
    .map(
      (app) => `
    <div class="list-item">
        <span>APP-${app.welfare_id}</span>
        <span class="truncate"><b>${app.full_name}</b></span>
        <span>${app.aid_type.replace("-", " ")}</span>
        <span>${new Date(app.created_at).toLocaleDateString()}</span>
        <span class="status ${app.status.toLowerCase()}">${app.status}</span>
        <button class="view-btn" data-id="${app.welfare_id}">Manage</button>
    </div>
  `
    )
    .join("");
}

// Inside openModal: Fix dropdown default value and pill classes
function openModal(app, modal) {
  currentAppId = app.welfare_id;

  modal.querySelector("#detailName").textContent = app.full_name;
  modal.querySelector("#detailAidType").textContent = app.aid_type.replace(
    "-",
    " "
  );
  modal.querySelector("#detailStatus").textContent = app.status;

  // Refresh pill color
  const statusEl = modal.querySelector("#detailStatus");
  statusEl.className = `status-pill ${app.status.toLowerCase()}`;

  modal.querySelector("#detailRemarks").textContent =
    app.remarks || "No remarks provided.";

  // IMPORTANT: Set dropdown value TO MATCH current app status
  const select = modal.querySelector("#updateStatusSelect");
  if (select) {
    // Force lowercase to match <option value="pending"> etc
    select.value = app.status.toLowerCase();
  }

  modal.classList.add("active");
}
