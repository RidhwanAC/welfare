<?php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Authentication</title>
  <link rel="stylesheet" href="style_auth.css">
  <style>
    /* Small layout tweak so content fits nicely on narrower screens */
    @media (max-width: 700px) {
      .image-section { display:none; }
      .form-section { width:100%; padding:30px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="image-section">
      <img src="background.jpeg" alt="background" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22600%22 height=%22400%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23f0f0f0%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23cccccc%22 font-size=%2224%22>No Image</text></svg>';">
    </div>

    <div class="form-section">
      <div class="toggle-buttons">
        <button type="button" id="btn-login" class="active">Login</button>
        <button type="button" id="btn-register">Register</button>
      </div>

      <form id="login-form" class="form active">
        <input type="text" id="login-username" placeholder="Username" required>
        <input type="password" id="login-password" placeholder="Password" required>
        <button type="button" class="submit-btn" id="login-submit">Login</button>
      </form>

      <form id="register-form" class="form">
        <input type="text" id="reg-username" placeholder="Username" required>
        <input type="email" id="reg-email" placeholder="Email" required>
        <input type="password" id="reg-password" placeholder="Password" required>
        <input type="password" id="reg-confirm" placeholder="Confirm Password" required>
        <button type="button" class="submit-btn" id="register-submit">Register</button>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btnLogin = document.getElementById('btn-login');
      const btnRegister = document.getElementById('btn-register');
      const loginForm = document.getElementById('login-form');
      const registerForm = document.getElementById('register-form');
      const loginSubmitBtn = document.getElementById('login-submit');
      const registerSubmitBtn = document.getElementById('register-submit');

      function showLogin() {
        btnLogin.classList.add('active');
        btnRegister.classList.remove('active');
        loginForm.classList.add('active');
        registerForm.classList.remove('active');
        document.getElementById('login-username').focus();
      }

      function showRegister() {
        btnRegister.classList.add('active');
        btnLogin.classList.remove('active');
        registerForm.classList.add('active');
        loginForm.classList.remove('active');
        document.getElementById('reg-username').focus();
      }

      btnLogin.addEventListener('click', showLogin);
      btnRegister.addEventListener('click', showRegister);

      // Handle form submit via JS so Enter key works and navigation is consistent
      loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('login-username').value.trim();
        const password = document.getElementById('login-password').value;
        if (!username || !password) {
          alert('Please enter username and password');
          return;
        }

        loginSubmitBtn.disabled = true;
        try {
          const res = await fetch('server/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ username: username, password: password })
          });

          let data = null;
          try { data = await res.json(); } catch (e) { /* ignore parse errors */ }

          if (!res.ok) {
            const msg = data && data.message ? data.message : (res.statusText || 'Login failed');
            alert(msg);
            return;
          }

          if (data && data.status === 'success') {
            // store user (without password) for later use
            if (data.data) sessionStorage.setItem('user', JSON.stringify(data.data));
            alert(data.message || 'Login successful');
            window.location.href = 'pg_home.php';
          } else {
            alert((data && data.message) ? data.message : 'Login failed');
          }
        } catch (err) {
          console.error(err);
          alert('Network or server error during login');
        } finally {
          loginSubmitBtn.disabled = false;
        }
      });

      registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        // Simple client-side confirm password check
        const username = document.getElementById('reg-username').value.trim();
        const email = document.getElementById('reg-email').value.trim();
        const pw = document.getElementById('reg-password').value;
        const cpw = document.getElementById('reg-confirm').value;
        if (!username || !email || !pw) {
          alert('Please fill all required fields');
          return;
        }
        if (pw !== cpw) {
          alert('Passwords do not match');
          return;
        }

        registerSubmitBtn.disabled = true;
        try {
          const res = await fetch('server/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ username: username, email: email, password: pw })
          });

          let data = null;
          try { data = await res.json(); } catch (e) { /* ignore parse errors */ }

          if (!res.ok) {
            const msg = data && data.message ? data.message : (res.statusText || 'Registration failed');
            alert(msg);
            return;
          }

          if (data && data.status === 'success') {
            if (data.data) sessionStorage.setItem('user', JSON.stringify(data.data));
            alert(data.message || 'Registered successfully');
            window.location.href = 'pg_home.php';
          } else {
            alert((data && data.message) ? data.message : 'Registration failed');
          }
        } catch (err) {
          console.error(err);
          alert('Network or server error during registration');
        } finally {
          registerSubmitBtn.disabled = false;
        }
      });

      // Wire up the visible buttons to submit their forms (so click or Enter both work)
      loginSubmitBtn.addEventListener('click', () => loginForm.requestSubmit());
      registerSubmitBtn.addEventListener('click', () => registerForm.requestSubmit());

      // Ensure initial focus
      if (loginForm.classList.contains('active')) {
        document.getElementById('login-username').focus();
      }
    });
  </script>
</body>
</html>
