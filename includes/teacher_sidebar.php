<?php 
$current_page = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/admin.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/image/icontitle.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Varela+Round&display=swap"
        rel="stylesheet">
    <title>HK Duty Tracker</title>
</head>

<body>
    <div class="wrapper">
        <div class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="../assets/image/University_of_Pangasinan_logo.png" alt="Logo">
                    <h2>HK Duty Tracker</h2>
                </div>
                <button class="close-sidebar"><i class="fas fa-times"></i></button>
            </div>
            <hr>
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'open' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="approve_duty.php"
                        class="<?php echo ($current_page == 'approve_duty.php') ? 'open' : ''; ?>">
                        <i class="fas fa-tasks"></i>Verify Duty Logs
                    </a>
                </li>
                <li>
                    <a href="export_logs.php"
                        class="<?php echo ($current_page == 'export_logs.php') ? 'open' : ''; ?>">
                        <i class="fas fa-file-export"></i> Export Reports
                    </a>
                </li>
                <li>
                    <a href="notification.php"
                        class="<?php echo ($current_page == 'notification.php') ? 'open' : ''; ?>">
                        <i class="fa-solid fa-bell"></i> Notifications
                    </a>
                </li>
                <li>
                    <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'open' : ''; ?>">
                        <i class="fa-regular fa-circle-user"></i> Profile Account
                    </a>
                </li>
                <li>
                    <a href="#" class="logout" onclick="openLogoutModal()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Logout Modal -->
        <div id="logoutModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeLogoutModal()">&times;</span>
                <!-- Add Exclamation Icon here -->
                <div class="modal-icon">
                    <i class="fas fa-exclamation-circle"></i> <!-- FontAwesome exclamation icon -->
                </div>
                <h2>Confirm Logout</h2>
                <p>Are you sure you want to logout?</p>
                <div class="modal-buttons">
                    <button onclick="closeLogoutModal()">Cancel</button>
                    <button onclick="confirmLogout()">Logout</button>
                </div>
            </div>
        </div>


        <script src="../assets/dashboard.js"></script>
</body>

</html>