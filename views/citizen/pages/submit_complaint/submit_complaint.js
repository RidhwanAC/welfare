export function init(userData) {
  const form = document.getElementById("complaint-form");
  const modal = document.getElementById("modalOverlay");
  const closeBtn = document.getElementById("closeModal");
  const actionBtn = document.getElementById("actionBtn");

  if (!userData || !form || !modal) return;

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    // 1. Validate Form
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    // 2. Prepare Payload
    const payload = {
      user_id: userData.user_id,
      complaint_type: document.getElementById("complaint-type").value,
      complaint_details: document.getElementById("complaint-details").value,
      status: "Pending",
    };

    // 3. HTTP POST
    try {
      const response = await fetch(
        "http://localhost/welfare/welfare/server/submit_complaint.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        }
      );

      const result = await response.json();

      if (result.status === "success") {
        // 4. Success → Open Modal
        modal.classList.add("active");
        form.reset();
      } else {
        alert("Submission failed: " + result.message);
      }
    } catch (err) {
      alert("Server error. Please try again later.");
    }
  });

  // 5. Close Modal Logic (same as apply_welfare)
  const closeModal = () => modal.classList.remove("active");
  if (closeBtn) closeBtn.addEventListener("click", closeModal);
  if (actionBtn) actionBtn.addEventListener("click", closeModal);
}
