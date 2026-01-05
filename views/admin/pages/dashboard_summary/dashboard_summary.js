export function init(userData) {
  const form = document.getElementById("welfare-form");
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

    // 2. Prepare Payload (Include user_id from session)
    const payload = {
      user_id: userData.user_id,
      aid_type: document.getElementById("aid-type").value,
      welfare_category: document.getElementById("welfare-category").value,
      remarks: document.getElementById("remarks").value,
      status: "Pending",
    };

    // 3. HTTP POST
    try {
      const response = await fetch(
        "http://localhost/welfare/welfare/server/submit_welfare.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        }
      );

      const result = await response.json();

      if (result.status === "success") {
        // 4. Success -> Open Modal
        modal.classList.add("active");
        form.reset();
      } else {
        alert("Submission failed: " + result.message);
      }
    } catch (err) {
      alert("Server error. Please try again later.");
    }
  });

  // 5. Close Modal Logic
  const closeModal = () => modal.classList.remove("active");
  if (closeBtn) closeBtn.addEventListener("click", closeModal);
  if (actionBtn) actionBtn.addEventListener("click", closeModal);
}
