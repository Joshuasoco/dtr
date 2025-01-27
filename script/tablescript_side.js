// Hide checkbox on page load
document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("all_students_checkbox").style.display = "none";
});

document.getElementById("edit_button").addEventListener("click", function () {
  const button = this;
  const icon = button.querySelector("i");
  const allStudentsCheckbox = document.getElementById("all_students_checkbox");

  if (button.classList.contains("delete-mode")) {
    // Switch back to Edit mode
    icon.classList.remove("bxs-trash");
    icon.classList.add("bxs-pencil");
    button.classList.remove("delete-mode");
    button.innerHTML = '<i class="bx bxs-pencil"></i> Edit';
    button.style.backgroundColor = "#d9d9d9";
    button.style.color = "black";
    allStudentsCheckbox.style.display = "none"; // Hide checkbox in Edit mode
  } else {
    // Switch to Delete mode
    icon.classList.remove("bxs-pencil");
    icon.classList.add("bxs-trash");
    button.classList.add("delete-mode");
    button.innerHTML = '<i class="bx bxs-trash"></i> &nbsp;&nbsp;&nbsp;';
    button.style.backgroundColor = "#E72E2E";
    button.style.color = "white";
    allStudentsCheckbox.style.display = "inline-block"; // Show checkbox in Delete mode
  }
});
