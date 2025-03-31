document.addEventListener("DOMContentLoaded", function() {
    const selectAllCheckbox = document.getElementById("selectAll");
    const logCheckboxes = document.querySelectorAll(".selectItem");
    const deleteButton = document.getElementById("deleteSelected");

    // ✅ Select or Deselect All Checkboxes
    selectAllCheckbox.addEventListener("change", function() {
        logCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    });

    // ✅ Delete Selected Duty Logs
    deleteButton.addEventListener("click", function() {
        let selectedLogs = document.querySelectorAll(".selectItem:checked");

        if (selectedLogs.length === 0) {
            alert("Please select at least one duty log to delete.");
            return;
        }

        if (!confirm("Are you sure you want to delete the selected duty logs?")) {
            return;
        }

        let logIds = Array.from(selectedLogs).map(cb => cb.value);

        fetch("delete_duty_logs.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    ids: logIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Duty logs deleted successfully!");
                    location.reload();
                } else {
                    alert("Error deleting duty logs: " + (data.error || "Unknown error"));
                }
            })
            .catch(error => console.error("Error:", error));
    });
});
