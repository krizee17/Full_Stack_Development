// Main JavaScript for AJAX functionality

// Auto-refresh dashboard counters
function refreshDashboardCounters() {
  fetch("api/dashboard_stats.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        document.getElementById("total-incidents").textContent =
          data.total_incidents;
        document.getElementById("detected-count").textContent = data.detected;
        document.getElementById("investigating-count").textContent =
          data.investigating;
        document.getElementById("resolved-count").textContent = data.resolved;
      }
    })
    .catch((error) => {
      console.error("Error refreshing dashboard:", error);
    });
}

// Update incident status via AJAX
function updateIncidentStatus(incidentId, newStatus) {
  const formData = new FormData();
  formData.append("incident_id", incidentId);
  formData.append("new_status", newStatus);
  formData.append("action", "update_status");

  fetch("api/update_status.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update the status badge in the table
        const statusCell = document.querySelector(
          `tr[data-incident-id="${incidentId}"] .status-badge`,
        );
        if (statusCell) {
          statusCell.className =
            "badge status-badge status-" + newStatus.toLowerCase();
          statusCell.textContent = newStatus;
          // Update the onclick to the new status
          statusCell.onclick = () => cycleIncidentStatus(incidentId, newStatus);
        }

        // Show success message
        showAlert("Status updated successfully!", "success");

        // Refresh dashboard if on dashboard page
        if (document.getElementById("total-incidents")) {
          refreshDashboardCounters();
        }
      } else {
        showAlert("Error updating status: " + data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showAlert("An error occurred while updating status", "error");
    });
}

// Cycle incident status on click
function cycleIncidentStatus(incidentId, currentStatus) {
  let nextStatus;
  switch (currentStatus) {
    case "Detected":
      nextStatus = "Investigating";
      break;
    case "Investigating":
      nextStatus = "Resolved";
      break;
    case "Resolved":
      // Maybe don't cycle back, or cycle to Detected?
      // For now, let's not change if Resolved
      return;
    default:
      return;
  }
  updateIncidentStatus(incidentId, nextStatus);
}

// Show alert message
function showAlert(message, type) {
  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type}`;
  alertDiv.textContent = message;

  const main = document.querySelector("main");
  if (main) {
    main.insertBefore(alertDiv, main.firstChild);

    // Remove alert after 5 seconds
    setTimeout(() => {
      alertDiv.remove();
    }, 5000);
  }
}

// Confirm delete
function confirmDelete(message) {
  return confirm(message || "Are you sure you want to delete this item?");
}

// Filter Modal Functions
function openFilterModal() {
  const modal = document.getElementById("filterModal");
  if (modal) {
    modal.classList.add("active");
  }
}

function closeFilterModal() {
  const modal = document.getElementById("filterModal");
  if (modal) {
    modal.classList.remove("active");
  }
}

// Close modal when clicking outside
document.addEventListener("click", function (event) {
  const modal = document.getElementById("filterModal");
  if (modal && event.target === modal) {
    closeFilterModal();
  }
});

// Initialize dashboard auto-refresh if on dashboard page
document.addEventListener("DOMContentLoaded", function () {
  if (document.getElementById("total-incidents")) {
    // Refresh immediately
    refreshDashboardCounters();

    // Refresh every 30 seconds
    setInterval(refreshDashboardCounters, 30000);
  }
});
