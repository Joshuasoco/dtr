<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Both email and password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin) {
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Incorrect password.";
            }
        } else {
            $_SESSION['error'] = "Email not found.";
        }
    }

    // Redirect back to login page to display errors
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="icon" href="../images/icontitle.png" />
    <link rel="stylesheet" type="text/css" href="../assets/login_admin.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Anek+Devanagari:wght@100..800&family=Jost:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Bebas+Neue&family=Poppins:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&family=Varela+Round&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="left">
            <img src="../assets/image/Rectangle bg.png" alt="University Logo">
        </div>
        <div class="right">
            <img src="../assets/image/phinmaed-logo.png" alt="Sign In Icon" class="signin-icon">
            <h2>Admin Login</h2>
            <p>Login to your Admin account</p>
            <form action="" method="POST">
                <?php if (!empty($_SESSION['error'])): ?>
                <div class="error-message" id="error-message">
                    <span class="error-icon">⚠️</span>
                    <span><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                    <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
                </div>
                <script>
                setTimeout(function() {
                    document.getElementById("error-message").style.display = "none";
                }, 1000); // Hide after 3 seconds
                </script>
                <?php unset($_SESSION['error']); // Clear error after displaying ?>
                <?php endif; ?>



                <label for="email">Email</label>
                <div class="email">
                    <input type="text" name="email" placeholder="Enter Admin" required>
                    <img src="../assets/image/email-svgrepo-com.svg" alt="email" class="email-icon">
                </div>
                <label for="password">Password</label>

                <div class="password">
                    <input type="password" name="password" placeholder="Enter your Password" id="password" required>
                    <img src="../assets/image/eye-closed-svgrepo-com.svg" alt="hide" onclick="pass()" class="pass-icon"
                        id="pass-icon">
                </div>

                <button type="submit" class="btn">Sign in</button>
            </form>

            <div class="divider"></div>

            <p class="terms">
                By clicking continue, you agree to our

                <button id="tos_button" class="tos-btn">Terms of Service</button>
                and
                <a href="#">Privacy Policy</a>.
            </p>
        </div>
    </div>
    <div class="tos-modal" id="tos_modal">
        <div class="tos-content">
            <!--gawa ka bagong file ganto-->
            <?php include '../admin/ToS.php';?>
        </div>
    </div>

    <div class="custom-shape-divider-bottom-1737291662">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path
                d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"
                class="shape-fill"></path>
        </svg>
    </div>
    <!-- Wave Line SVG -->
</body>

<script>
function pass() {
    let passwordInput = document.getElementById("password");
    let passIcon = document.getElementById("pass-icon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        passIcon.src = "../assets/image/eye-open-svgrepo-com.svg";
    } else {
        passwordInput.type = "password";
        passIcon.src = "../assets/image/eye-closed-svgrepo-com.svg";
    }
}

const tosmodal = document.getElementById("tos_modal");
const openbutton = document.getElementById("tos_button");
const accept = document.getElementById("accept");
const decline = document.getElementById("decline");

/*wrap to prevent errors 
js won’t try to add event listeners to null elements.*/
if (accept) {
    accept.addEventListener("click", () => {
        tosmodal.style.display = "none";
        alert("Accepted Terms of Service");
    });
}

if (decline) {
    decline.addEventListener("click", () => {
        tosmodal.style.display = "none";
    });
}

if (openbutton) {
    openbutton.addEventListener("click", () => {
        if (tosmodal) tosmodal.style.display = "flex";
    });
}

if (tosmodal) {
    window.addEventListener("click", (e) => {
        if (e.target === tosmodal) {
            tosmodal.style.display = "none";
        }
    });
}
</script>

</html>