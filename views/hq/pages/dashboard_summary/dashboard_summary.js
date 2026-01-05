export async function init(userData) {
  const { privilege, district, sub_district } = userData;

  try {
    const url = `/welfare/welfare/server/fetch_summary.php?privilege=${privilege}&district=${district}&sub_district=${sub_district}`;
    const response = await fetch(url);
    const data = await response.json();

    // Update Counts
    document.getElementById("count-citizens").textContent = data.total_citizens;
    document.getElementById("count-welfare").textContent = data.total_welfare;
    document.getElementById("count-pending-welfare").textContent =
      data.pending_welfare;
    document.getElementById("count-pending-complaints").textContent =
      data.pending_complaints;

    // Update Currency with RM formatting
    const totalAids = parseFloat(data.total_aids || 0);
    document.getElementById(
      "count-total-aids"
    ).textContent = `RM ${totalAids.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })}`;
  } catch (error) {
    console.error("Error loading dashboard data:", error);
  }
}
