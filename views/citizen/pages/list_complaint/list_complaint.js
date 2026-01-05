let complaints = [];

export async function init(userData) {
  const listContainer = document.getElementById("complaintList");
  const modal = document.getElementById("complaintDetailModal");
  const closeBtn = document.getElementById("closeComplaintModal");
  const closeActionBtn = document.getElementById("closeComplaintBtn");

  if (!userData || !listContainer || !modal) return;

  // Modal close logic
  const closeModal = () => modal.classList.remove("active");
  if (closeBtn) closeBtn.addEventListener("click", closeModal);
  if (closeActionBtn) closeActionBtn.addEventListener("click", closeModal);

  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  try {
    // Note: Ensure this PHP endpoint exists or update the path
    const response = await fetch(
      `http://localhost/welfare/welfare/server/fetch_complaint.php?user_id=${userData.user_id}`
    );

    const result = await response.json();

    if (result.status !== "success") {
      listContainer.innerHTML = "<p>Failed to load complaints.</p>";
      return;
    }

    if (result.data.length === 0) {
      listContainer.innerHTML = "<p>No complaints found.</p>";
      return;
    }

    complaints = result.data;

    listContainer.innerHTML = complaints
      .map((comp) => {
        const date = new Date(comp.created_at).toLocaleDateString();

        return `
          <div class="list-item">
            <span>CMP-${comp.complaint_id}</span>
            <span>${formatText(comp.complaint_type)}</span>
            <span>${date}</span>
            <span class="status ${comp.status.toLowerCase()}">
              ${comp.status}
            </span>
            <div class="btn-container">
              <button class="view-btn" data-id="${comp.complaint_id}">
                View
              </button>
            </div>
          </div>
        `;
      })
      .join("");

    // Event delegation
    listContainer.addEventListener("click", (e) => {
      const btn = e.target.closest(".view-btn");
      if (!btn) return;

      const id = btn.dataset.id;
      const complaint = complaints.find(
        (c) => c.complaint_id.toString() === id
      );

      if (complaint) {
        openModal(complaint, modal);
      }
    });
  } catch (err) {
    console.error(err);
    listContainer.innerHTML = "<p>Server error.</p>";
  }
}

function openModal(complaint, modal) {
  modal.querySelector("#detailComplaintType").textContent = formatText(
    complaint.complaint_type
  );

  modal.querySelector("#detailComplaintDate").textContent = new Date(
    complaint.created_at
  ).toLocaleString();

  const statusEl = modal.querySelector("#detailComplaintStatus");
  statusEl.textContent = complaint.status;
  statusEl.className = `status-pill ${complaint.status.toLowerCase()}`;

  modal.querySelector("#detailComplaintDetails").textContent =
    complaint.complaint_details || "-";

  modal.classList.add("active");
}

function formatText(text) {
  return (
    text?.replace(/-/g, " ").replace(/\b\w/g, (c) => c.toUpperCase()) || "-"
  );
}
