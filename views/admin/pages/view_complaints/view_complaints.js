let complaints = [];
let currentComplaintId = null;

export async function init(userData) {
  const listContainer = document.getElementById("complaintList");
  const modal = document.getElementById("detailModal");
  const closeBtn = document.getElementById("closeDetailModal");
  const closeActionBtn = document.getElementById("closeDetailBtn");
  const saveBtn = document.getElementById("saveStatusBtn");

  if (!userData || !listContainer) return;

  const closeModal = () => modal.classList.remove("active");
  if (closeBtn) closeBtn.onclick = closeModal;
  if (closeActionBtn) closeActionBtn.onclick = closeModal;
  modal.onclick = (e) => {
    if (e.target === modal) closeModal();
  };

  const loadData = async () => {
    try {
      // Updated URL for complaints
      const url = `/welfare/welfare/server/fetch_complaint.php?user_id=${userData.user_id}&privilege=${userData.privilege}&district=${userData.district}&sub_district=${userData.sub_district}`;
      const response = await fetch(url);
      const result = await response.json();
      if (result.status === "success") {
        complaints = result.data;
        renderList(listContainer);
      }
    } catch (err) {
      console.error(err);
    }
  };

  saveBtn.onclick = async () => {
    const newStatus = document.getElementById("updateStatusSelect").value;
    try {
      const res = await fetch("/welfare/welfare/server/update_complaint.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          complaint_id: currentComplaintId,
          status: newStatus,
        }),
      });
      const data = await res.json();
      if (data.status === "success") {
        alert("Complaint Status Updated!");
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
    const complaint = complaints.find((c) => c.complaint_id == btn.dataset.id);
    if (complaint) openModal(complaint, modal);
  };
}

function renderList(container) {
  container.innerHTML = complaints
    .map(
      (cmp) => `
    <div class="list-item">
        <span>CMP-${cmp.complaint_id}</span>
        <span class="truncate"><b>${cmp.full_name}</b></span>
        <span>${new Date(cmp.created_at).toLocaleDateString()}</span>
        <span class="status ${cmp.status.toLowerCase()}">${cmp.status}</span>
        <button class="view-btn" data-id="${cmp.complaint_id}">Manage</button>
    </div>
  `
    )
    .join("");
}

function openModal(cmp, modal) {
  currentComplaintId = cmp.complaint_id;

  modal.querySelector("#detailName").textContent = cmp.full_name;
  modal.querySelector("#detailStatus").textContent = cmp.status;

  const statusEl = modal.querySelector("#detailStatus");
  statusEl.className = `status-pill ${cmp.status.toLowerCase()}`;

  // Using complaint_details field
  modal.querySelector("#detailRemarks").textContent =
    cmp.complaint_details || "No details provided.";

  const select = modal.querySelector("#updateStatusSelect");
  if (select) {
    select.value = cmp.status.toLowerCase();
  }

  modal.classList.add("active");
}
