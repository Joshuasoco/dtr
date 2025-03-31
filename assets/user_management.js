document.addEventListener("DOMContentLoaded", function () {
  // ====== MODAL ELEMENTS ======
  // Add Modal
  const addModal = document.getElementById("userModal");
  const openModalBtn = document.getElementById("openModalBtn");
  const closeModalBtn = document.getElementById("adminClose_button");
  const cancelBtn = document.getElementById("cancelBtn");

  // Delete Modal
  const deleteModal = document.getElementById("deleteModal");
  const closeDeleteBtn = document.getElementById("admin_close");
  const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
  const deleteUserButtons = document.querySelectorAll(".delete-user");

  // Edit Modal
  const editModal = document.getElementById("editModal");
  const saveBtn = document.getElementById("saveBtn");
  const editForm = document.getElementById("editForm");
  const editCloseBtn = document.querySelector("#editModal .admin-close");
  const editCancelBtn = document.getElementById("cancelEditBtn");
  const editUserButtons = document.querySelectorAll(".edit-user");

  // ====== FORM ELEMENTS ======
  const addForm = document.getElementById("addForm");
  const submitBtn = document.getElementById("submitBtn");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirmPassword");
  const passwordToggles = document.querySelectorAll(".password-toggle");
  const passwordHints = document.querySelectorAll(".password-hints li");
  const strengthMeter = document.querySelector(".strength-meter");
  const strengthText = document.querySelector(".strength-text span");
  const passwordMatch = document.querySelector(".password-match");

  let teacherIdToDelete = null;

  // ====== MODAL FUNCTIONS ======
  function openModal(modal) {
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  function closeModal(modal, formToReset = null) {
    modal.classList.remove("active");
    document.body.style.overflow = "";
    if (formToReset) formToReset.reset();
    if (modal === addModal) resetPasswordValidation();
    if (modal === deleteModal) teacherIdToDelete = null;
  }

  // ====== PASSWORD VALIDATION FUNCTIONS ======
  function checkPasswordStrength(password) {
    const strength = {
      length: password.length >= 8,
      uppercase: /[A-Z]/.test(password),
      number: /\d/.test(password),
      special: /[!@#$%^&*(),.?":{}|<>]/.test(password),
    };

    const strengthLevel = Object.values(strength).filter(Boolean).length;

    // Update hints
    passwordHints.forEach((hint) => {
      const requirement = hint.getAttribute("data-requirement");
      hint.classList.toggle("valid", strength[requirement]);
    });

    // Update strength meter
    const strengthClasses = [
      "strength-weak",
      "strength-medium",
      "strength-strong",
      "strength-very-strong",
    ];
    strengthMeter.className =
      "strength-meter " + strengthClasses[strengthLevel - 1];

    const strengthLabels = ["weak", "medium", "strong", "very strong"];
    strengthText.textContent = strengthLabels[strengthLevel - 1] || "";
    strengthText.style.color = getStrengthColor(strengthLevel);

    // Show strength meter if password not empty
    document.querySelector(".password-strength").style.display = password
      ? "block"
      : "none";

    return strengthLevel >= 3; // Consider medium or above as acceptable
  }

  function getStrengthColor(level) {
    const colors = [
      "var(--danger-color)",
      "var(--warning-color)",
      "var(--success-color)",
      "var(--primary-color)",
    ];
    return colors[level - 1] || "var(--danger-color)";
  }

  function checkPasswordMatch() {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    const isValid = password && password === confirmPassword;

    passwordMatch.style.display = password && confirmPassword ? "flex" : "none";

    if (password && confirmPassword) {
      if (isValid) {
        passwordMatch.style.color = "var(--success-color)";
        passwordMatch.querySelector(".match-text").textContent =
          "Passwords match";
        passwordMatch.querySelector(".match-icon").className =
          "fas fa-check-circle match-icon";
      } else {
        passwordMatch.style.color = "var(--danger-color)";
        passwordMatch.querySelector(".match-text").textContent =
          "Passwords don't match";
        passwordMatch.querySelector(".match-icon").className =
          "fas fa-times-circle match-icon";
      }
    }

    return isValid;
  }

  function resetPasswordValidation() {
    passwordHints.forEach((hint) => hint.classList.remove("valid"));
    document.querySelector(".password-strength").style.display = "none";
    passwordMatch.style.display = "none";
  }

  // ====== EVENT HANDLERS ======

  // Add Modal Events
  if (openModalBtn) {
    openModalBtn.addEventListener("click", () => openModal(addModal));
  }

  if (closeModalBtn) {
    closeModalBtn.addEventListener("click", () =>
      closeModal(addModal, addForm)
    );
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => closeModal(addModal, addForm));
  }

  // Delete Modal Events
  deleteUserButtons.forEach((button) => {
    button.addEventListener("click", function () {
      teacherIdToDelete = this.getAttribute("data-teacher-id");
      openModal(deleteModal);
    });
  });

  if (closeDeleteBtn) {
    closeDeleteBtn.addEventListener("click", () => closeModal(deleteModal));
  }

  if (cancelDeleteBtn) {
    cancelDeleteBtn.addEventListener("click", () => closeModal(deleteModal));
  }

  // Edit Modal Events
  editUserButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const teacherId = this.getAttribute("data-teacher-id");

      // Fetch teacher details via AJAX
      $.ajax({
        url: "edit_teacher.php",
        type: "POST",
        data: { teacher_id: teacherId },
        dataType: "json",
        success: function (response) {
          if (response.success && response.teacher) {
            // Populate form fields
            $("#editTeacherId").val(response.teacher.id);
            $("#editModal input[name='username']").val(
              response.teacher.name || ""
            );
            $("#editModal input[name='email']").val(
              response.teacher.email || ""
            );
            $("#editModal select[name='department']").val(
              response.teacher.department || ""
            );

            // Show the modal
            openModal(editModal);
          } else {
            alert(
              "Error: " + (response.message || "Failed to load teacher data")
            );
          }
        },
        error: function (xhr) {
          alert("Error loading teacher: " + xhr.statusText);
        },
      });
    });
  });

  if (editCloseBtn) {
    editCloseBtn.addEventListener("click", () =>
      closeModal(editModal, editForm)
    );
  }

  if (editCancelBtn) {
    editCancelBtn.addEventListener("click", () =>
      closeModal(editModal, editForm)
    );
  }

  // ====== CLICK OUTSIDE TO CLOSE MODALS ======
  [addModal, deleteModal, editModal].forEach((modal) => {
    if (modal) {
      modal.addEventListener("click", function (e) {
        if (e.target === modal) {
          closeModal(
            modal,
            modal === addModal ? addForm : modal === editModal ? editForm : null
          );
        }
      });
    }
  });

  // ====== ESC KEY TO CLOSE MODALS ======
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      if (addModal && addModal.classList.contains("active")) {
        closeModal(addModal, addForm);
      } else if (deleteModal && deleteModal.classList.contains("active")) {
        closeModal(deleteModal);
      } else if (editModal && editModal.classList.contains("active")) {
        closeModal(editModal, editForm);
      }
    }
  });

  // ====== PASSWORD TOGGLE FUNCTIONALITY ======
  passwordToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const input = this.previousElementSibling;
      const isPassword = input.type === "password";

      input.type = isPassword ? "text" : "password";
      this.innerHTML = isPassword
        ? '<i class="fas fa-eye-slash"></i>'
        : '<i class="fas fa-eye"></i>';
      this.setAttribute(
        "aria-label",
        isPassword ? "Hide password" : "Show password"
      );
    });
  });

  // ====== REAL-TIME PASSWORD VALIDATION ======
  if (passwordInput) {
    passwordInput.addEventListener("input", function () {
      checkPasswordStrength(this.value);
      checkPasswordMatch();
    });
  }

  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener("input", checkPasswordMatch);
  }

  // ====== FORM SUBMISSION HANDLERS ======

  // Save Changes (Edit Form)
  if (saveBtn && editForm) {
    saveBtn.addEventListener("click", function (e) {
      e.preventDefault();

      // Create FormData object from the form
      let formData = new FormData(editForm);

      // Submit the form data
      $.ajax({
        url: "edit_teacher.php",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (response) {
          if (response.success) {
            window.location.reload();
          } else {
            alert("Error: " + (response.message || "Update failed"));
          }
        },
        error: function (xhr) {
          alert(
            "Error updating teacher: " +
              ((xhr.responseJSON && xhr.responseJSON.message) || xhr.statusText)
          );
        },
        complete: function () {
          closeModal(editModal, editForm);
        },
      });
    });
  }

  // Confirm Delete Action
  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener("click", function () {
      if (teacherIdToDelete) {
        fetch("delete_teacher.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({ teacher_id: teacherIdToDelete }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert(data.message);
              location.reload(); // Reload the page to reflect changes
            } else {
              alert(data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while deleting the teacher.");
          })
          .finally(() => {
            closeModal(deleteModal);
          });
      }
    });
  }
});

// Separate function for viewing students
function viewStudents(button) {
  var teacherId = button.getAttribute("data-teacher-id");
  window.location.href = "viewteacher_handle.php?teacher_id=" + teacherId;
}
