
document.addEventListener("DOMContentLoaded", function() {
    const colorCircles = document.querySelectorAll(".color-circle");
    const sidebar = document.querySelector(".sidebar");
    const settingsButton = document.getElementById("settingsButton");
    const settingsSidebar = document.getElementById("settingsSidebar");
    const closeSettings = document.getElementById("closeSettings");
    const lightModeBox = document.getElementById("lightModeBox");
    const darkModeBox = document.getElementById("darkModeBox");
    const bgOptions = document.querySelectorAll(".bg-option");
    const removeBgButton = document.getElementById("removeBgImage");
    const tableHeaders = document.querySelectorAll("th"); // Select all table headers

    // Load saved sidebar color or set default
    let savedColor = localStorage.getItem("sidebarColor") || "#343a40";
    let savedTheme = localStorage.getItem("theme") || "light-mode";
    let savedBgImage = localStorage.getItem("bgImage");

    // 📌 Function to Apply Sidebar & Table Header Color
    function applySidebarColor(color) {
        if (document.body.classList.contains("light-mode")) {
            document.documentElement.style.setProperty("--theme-color", color);
            localStorage.setItem("sidebarColor", color);
            
            // Apply the same color to table headers
            tableHeaders.forEach(th => {
                th.style.backgroundColor = color;
                th.style.color = "#fff"; // Ensure text remains readable
            });
        }
    }

    // 📌 Function to Apply Theme Mode
    function applyTheme(theme) {
        document.body.classList.remove("light-mode", "dark-mode");
        document.body.classList.add(theme);
        localStorage.setItem("theme", theme);

        if (theme === "dark-mode") {
            document.documentElement.style.setProperty("--theme-color", "#222"); // Dark Sidebar

            // Dark mode: Change table headers to a dark color
            tableHeaders.forEach(th => {
                th.style.backgroundColor = "#333";
                th.style.color = "#fff";
            });
        } else {
            applySidebarColor(savedColor); // Restore Saved Sidebar Color in Light Mode
        }

        // Sidebar Text Color Always White
        document.documentElement.style.setProperty("--sidebar-text-color", "#fff");

        // Toggle active state
        lightModeBox.classList.toggle("active", theme === "light-mode");
        darkModeBox.classList.toggle("active", theme === "dark-mode");
    }

    // 📌 Apply saved settings
    applyTheme(savedTheme);
    applySidebarColor(savedColor);

    if (savedBgImage) {
        document.body.style.backgroundImage = `url('../assets/image/${savedBgImage}')`;
    }

    // 📌 Change Sidebar & Table Header Color on Selection
    colorCircles.forEach(circle => {
        circle.addEventListener("click", function() {
            let selectedColor = this.getAttribute("data-color");
            savedColor = selectedColor;
            applySidebarColor(selectedColor);
        });
    });

    // 📌 Light & Dark Mode Toggle
    lightModeBox.addEventListener("click", () => applyTheme("light-mode"));
    darkModeBox.addEventListener("click", () => applyTheme("dark-mode"));

    // 📌 Background Image Selection
    bgOptions.forEach(img => {
        img.addEventListener("click", function() {
            let selectedImage = this.getAttribute("data-image");
            document.body.style.backgroundImage = `url('../assets/image/${selectedImage}')`;
            localStorage.setItem("bgImage", selectedImage);
        });
    });

    // 📌 Remove Background Image
    removeBgButton.addEventListener("click", function() {
        document.body.style.backgroundImage = "none";
        localStorage.removeItem("bgImage");
    });

    // 📌 Settings Panel Toggle
    settingsButton.addEventListener("click", function() {
        settingsSidebar.classList.add("open");
    });

    closeSettings.addEventListener("click", function() {
        settingsSidebar.classList.remove("open");
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (menuToggle && sidebar && mainContent) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('expanded');
        });
    }
    
    // Close sidebar when clicking the close button
    const closeSidebar = document.querySelector('.close-sidebar');
    if (closeSidebar && sidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
            if (mainContent) {
                mainContent.classList.add('expanded');
            }
        });
    }
    
    // Settings panel toggle
    const settingsButton = document.getElementById('settingsButton');
    const settingsSidebar = document.getElementById('settingsSidebar');
    const closeSettings = document.getElementById('closeSettings');
    
    if (settingsButton && settingsSidebar && closeSettings) {
        settingsButton.addEventListener('click', function() {
            settingsSidebar.classList.add('active');
        });
        
        closeSettings.addEventListener('click', function() {
            settingsSidebar.classList.remove('active');
        });
    }
    
    // Theme selection
    const themeBoxes = document.querySelectorAll('.theme-box');
    
    themeBoxes.forEach(box => {
        box.addEventListener('click', function() {
            // Remove active class from all boxes
            themeBoxes.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked box
            this.classList.add('active');
            
            // Get theme from data attribute
            const theme = this.getAttribute('data-theme');
            
            // Apply theme (body class change)
            document.body.className = theme;
            
            // Save preference to localStorage
            localStorage.setItem('preferred-theme', theme);
        });
    });
    
    // Color theme selection
    const colorCircles = document.querySelectorAll('.color-circle');
    
    colorCircles.forEach(circle => {
        circle.addEventListener('click', function() {
            // Remove active class from all circles
            colorCircles.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked circle
            this.classList.add('active');
            
            // Get color from data attribute
            const color = this.getAttribute('data-color');
            
            // Apply color to sidebar
            if (sidebar) {
                sidebar.style.background = color;
            }
            
            // Save preference to localStorage
            localStorage.setItem('sidebar-color', color);
        });
    });
    
    // Background image selection
    const bgOptions = document.querySelectorAll('.bg-option');
    const removeBgBtn = document.getElementById('removeBgImage');
    
    bgOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            bgOptions.forEach(o => o.classList.remove('active'));
            
            // Add active class to clicked option
            this.classList.add('active');
            
            // Get image from data attribute
            const image = this.getAttribute('data-image');
            
            // Apply background to main-content
            if (mainContent) {
                mainContent.style.backgroundImage = `url('../assets/image/${image}')`;
                mainContent.style.backgroundSize = 'cover';
                mainContent.style.backgroundAttachment = 'fixed';
            }
            
            // Save preference to localStorage
            localStorage.setItem('bg-image', image);
        });
    });
    
    if (removeBgBtn) {
        removeBgBtn.addEventListener('click', function() {
            // Remove background from main-content
            if (mainContent) {
                mainContent.style.backgroundImage = 'none';
                mainContent.style.background = '#f5f7fa';
            }
            
            // Remove active class from all options
            bgOptions.forEach(o => o.classList.remove('active'));
            
            // Remove preference from localStorage
            localStorage.removeItem('bg-image');
        });
    }
    
    // Load saved preferences from localStorage
    const loadSavedPreferences = () => {
        // Load theme
        const savedTheme = localStorage.getItem('preferred-theme');
        if (savedTheme) {
            document.body.className = savedTheme;
            
            // Update active theme box
            themeBoxes.forEach(box => {
                if (box.getAttribute('data-theme') === savedTheme) {
                    box.classList.add('active');
                } else {
                    box.classList.remove('active');
                }
            });
        }
        
        // Load sidebar color
        const savedColor = localStorage.getItem('sidebar-color');
        if (savedColor && sidebar) {
            sidebar.style.background = savedColor;
            
            // Update active color circle
            colorCircles.forEach(circle => {
                if (circle.getAttribute('data-color') === savedColor) {
                    circle.classList.add('active');
                } else {
                    circle.classList.remove('active');
                }
            });
        }
        
        // Load background image
        const savedBgImage = localStorage.getItem('bg-image');
        if (savedBgImage && mainContent) {
            mainContent.style.backgroundImage = `url('../assets/image/${savedBgImage}')`;
            mainContent.style.backgroundSize = 'cover';
            mainContent.style.backgroundAttachment = 'fixed';
        }
        
        // Check if sidebar should be active based on screen size
        const checkSidebarState = () => {
            if (window.innerWidth <= 991) {
                sidebar.classList.remove('active');
                mainContent.classList.add('expanded');
            } else {
                sidebar.classList.remove('active');
                mainContent.classList.remove('expanded');
            }
        };
        
        // Initialize sidebar state
        checkSidebarState();
        
        // Update on window resize
        window.addEventListener('resize', checkSidebarState);
    };
    
    // Call function to load saved preferences
    loadSavedPreferences();
});
// calendar
document.addEventListener("DOMContentLoaded", function () {
    function formatDateWithTime(date) {
        const options = { year: "numeric", month: "short", day: "2-digit" };
        const formattedDate = date.toLocaleDateString("en-US", options);

        let hours = date.getHours();
        let minutes = date.getMinutes();
        let ampm = hours >= 12 ? "PM" : "AM";

        hours = hours % 12 || 12; // Convert 24-hour to 12-hour format
        minutes = minutes < 10 ? "0" + minutes : minutes; // Ensure two digits for minutes

        return `${formattedDate} - ${hours}:${minutes} ${ampm}`;
    }

    function updateDateTime() {
        const now = new Date();
        const formattedDateTime = formatDateWithTime(now);
        document.getElementById("dateRange").value = formattedDateTime;
    }

    // Initialize Flatpickr for date selection
    flatpickr("#dateRange", {
        enableTime: true, // Enables time selection
        dateFormat: "M d, Y - h:i K", // Format: Mar 09, 2025 - 07:22 PM
        defaultDate: new Date(),
        time_24hr: false, // Use 12-hour format with AM/PM
        onClose: function(selectedDates, dateStr) {
            document.getElementById("dateRange").value = dateStr;
        }
    });

    // Update the date-time immediately
    updateDateTime();
});


