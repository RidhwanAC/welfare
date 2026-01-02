const navItems = document.querySelectorAll(".nav-item");
const contentArea = document.getElementById("main-content");

const pages = {
  Register: "./pages/register.html",
  "Apply Welfare": "./pages/apply_welfare.html",
  "Application Status": "./pages/application_status.html",
  "Submit Complaint": "./pagest/submit_complaint.html",
  "List Complaint": "./pages/list_complaint.html",
  "My Aid History": "./pages/aid_history.html",
};

navItems.forEach((item) => {
  item.addEventListener("click", async function () {
    navItems.forEach((nav) => nav.classList.remove("active"));

    this.classList.add("active");

    const selectedTitle = this.innerText;
    const pageSource = pages[selectedTitle];

    try {
      const response = await fetch(pageSource);
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);

      const htmlContent = await response.text();
      contentArea.innerHTML = htmlContent;
    } catch (error) {
      contentArea.innerHTML =
        "<h1>Error</h1><p>Could not load the requested page.</p>";
    }
  });
});

// Initial state
navItems[0].click();
