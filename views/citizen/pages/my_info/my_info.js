export function init(userData) {
  const form = document.getElementById("register-form");
  const openBtn = document.getElementById("openModal");
  const closeBtn = document.getElementById("closeModal");
  const actionBtn = document.getElementById("actionBtn");
  const modal = document.getElementById("modalOverlay");

  // Check if data and elements exist
  if (!userData || !form || !modal) return;

  // 1. AUTO-FILL FORM FIELDS
  // Mapping the session data keys to your HTML input IDs
  const fieldMapping = {
    "full-name": userData.full_name,
    "ic-number": userData.ic_number,
    "email-address": userData.email,
    "phone-number": userData.phone,
    "household-size": userData.household_size,
    "monthly-income": userData.household_income,
    district: userData.district,
    "sub-district": userData.sub_district,
  };

  // Loop through the mapping and assign values if the elements exist
  Object.keys(fieldMapping).forEach((id) => {
    const element = document.getElementById(id);
    if (element && fieldMapping[id]) {
      element.value = fieldMapping[id];
    }
  });

  const emailInput = document.getElementById("email-address");
  if (emailInput) {
    emailInput.readOnly = true;
  }

  if (openBtn) {
    openBtn.addEventListener("click", async (event) => {
      event.preventDefault();

      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const payload = {
        user_id: userData.user_id,
        full_name: document.getElementById("full-name").value,
        ic_number: document.getElementById("ic-number").value,
        phone: document.getElementById("phone-number").value,
        household_size: document.getElementById("household-size").value,
        household_income: document.getElementById("monthly-income").value,
        district: document.getElementById("district").value,
        sub_district: document.getElementById("sub-district").value,
      };

      try {
        const response = await fetch(
          "http://localhost/welfare/welfare/server/update_my_info.php",
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
          }
        );

        const result = await response.json();

        if (result.status === "success") {
          // Sync localStorage
          Object.assign(userData, payload);
          localStorage.setItem("user_session", JSON.stringify(userData));

          // ✅ OPEN MODAL ONLY ON SUCCESS
          modal.classList.add("active");
        } else {
          alert("Error: " + result.message);
        }
      } catch (err) {
        alert("Server error. Please try again.");
        console.error(err);
      }
    });
  }

  const closeModal = () => {
    modal.classList.remove("active");
  };

  if (closeBtn) closeBtn.addEventListener("click", closeModal);

  if (actionBtn) {
    actionBtn.addEventListener("click", (event) => {
      event.preventDefault();
      closeModal();
    });
  }
}
