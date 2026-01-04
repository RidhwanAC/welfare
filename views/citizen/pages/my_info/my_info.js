export function init() {
  const form = document.getElementById("register-form");
  const openBtn = document.getElementById("openModal");
  const closeBtn = document.getElementById("closeModal");
  const actionBtn = document.getElementById("actionBtn");
  const modal = document.getElementById("modalOverlay");

  if (!openBtn || !modal || !form) return;

  // OPEN MODAL
  openBtn.addEventListener("click", (event) => {
    event.preventDefault();
    if (form.checkValidity()) {
      modal.classList.add("active");
    } else {
      form.reportValidity();
    }
  });

  // CLOSE MODAL LOGIC
  const closeModal = () => {
    modal.classList.remove("active");
  };

  if (closeBtn) closeBtn.addEventListener("click", closeModal);

  if (actionBtn) {
    actionBtn.addEventListener("click", (event) => {
      event.preventDefault();
      // TODO: Add your action logic here
      closeModal();
      form.reset();
    });
  }
}
