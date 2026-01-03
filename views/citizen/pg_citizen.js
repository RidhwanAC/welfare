const navItems = document.querySelectorAll(".nav-item");
const contentArea = document.getElementById("main-content");

const pages = {
  Register: "register",
  "Apply Welfare": "apply_welfare",
  "Application Status": "application_status",
  "Submit Complaint": "submit_complaint",
  "List Complaint": "list_complaint",
  "My Aid History": "my_aid_history",
};

navItems.forEach((item) => {
  item.addEventListener("click", async function () {
    navItems.forEach((nav) => nav.classList.remove("active"));
    this.classList.add("active");

    const pageKey = this.innerText;
    const folder = pages[pageKey];

    try {
      const response = await fetch(`./pages/${folder}.html`);
      const html = await response.text();
      contentArea.innerHTML = html;

      const module = await import(`./pages/${folder}.js`);

      if (module.init) {
        module.init();
      }
    } catch (error) {
      contentArea.innerHTML =
        "<h1>Error</h1><p>Could not load the requested page.</p>";
    }
  });
});

// Initial state
navItems[0].click();
