export async function init(userData) {
  const { privilege, district, sub_district } = userData;

  try {
    // Build the URL with query parameters for filtering
    const url = `http://localhost/welfare/welfare/server/fetch_summary.php?privilege=${privilege}&district=${district}&sub_district=${sub_district}`;

    const response = await fetch(url);
    const data = await response.json();

    // Update the DOM
    document.getElementById("count-citizens").textContent = data.total_citizens;
    document.getElementById("count-welfare").textContent = data.total_welfare;
    document.getElementById("count-pending-welfare").textContent =
      data.pending_welfare;
    document.getElementById("count-pending-complaints").textContent =
      data.pending_complaints;
  } catch (error) {
    console.error("Error loading dashboard data:", error);
  }
}
