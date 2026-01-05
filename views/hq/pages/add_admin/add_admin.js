import { kedahData } from "./location_data.js";

export async function init(userData) {
  const form = document.getElementById("add-admin-form");
  const modal = document.getElementById("modalOverlay");
  const actionBtn = document.getElementById("actionBtn");
  const districtSelect = document.getElementById("district");
  const subDistrictSelect = document.getElementById("sub_district");

  // Security check
  if (!userData || userData.privilege != 4) {
    document.body.innerHTML = `<div style="padding: 50px; text-align: center;"><h3>Access Denied</h3></div>`;
    return;
  }

  // --- 1. POPULATE DISTRICTS ---
  districtSelect.innerHTML =
    '<option value="" disabled selected>-- Select district --</option>';
  Object.keys(kedahData).forEach((district) => {
    const option = document.createElement("option");
    option.value = district;
    option.textContent = district
      .replace(/-/g, " ")
      .replace(/\b\w/g, (c) => c.toUpperCase());
    districtSelect.appendChild(option);
  });

  // --- 2. HANDLE SUB-DISTRICT LOGIC ---
  districtSelect.addEventListener("change", function () {
    const selectedDistrict = this.value;
    subDistrictSelect.innerHTML =
      '<option value="" disabled selected>-- Select sub-district --</option>';

    if (selectedDistrict && kedahData[selectedDistrict]) {
      subDistrictSelect.disabled = false;
      kedahData[selectedDistrict].forEach((mukim) => {
        const option = document.createElement("option");
        option.value = mukim.toLowerCase().replace(/\s+/g, "-");
        option.textContent = mukim;
        subDistrictSelect.appendChild(option);
      });
    } else {
      subDistrictSelect.disabled = true;
    }
  });

  // --- 3. SUBMIT FORM ---
  form.onsubmit = async (e) => {
    e.preventDefault();

    const adminData = {
      full_name: document.getElementById("full_name").value,
      ic_number: document.getElementById("ic_number").value,
      email: document.getElementById("email").value, // Added email
      phone: document.getElementById("phone").value,
      privilege: document.getElementById("privilege").value,
      district: districtSelect.value,
      sub_district: subDistrictSelect.value,
      username: document.getElementById("username").value,
      password: document.getElementById("password").value,

      // Validation data from the CURRENT logged-in HQ user
      requester_id: userData.user_id,
      requester_privilege: userData.privilege, // This will be 4
    };

    try {
      const response = await fetch("/welfare/welfare/server/add_admin.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(adminData),
      });

      const result = await response.json();

      if (result.status === "success") {
        showModal("Success!", result.message, true);
      } else {
        showModal("Error", result.message, false);
      }
    } catch (error) {
      showModal("Connection Error", "Could not connect to the server.", false);
    }
  };

  function showModal(title, message, isSuccess) {
    const titleEl = document.getElementById("modalTitle");
    titleEl.textContent = title;
    titleEl.style.color = isSuccess ? "#2ecc71" : "#e74c3c";
    document.getElementById("modalMessage").textContent = message;
    modal.classList.add("active");
  }

  actionBtn.onclick = () => {
    modal.classList.remove("active");
    if (document.getElementById("modalTitle").textContent === "Success!") {
      form.reset();
      subDistrictSelect.disabled = true;
    }
  };
}
