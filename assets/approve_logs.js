document.addEventListener("DOMContentLoaded", function() {
    console.log("approve_logs.js loaded");

    const approveButton = document.getElementById("approveSelected");
    if (!approveButton) {
        console.error("Approve button not found");
    }


    approveButton.addEventListener("click", function() {
        let selectedLogs = document.querySelectorAll(".selectItem:checked");

        if (selectedLogs.length === 0) {
            alert("Please select at least one duty log to approve.");
            return;
        }

        if (!confirm("Are you sure you want to approve the selected duty logs?")) {
            return;
        }

        let logIds = Array.from(selectedLogs).map(cb => cb.value);

        fetch("bulk_approve.php", {
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
                    alert("Duty logs approved successfully!");
                    location.reload();
                } else {
                    alert("Error approving duty logs: " + (data.error || "Unknown error"));
                }
            })
            .catch(error => console.error("Error:", error));
    });
});