let citizens = [];
let selectedUserId = null;

export async function init(userData) {
  const listContainer = document.getElementById("citizenList");
  const modal = document.getElementById("aidModal");
  const closeBtn = document.getElementById("closeAidModal");
  const cancelBtn = document.getElementById("cancelAidBtn");
  const aidForm = document.getElementById("aidForm");

  if (!userData || !listContainer) return;

  const closeModal = () => {
    modal.classList.remove("active");
    aidForm.reset();
  };

  if (closeBtn) closeBtn.onclick = closeModal;
  if (cancelBtn) cancelBtn.onclick = closeModal;

  const loadCitizens = async () => {
    try {
      const url = `/welfare/welfare/server/fetch_citizen.php?privilege=${userData.privilege}&district=${userData.district}&sub_district=${userData.sub_district}`;
      const response = await fetch(url);
      const result = await response.json();
      if (result.status === "success") {
        citizens = result.data;
        renderList(listContainer);
      }
    } catch (err) {
      console.error("Fetch error:", err);
    }
  };

  aidForm.onsubmit = async (e) => {
    e.preventDefault();
    const amount = document.getElementById("aidAmount").value;
    const remark = document.getElementById("aidRemark").value;

    try {
      const res = await fetch("/welfare/welfare/server/submit_aid.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          user_id: selectedUserId,
          amount: amount,
          aid_remark: remark,
        }),
      });
      const data = await res.json();
      if (data.status === "success") {
        alert("Aid successfully distributed!");
        closeModal();
      } else {
        alert("Error: " + data.message);
      }
    } catch (e) {
      alert("Submission failed");
    }
  };

  loadCitizens();

  listContainer.onclick = (e) => {
    const btn = e.target.closest(".view-btn");
    if (!btn) return;
    const citizen = citizens.find((c) => c.user_id == btn.dataset.id);
    if (citizen) openModal(citizen, modal);
  };
}

const toTitleCase = (str) => {
  return str
    ? str
        .toLowerCase()
        .split(" ")
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(" ")
    : "";
};

function renderList(container) {
  container.innerHTML = citizens
    .map(
      (c) => `
    <div class="list-item">
        <span class="truncate"><b>${c.full_name}</b></span>
        <span>${toTitleCase(c.district)}</span>
        <span>${toTitleCase(c.sub_district)}</span>
        <button class="view-btn" data-id="${c.user_id}">Distribute</button>
    </div>
  `
    )
    .join("");
}

async function openModal(citizen, modal) {
  selectedUserId = citizen.user_id;
  document.getElementById("recipientName").textContent = citizen.full_name;
  document.getElementById("recipientId").textContent = citizen.user_id;

  await loadAidHistory(citizen.user_id);
  modal.classList.add("active");
}

async function loadAidHistory(userId) {
  const historyList = document.getElementById("aidHistoryList");
  historyList.innerHTML = "<p>Loading history...</p>";

  try {
    const res = await fetch(
      `/welfare/welfare/server/fetch_aid_history.php?user_id=${userId}`
    );
    const result = await res.json();

    if (result.status === "success" && result.data.length > 0) {
      const total = result.data.reduce(
        (sum, item) => sum + parseFloat(item.amount),
        0
      );

      const totalBadge = `
        <div style="background: #e8f5e9; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; border: 1px solid #c8e6c9;">
          <span style="font-size: 0.8rem; color: #4caf50; font-weight: bold; text-transform: uppercase;">Total Distributed</span><br>
          <strong style="font-size: 1.2rem; color: #2e7d32;">RM ${total.toFixed(
            2
          )}</strong>
        </div>`;

      const listHtml = result.data
        .map(
          (item) => `
        <div class="history-item">
            <div class="history-meta">
              <span>${new Date(item.created_at).toLocaleDateString(
                "en-GB"
              )}</span>
              <span style="margin-left: 10px;">ID: ${item.aid_id}</span>
            </div>
            <span class="amt">RM ${parseFloat(item.amount).toFixed(2)}</span>
            <p style="margin: 4px 0 0 0; color: #4a5568;">${item.aid_remark}</p>
        </div>
      `
        )
        .join("");

      historyList.innerHTML = totalBadge + listHtml;
    } else {
      historyList.innerHTML =
        "<p style='text-align:center; padding:20px; color: #a0aec0;'>No previous records found.</p>";
    }
  } catch (e) {
    historyList.innerHTML = "<p>Failed to load history.</p>";
  }
}
