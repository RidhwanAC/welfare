// 1. Get DOM Elements
const navItems = document.querySelectorAll(".nav-item");
const contentArea = document.getElementById("main-content");
const usernameDisplay = document.querySelector(".user-info p:first-child");
const logoutBtn = document.querySelector(".right-appbar button");

// 2. Load and Validate Session Data
const sessionData = localStorage.getItem("user_session");

if (!sessionData) {
  // Redirect to login if no session is found
  window.location.replace("/welfare/welfare/views/auth/auth.html");
}

const user = JSON.parse(sessionData);

// 3. Update UI with Session Data
if (usernameDisplay) {
  usernameDisplay.textContent = user.username;
}

// 4. Logout Logic
logoutBtn.addEventListener("click", () => {
  localStorage.removeItem("user_session");
  window.location.href = "/welfare/welfare/views/auth/auth.html";
});

// 5. Navigation Logic
const pages = {
  "Dashboard Summary": "dashboard_summary",
  "View Applications": "view_applications",
  "View Complaints": "view_complaints",
  "Distribute Aids": "distribute_aids",
};

navItems.forEach((item) => {
  item.addEventListener("click", async function () {
    navItems.forEach((nav) => nav.classList.remove("active"));
    this.classList.add("active");

    const pageKey = this.innerText;
    const folder = pages[pageKey];

    try {
      const response = await fetch(`./pages/${folder}/${folder}.html`);
      const html = await response.text();
      contentArea.innerHTML = html;

      // Import the specific module for the page
      const module = await import(`./pages/${folder}/${folder}.js`);

      // PASS SESSION DATA: If the module has an init function, pass the user object to it
      if (module.init) {
        requestAnimationFrame(() => {
          module.init(user);
        });
      }
    } catch (error) {
      console.error("Navigation error:", error);
      contentArea.innerHTML =
        "<h1>Error</h1><p>Could not load the requested page.</p>";
    }
  });
});

// Initial state - Load the first menu item
if (navItems.length > 0) {
  navItems[0].click();
}
