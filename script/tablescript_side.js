document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.querySelector("#student_table tbody");
  const img_no_result = document.querySelector("#img_res");
  const editButton = document.getElementById("edit_button");
  const tableRows = document.querySelectorAll("#student_table tr");
  const allStudentsCheckbox = document.getElementById("all_students_checkbox");
  const noResults = document.querySelector(".no_results");
  const searchTermDisplay = document.getElementById("search_term");
  allStudentsCheckbox.style.display = "none";

  //table if empty
  function check_table() {
    const visibleRows = document.querySelectorAll(
      "#student_table tbody tr:not([style*='display: none'])"
    );
    if (visibleRows.length === 0) {
      noResults.style.display = "block";
      img_no_result.style.display = "block";
    } else {
      noResults.style.display = "none";
      img_no_result.style.display = "none";
    }
  }
  check_table();

  //search input
  const searchInput = document.getElementById("search_input");
  searchInput.addEventListener("input", () => {
    const searchTerm = searchInput.value.toLowerCase();
    const rows = document.querySelectorAll("#student_table tbody tr");
    let visibleCount = 0;

    rows.forEach((row) => {
      const cells = row.children;
      let visible = false;

      if (cells.length >= 2) {
        const studentId = cells[0].textContent.toLowerCase();
        const studentName = cells[1].textContent.toLowerCase();
        visible =
          studentId.includes(searchTerm) || studentName.includes(searchTerm);
      }

      row.style.display = visible ? "" : "none";
      if (visible) visibleCount++;
    });

    if (visibleCount === 0) {
      noResults.style.display = "block";
      img_no_result.style.display = "block";
      searchTermDisplay.textContent = `"${searchInput.value}"`;
    } else {
      noResults.style.display = "none";
      img_no_result.style.display = "none";
    }
  });

  allStudentsCheckbox.addEventListener("change", (e) => {
    const checkboxes = document.querySelectorAll(".row-checkbox");
    checkboxes.forEach((checkbox) => {
      checkbox.checked = e.target.checked;
    });
  });

  const toggleCheckboxes = (show) => {
    allStudentsCheckbox.style.display = show ? "inline" : "none";

    tableRows.forEach((row, index) => {
      if (index === 0) return; // Skip header row

      const idCell = row.children[0];
      const renderedHoursCell = row.querySelector("td:nth-child(6)");
      const currentId = idCell.textContent;

      if (show) {
        if (!idCell.querySelector("input[type='checkbox']")) {
          idCell.textContent = "";
          const checkbox = document.createElement("input");
          checkbox.type = "checkbox";
          checkbox.className = "row-checkbox";
          checkbox.style.marginRight = "10px"; // Space before the ID
          idCell.appendChild(checkbox);
          idCell.appendChild(document.createTextNode(currentId));
        }
      
        if (!renderedHoursCell.querySelector(".hours-button")) {
          const currentHours = renderedHoursCell.textContent;
          const container = document.createElement("div");
          container.className = "hours-container"; // Use flexbox for proper alignment
          container.innerHTML = `
            <span>${currentHours}</span>
            <button class="hours-button">
              <img src="/images/pen.svg" alt="edit hours" class="hours-icon">
            </button>
          `;
          renderedHoursCell.textContent = "";  // Clear the cell content
          renderedHoursCell.appendChild(container);
      
          const button = container.querySelector(".hours-button");
          button.addEventListener("click", (e) => {
            e.stopPropagation(); 
            window.location.href = "/htmlsidebar/hkstudent_sidebar.html";
            console.log("Edit hours clicked for row:", index); 
          });
        }
      } else {
        const checkbox = idCell.querySelector("input[type='checkbox']");
        if (checkbox) {
          idCell.textContent = currentId; 
        }
      
        const hoursContainer = renderedHoursCell.querySelector(".hours-container");
        if (hoursContainer) {
          const hoursText = hoursContainer.querySelector("span").textContent;
          renderedHoursCell.textContent = hoursText; 
        }
      }
    });
  };

  editButton.addEventListener("click", () => {
    const button = editButton;
    const icon = button.querySelector("i");

    if (button.classList.contains("delete-mode")) {
      const selectedCheckboxes = document.querySelectorAll(
        ".row-checkbox:checked"
      );
      if (selectedCheckboxes.length === 0) {
        icon.classList.remove("bxs-trash");
        icon.classList.add("bxs-pencil");
        button.classList.remove("delete-mode");
        button.innerHTML = '<i class="bx bxs-pencil"></i> Edit';
        button.style.backgroundColor = "#d9d9d9";
        button.style.color = "black";

        toggleCheckboxes(false); // hide checkboxes
        return;
      }
      show_delete_popup();
    } else {
      
      icon.classList.remove("bxs-pencil");
      icon.classList.add("bxs-trash");
      button.classList.add("delete-mode");
      button.innerHTML = '<i class="bx bxs-trash"></i> &nbsp;&nbsp;&nbsp;';
      button.style.backgroundColor = "#E72E2E";
      button.style.color = "white";

      toggleCheckboxes(true); // show checkboxes
    }
  });

  const addNewLink = document.querySelector(".add_new");
  const popupDiv = document.getElementById("divOne");

  addNewLink.addEventListener("click", (e) => {
    e.preventDefault(); 
    if (popupDiv) {
      popupDiv.style.display = "block";
    }
  });

  // add close button functionality
  const closeButton = document.getElementById("closePopup");
  closeButton.addEventListener("click", (e) => {
    e.preventDefault();
    popupDiv.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === popupDiv) {
      popupDiv.style.display = "none";
    }
  });
});
document.querySelector("form").addEventListener("submit", function (e) {
  const student_Id = document.getElementById("student_id");
  const student_Name = document.getElementById("student_name");

  if (student_Id.value === "" || student_Name.value === "") {
    alert("Please fill in all fields");
    return false;
  } else {
    alert("Form submitted");
    return true;
  }
});
//delete popup table
function show_delete_popup() {
  document.getElementById("delete_warning").style.display = "block";
}

function hide_delete_popup() {
  document.getElementById("delete_warning").style.display = "none";
}

function delete_selected() {
  const selectedCheckboxes = document.querySelectorAll(".row-checkbox:checked");
  if (selectedCheckboxes.length === 0) {
    alert("No student selected for deletion.");
    return;
  }
  hide_delete_popup(); 

  selectedCheckboxes.forEach((checkbox) => {
    checkbox.closest("tr").remove(); 
  });
  alert("Selected students deleted.");
}
//logout popup js
function show_logout() {
  console.log("Show logout popup");
  document.getElementById("logout_warning").style.display = "block";
}
function hide_logout() {
  document.getElementById("logout_warning").style.display = "none";
}

function logout() {
  document.getElementById("logout_warning").style.display = "none";
  console.log("Logout");
  //dito yung link ng login page sa href
  window.location.href = "/htmlsidebar/html_sidebar.html";
}

/*function validate(){
    if(document.myForm.name.value == ""){
      alert("enter a name");
      document.myForm.name.focus();
      return false;
    }
    if(!/^[a-zA-Z]*$/g.test(document.myForm.name.value)){
      alert("Invalid characters");
      document.myForm.name.focus();
      return false;
    }
  }*/
