<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle delete request
if (isset($_GET['delete'])) {
    $student_id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        if ($stmt->execute([$student_id])) {
            header('Location: student_profiles.php?success=deleted');
            exit();
        }
    } catch (PDOException $e) {
        $error = "Error deleting student: " . $e->getMessage();
    }
}
/*Handle delete student
if (isset($_GET['delete'])) {
    $student_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    if ($stmt->execute([$student_id])) {
        header('Location: student_profiles.php');
        exit();
    } else {
        echo "Error deleting student profile.";
    }
}*/
// Fetch students with their total approved hours
$stmt = $pdo->prepare("
    SELECT s.*, 
    COALESCE(SUM(dl.hours_worked), 0) as total_hours
    FROM students s
    LEFT JOIN duty_logs dl ON s.student_id = dl.student_id AND dl.status = 'Approved'
    GROUP BY s.student_id, s.id, s.name, s.scholarship_type, s.course, s.department, s.year_level, s.hk_duty_status, s.created_at
");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profiles</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="icon" href="../assets/image/icontitle.png" />
    <script src="../assets/dashboard.js"></script>
    <script src="../assets/search_filter.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Match with approved_duties.php -->

        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <!-- Match with approved_duties.php -->
            <header class="header-container">
                <h2><i class="fa fa-users"></i> Student Profiles</h2>

                <div class="header-right">
                    <div class="search-sort-container">
                        <div class="search-container">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search...">
                        </div>

                        <div class="dropdown">
                            <img src="../assets/image/sort-icon.jpg" alt="Sort" onclick="toggleDropdown()">
                            <div class="dropdown-content" id="dropdown">
                                <select id="sortSelect">
                                    <option value="" disabled selected>--Filter--</option>
                                    <option value="id">ID</option>
                                    <option value="student_id">Student ID</option>
                                    <option value="name">Name</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </header>

            <section class="table-container">
                <div class="table-content">
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th class="sortable" data-column="student_id">Student ID</th>
                                <th class="sortable" data-column="name">Name</th>
                                <th class="sortable" data-column="scholar_type">Scholar Type</th>
                                <th class="sortable" data-column="course">Course</th>
                                <th class="sortable" data-column="department">Department</th>
                                <th class="sortable" data-column="year_level">Year Level</th>
                                <th class="sortable" data-column="duty_status">HK Duty Status</th>
                                <th class="sortable" data-column="total_hours">Total Hours</th>
                                <th class="sortable" data-column="created_at">Registered Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <?php 
                                // Determine required duty hours based on scholarship type
                                $required_hours = 90; // Default required hours
                                if ($student['scholarship_type'] == "HK 25") {
                                    $required_hours = 50;
                                } elseif ($student['scholarship_type'] == "HK 50") {
                                    $required_hours = 90;
                                } elseif ($student['scholarship_type'] == "HK 75") {
                                    $required_hours = 120;
                                }
                            ?>
                            <tr>
                            <td data-column="student_id"><?php echo htmlspecialchars($student['student_id']); ?>
                            </td>
                                <td data-column="name"><?php echo htmlspecialchars($student['name']); ?></td>

                                <td data-column="scholarship_type">
                                    <?php echo htmlspecialchars($student['scholarship_type']); ?></td>
                                <td data-column="course"><?php echo htmlspecialchars($student['course']); ?></td>
                                <td data-column="department"><?php echo htmlspecialchars($student['department']); ?>
                                </td>
                                <td data-column="year_level"><?php echo htmlspecialchars($student['year_level']); ?>
                                </td>
                                <td data-column="hk_duty_status">
                                    <?php echo htmlspecialchars($student['hk_duty_status']); ?></td>

                                <td data-column="total_hours">
                                                                    <?php
                                    // Assuming $student['total_hours'] contains the decimal value
                                    $total_hours_decimal = $student['total_hours'];
                                    
                                    // Extract hours and minutes from the decimal value
                                    $hours = floor($total_hours_decimal); // Get the integer part for hours
                                    $minutes = round(($total_hours_decimal - $hours) * 60); // Convert decimal part to minutes
                                    
                                    // Determine required duty hours based on scholarship type
                                    $required_hours = 90; // Default required hours
                                    if ($student['scholarship_type'] == "HK 25") {
                                        $required_hours = 50;
                                    } elseif ($student['scholarship_type'] == "HK 50") {
                                        $required_hours = 90;
                                    } elseif ($student['scholarship_type'] == "HK 75") {
                                        $required_hours = 120;
                                    }

                                    // Display total hours in "X hr Y min" format and required hours
                                    echo "$hours hr " . ($minutes > 0 ? "$minutes m" : "") . " / $required_hours hrs";
                                    ?>
                                </td>

                                <td><?php echo date('Y-m-d', strtotime($student['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Edit Button -->
                                        <a href="#" class="edit-btn btn" data-id="<?php echo $student['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($student['name']); ?>"
                                            data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>"
                                            data-email="<?php echo htmlspecialchars($student['email']); ?>"
                                            data-course="<?php echo htmlspecialchars($student['course']); ?>"
                                            data-department="<?php echo htmlspecialchars($student['department']); ?>"
                                            data-scholarship-type="<?php echo htmlspecialchars($student['scholarship_type']); ?>"
                                            data-hk-duty-status="<?php echo htmlspecialchars($student['hk_duty_status']); ?>"
                                            data-year-level="<?php echo htmlspecialchars($student['year_level']); ?>">
                                            <i class="fa-solid fa-pencil"></i> <!-- Edit Icon -->
                                        </a>
                                        <a href="#" class="delete-student btn" data-id="<?php echo $student['id']; ?>">
                                            <i class="fa fa-trash"></i> <!-- Delete Icon -->
                                        </a>
                                    </div>
                                </td>

                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
            </section>
        </main>
    </div>
    <!-- Edit Student Modal -->
    <div id="editStudentModal" class="edit-modal">
        <div class="edit-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Student Profile</h2>
            <div id="modalMessage"></div>

            <form id="editStudentForm">
                <input type="hidden" id="studentId" name="id">

                <label><i class="fas fa-user"></i> Name</label>
                <input type="text" id="studentName" name="name" placeholder="Name" required>

                <label><i class="fas fa-id-card"></i> Student ID</label>
                <input type="text" id="studentStudentID" name="student_id" placeholder="Student ID" required>


                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="studentEmail" name="email" placeholder="Email" required>

                <label><i class="fa-solid fa-graduation-cap"></i> Course</label>
                <select name="course" id="studentCourse" required>
                    <option value="" disabled selected>Select Course</option>
                    <option value="BSIT">BS Information Technology</option>
                    <option value="BSCS">BS Computer Science</option>
                    <option value="BSE">BS Education</option>
                    <option value="BBA">BS Business Administration</option>
                    <option value="BSCRIM">BS Criminology</option>
                    <option value="BSA">BS Accountancy</option>
                    <option value="BSN">BS Nursing</option>
                    <option value="BSARCH">BS Architecture</option>
                    <option value="BSCOE">BS Computer Engineering</option>
                    <option value="BSEE">BS Electrical Engineering</option>
                </select>

                <label><i class="fas fa-building"></i> Department</label>
                <select name="department" id="studentDepartment" required>
                    <option value="" disabled selected>Select Department</option>
                    <option value="CEA">CEA</option>
                    <option value="CMA">CMA</option>
                    <option value="CAHS">CAHS</option>
                    <option value="CITE">CITE</option>
                    <option value="CCJE">CCJE</option>
                    <option value="CELA">CELA</option>
                </select>

                <label><i class="fas fa-layer-group"></i> Year Level</label>
                <select name="year_level" id="studentYearLevel" required>
                    <option value="" disabled selected>Select Year Level</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                    <option value="5th Year">5th Year</option>
                </select>

                <label><i class="fas fa-tasks"></i> HK Duty Status</label>
                <select name="hk_duty_status" id="studentHKDutyStatus" required>
                    <option value="" disabled selected>Select Duty Status</option>
                    <option value="Module Distributor">Module Distributor</option>
                    <option value="Student Facilitator">Student Facilitator</option>
                    <option value="Library Assistant">Library Assistant</option>
                    <option value="Admin Assistant">External Facilitator</option>
                </select>


                <label><i class="fas fa-award"></i> Scholarship Type</label>
                <select name="scholarship_type" id="studentScholarshipType" required>
                    <option value="" disabled selected>Select Scholarship Type</option>
                    <option value="HK 25">HK 25</option>
                    <option value="HK 50">HK 50</option>
                    <option value="HK 75">HK 75</option>
                </select>

                <div class="buttons">
                    <button type="button" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="approve-button">Update</button>
                </div>
            </form>
        </div>
    </div>
    <!-- delete modal-->
    <div id="myModal" class="student-delete-overlay">
        <div class="modal-admin">
            <div class="student-delete-header">
                <h3 class="student-delete-title">Confirm Deletion</h3>
                <button class="admin-modal-close" id="student_close"><i class="fas fa-times"></i></button>
            </div>
            <div class="student-delete-body">
                <p>Are you sure you want to delete this student?</p>
            </div>
            <div class="student-delete-foooter">
                <button class="student-cancel" id="student_close">Cancel</button>
                <button class="student-delete" id="student_delete">Delete</button>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function(){
        const deleteModal= document.getElementById("myModal");
        const deleteButtons = document.querySelectorAll(".delete-student");
        const closeModalButtons = document.querySelectorAll("#student_close");
        const confirmDeleteButton = document.getElementById("student_delete");
        
        let studentId = null;
        
        //open modal deletion for student
        deleteButtons.forEach(button =>{
            button.addEventListener("click", function(event){
                event.preventDefault();
                deleteModal.style.display ="block";
                studentId = this.getAttribute("data-id");
                deleteModal.style.display = "block";

            });
        });
        //close times modal deletion for Student
        closeModalButtons.forEach(button => {
            button.addEventListener("click", function(){
                deleteModal.style.display = "none";
                studentId = null;
            });
        });
        //close modal if click outside
        window.addEventListener("click", function(event){
            if(event.target == deleteModal){
                deleteModal.style.display = "none";
                studentId = null;
            }
        })
        //confirm deletion of the student button
        confirmDeleteButton.addEventListener("click", function(){
            if(studentId){
                window.location.href = `student_profiles.php?delete=${studentId}`;
            }
        });
    });

    // Get the modal and elements
    var modal = document.getElementById("editStudentModal");
    var closeBtn = document.getElementsByClassName("close")[0];

    // Handle edit button click
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function() {
            document.getElementById("studentId").value = this.dataset.id;
            document.getElementById("studentName").value = this.dataset.name;
            document.getElementById("studentStudentID").value = this.dataset.studentId;
            document.getElementById("studentEmail").value = this.dataset.email;
            document.getElementById("studentCourse").value = this.dataset.course;
            document.getElementById("studentDepartment").value = this.dataset.department;
            document.getElementById("studentScholarshipType").value = this.dataset.scholarshipType;
            document.getElementById("studentHKDutyStatus").value = this.dataset.hkDutyStatus;
            document.getElementById("studentYearLevel").value = this.dataset.yearLevel;

            document.getElementById("editStudentModal").style.display = "block";
        });
    });

    function closeModal() {
        document.getElementById("editStudentModal").style.display = "none";
    }

    document.getElementById("editStudentForm").onsubmit = function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        fetch("edit_student_profile.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById("modalMessage").innerHTML = data;
                setTimeout(() => {
                    closeModal();
                    location.reload(); // Refresh table after update
                }, 1500);
            });
    };
    </script>


</body>

</html>