// logout modal

function openLogoutModal() {
    document.getElementById('logoutModal').style.display = 'block';
}

function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}

function confirmLogout() {
    window.location.href = 'logout.php';
}

// Close the modal if the user clicks outside of it
window.onclick = function(event) {
    const modal = document.getElementById('logoutModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
   // Toggle notification dropdown
   document.getElementById("notificationToggle").addEventListener("click", function() {
    var menu = document.getElementById("notificationMenu");
    menu.classList.toggle("show");
});

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
    if (!event.target.matches('.notification-icon') && !event.target.matches('.notification-icon *')) {
        var dropdowns = document.getElementsByClassName("dropdown-menu");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
};

// Mark notification as read
document.querySelectorAll(".notification-item").forEach(item => {
    item.addEventListener("click", function() {
        var notificationId = this.dataset.id;
        this.parentElement.classList.remove("unread");
        // Add AJAX call to mark notification as read in the database
        // Example:
        // fetch(`mark_notification_read.php?id=${notificationId}`, { method: 'POST' });
    });
});

// Analytics Chart
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById("scholarChart").getContext("2d");

    if (typeof departments !== "undefined" && typeof studentCounts !== "undefined") {
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: departments,
                datasets: [{
                    label: "Total Students Per Department",
                    data: studentCounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    } else {
        console.error("Chart data is missing.");
    }
});



document.addEventListener("DOMContentLoaded", function () {
    function updateNotificationBadge() {
        fetch('../admin/fetch_notifications.php')
            .then(response => response.json())
            .then(data => {
                let notificationCount = document.getElementById('notificationCount');
                if (data.count > 0) {
                    notificationCount.textContent = data.count;
                    notificationCount.style.display = 'inline-block';
                } else {
                    notificationCount.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    // Fetch notifications initially
    updateNotificationBadge();

    // Refresh notifications every 30 seconds
    setInterval(updateNotificationBadge, 30000);
});
document.querySelectorAll('.has-submenu > a').forEach(item => {
    item.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) { // Mobile only
            e.preventDefault();
            const submenu = item.parentElement.querySelector('.submenu');
            const isActive = submenu.style.maxHeight !== '0px';
            
            // Close all submenus
            document.querySelectorAll('.submenu').forEach(sub => {
                sub.style.maxHeight = null;
            });
            
            // Toggle current
            if (!isActive) {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
            }
        }
    });
});