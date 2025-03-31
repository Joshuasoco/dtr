document.addEventListener("DOMContentLoaded", function() {
    const selectAllCheckbox = document.getElementById("selectAll");
    const studentCheckboxes = document.querySelectorAll(".selectItem");
    const deleteButton = document.getElementById("deleteSelected");

    // ✅ Function to toggle all checkboxes when the header checkbox is clicked
    selectAllCheckbox.addEventListener("change", function() {
        studentCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    });

    // ✅ Function to delete selected students
    deleteButton.addEventListener("click", function() {
        let selected = document.querySelectorAll(".selectItem:checked");

        if (selected.length === 0) {
            alert("Please select at least one student to delete.");
            return;
        }

        if (!confirm("Are you sure you want to delete the selected students?")) {
            return;
        }

        let studentIds = Array.from(selected).map(cb => cb.value);

        fetch("delete_students.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    ids: studentIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Students deleted successfully!");
                    location.reload();
                } else {
                    alert("Error deleting students: " + (data.error || "Unknown error"));
                }
            })
            .catch(error => console.error("Error:", error));
    });
});