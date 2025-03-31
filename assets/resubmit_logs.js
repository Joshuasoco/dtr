document.addEventListener("DOMContentLoaded", function() {
    const selectAllCheckbox = document.getElementById("selectAll");
    const logCheckboxes = document.querySelectorAll(".selectItem");
    const resubmitButton = document.getElementById("resubmitSelected");

    // ✅ Select or Deselect All Checkboxes
    selectAllCheckbox.addEventListener("change", function() {
        logCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    });

    // ✅ Resubmit Selected Duty Logs
    resubmitButton.addEventListener("click", function() {
        let selectedLogs = document.querySelectorAll(".selectItem:checked");

        if (selectedLogs.length === 0) {
            alert("Please select at least one duty log to resubmit.");
            return;
        }

        if (!confirm("Are you sure you want to resubmit the selected duty logs?")) {
            return;
        }

        let logIds = Array.from(selectedLogs).map(cb => cb.value);

        fetch("resubmit_approved_duty_logs.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ ids: logIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Selected duty logs have been resubmitted!");
                location.reload();
            } else {
                alert("Error resubmitting duty logs: " + (data.error || "Unknown error"));
            }
        })
        .catch(error => console.error("Error:", error));
    });
});
