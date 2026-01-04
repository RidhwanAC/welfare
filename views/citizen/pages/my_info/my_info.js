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

  // 2. MODAL LOGIC
  if (openBtn) {
    openBtn.addEventListener("click", (event) => {
      event.preventDefault();
      if (form.checkValidity()) {
        modal.classList.add("active");
      } else {
        form.reportValidity();
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
      // TODO: Add your save logic here

      closeModal();
      // form.reset(); // Usually, you don't want to reset 'My Info' after saving
    });
  }
}
