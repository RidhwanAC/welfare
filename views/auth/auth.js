const authForm = document.getElementById("auth-form");
const formGroup = document.getElementById("form-group");
const submitBtn = document.getElementById("submit-btn");
const btnLogin = document.getElementById("btn-login");
const btnRegister = document.getElementById("btn-register");

// Track current mode
let currentMode = "login";

const showLogin = () => {
  currentMode = "login";
  submitBtn.textContent = "Login";
  formGroup.innerHTML = `
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
    `;
  btnLogin.classList.add("active");
  btnRegister.classList.remove("active");
};

const showRegister = () => {
  currentMode = "register";
  submitBtn.textContent = "Register";
  formGroup.innerHTML = `
        <input type="text" name="username" placeholder="Username" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <input type="password" name="confirm-password" placeholder="Confirm Password" required />
    `;
  btnLogin.classList.remove("active");
  btnRegister.classList.add("active");
};

// --- Validation Helper ---
const validateForm = (formData) => {
  const username = formData.get("username");
  const password = formData.get("password");

  if (username.length < 3) {
    alert("Username must be at least 3 characters long.");
    return false;
  }

  if (password.length < 6) {
    alert("Password must be at least 6 characters long.");
    return false;
  }

  if (currentMode === "register") {
    const email = formData.get("email");
    const confirmPassword = formData.get("confirm-password");

    // Basic Email Regex
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
      alert("Please enter a valid email address.");
      return false;
    }

    if (password !== confirmPassword) {
      alert("Passwords do not match!");
      return false;
    }
  }
  return true;
};

// --- Updated Form Submission Logic ---
authForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  // Create the data object from the form
  const formData = new FormData(authForm);

  // 1. Run Validation
  if (!validateForm(formData)) {
    return; // Stop here if validation fails
  }

  // 2. Visual feedback (Disable button during request)
  submitBtn.disabled = true;
  submitBtn.textContent = "Processing...";

  try {
    const url =
      currentMode === "register" ? "/server/register.php" : "/server/login.php";

    const response = await fetch(url, {
      method: "POST",
      body: formData,
    });

    // Check if response is actually JSON
    const contentType = response.headers.get("content-type");
    if (!contentType || !contentType.includes("application/json")) {
      throw new TypeError("Oops, we didn't get JSON from the server!");
    }

    const result = await response.json();

    if (result.status === "success") {
      alert(currentMode === "register" ? "Account created!" : "Logged in!");
      if (currentMode === "register") {
        showLogin();
      } else {
        // Redirect to dashboard on login success
        window.location.href = "dashboard.php";
      }
    } else {
      alert("Error: " + result.message);
    }
  } catch (error) {
    console.error("Submission Error:", error);
    alert("Server error. Please try again later.");
  } finally {
    // 3. Re-enable button
    submitBtn.disabled = false;
    submitBtn.textContent = currentMode === "register" ? "Register" : "Login";
  }
});

// Event Listeners for toggling
btnLogin.addEventListener("click", showLogin);
btnRegister.addEventListener("click", showRegister);

// Initialize
showLogin();
