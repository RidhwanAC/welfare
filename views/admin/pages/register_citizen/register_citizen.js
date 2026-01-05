import { kedahData } from "./location_data.js";

export function init(userData) {
  const form = document.getElementById("register-form");
  const openBtn = document.getElementById("openModal");
  const closeBtn = document.getElementById("closeModal");
  const actionBtn = document.getElementById("actionBtn");
  const modal = document.getElementById("modalOverlay");
  const districtSelect = document.getElementById("district");
  const subDistrictSelect = document.getElementById("sub-district");

  // Check if data and elements exist
  if (!userData || !form || !modal) return;

  // --- 1. POPULATE DISTRICT DROPDOWN ---
  // Clear existing options except the placeholder
  districtSelect.innerHTML =
    '<option value="" disabled selected>-- Select district</option>';

  Object.keys(kedahData).forEach((district) => {
    const option = document.createElement("option");
    option.value = district;
    // Format "kota-setar" to "Kota Setar"
    option.textContent = district
      .replace(/-/g, " ")
      .replace(/\b\w/g, (c) => c.toUpperCase());
    districtSelect.appendChild(option);
  });

  // --- 2. REUSABLE SUB-DISTRICT POPULATION LOGIC ---
  const populateSubDistricts = (selectedDistrict, currentSubValue = "") => {
    // Reset Sub-District dropdown
    subDistrictSelect.innerHTML =
      '<option value="" disabled selected>-- Select sub-district</option>';

    if (selectedDistrict && kedahData[selectedDistrict]) {
      subDistrictSelect.disabled = false;

      kedahData[selectedDistrict].forEach((mukim) => {
        const option = document.createElement("option");
        // Convert "Alor Merah" to "alor-merah" for value
        const valueSlug = mukim.toLowerCase().replace(/\s+/g, "-");
        option.value = valueSlug;
        option.textContent = mukim;
        subDistrictSelect.appendChild(option);
      });

      // If we have a value to pre-select (from auto-fill)
      if (currentSubValue) {
        subDistrictSelect.value = currentSubValue;
      }
    } else {
      subDistrictSelect.disabled = true;
    }
  };

  // --- 3. EVENT LISTENER FOR MANUAL DISTRICT CHANGE ---
  districtSelect.addEventListener("change", function () {
    populateSubDistricts(this.value);
  });

  // --- 4. AUTO-FILL FORM FIELDS ---
  const fieldMapping = {
    "full-name": userData.full_name,
    "ic-number": userData.ic_number,
    "email-address": userData.email,
    "phone-number": userData.phone,
    "household-size": userData.household_size,
    "monthly-income": userData.household_income,
    district: userData.district,
  };

  // Loop through standard fields
  Object.keys(fieldMapping).forEach((id) => {
    const element = document.getElementById(id);
    if (element && fieldMapping[id]) {
      element.value = fieldMapping[id];
    }
  });

  // SPECIAL AUTO-FILL FOR SUB-DISTRICT:
  // It needs the district to be set first to generate the options
  if (userData.district) {
    populateSubDistricts(userData.district, userData.sub_district);
  }

  const emailInput = document.getElementById("email-address");
  if (emailInput) {
    emailInput.readOnly = true;
  }

  // --- 5. SUBMIT / SAVE LOGIC ---
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
          // Sync existing session data with new values
          Object.assign(userData, payload);
          localStorage.setItem("user_session", JSON.stringify(userData));

          // Open success modal
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

  // --- 6. MODAL NAVIGATION ---
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
