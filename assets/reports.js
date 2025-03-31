document.addEventListener("DOMContentLoaded", function () {
    console.log("Reports script loaded!");

    // CSV Export Function
    document.getElementById("exportCSV").addEventListener("click", function () {
        window.location.href = "export_report.php?type=csv";
    });

    // PDF Export Function
    document.getElementById("exportPDF").addEventListener("click", function () {
        window.location.href = "export_report.php?type=pdf";
    });

    // Filtering Records (Admin Panel)
    document.getElementById("filterMonth").addEventListener("change", function () {
        let selectedMonth = this.value;
        fetch(`search_filter.php?month=${selectedMonth}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById("dutyLogsTable").innerHTML = data;
            })
            .catch(error => console.error("Error filtering records:", error));
    });
});
