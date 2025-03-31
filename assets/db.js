
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
function formatDate(date) {
    const options = { month: "short", day: "2-digit", year: "numeric" };
    return date.toLocaleDateString("en-US", options);
}

// Get today's date
const today = new Date();
const startDate = formatDate(today);
const endDate = formatDate(new Date(today.setDate(today.getDate() + 7)));

// Set input value
document.getElementById("dateRange").value = `${startDate} - ${endDate}`;


document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById("menu-toggle");
    const sidebar = document.querySelector(".dashboard-container .sidebar");
    const settingsButton = document.getElementById("settingsButton");
    const settingsSidebar = document.getElementById("settingsSidebar");
    const closeSettings = document.getElementById("closeSettings");
    const lightModeBox = document.getElementById("lightModeBox");
    const darkModeBox = document.getElementById("darkModeBox");
    const body = document.body;

    // Sidebar Toggle
    menuToggle.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
    });

    // Open Settings Panel
    settingsButton.addEventListener("click", function () {
        settingsSidebar.classList.add("open");
    });

    // Close Settings Panel
    closeSettings.addEventListener("click", function () {
        settingsSidebar.classList.remove("open");
    });

    // Apply Dark Mode
    function enableDarkMode() {
        body.classList.add("dark-mode");
        localStorage.setItem("theme", "dark-mode");
    }

    // Apply Light Mode
    function disableDarkMode() {
        body.classList.remove("dark-mode");
        localStorage.setItem("theme", "light-mode");
    }

    // Check saved theme in LocalStorage
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark-mode") {
        enableDarkMode();
        darkModeBox.classList.add("active");
        lightModeBox.classList.remove("active");
    } else {
        disableDarkMode();
        lightModeBox.classList.add("active");
        darkModeBox.classList.remove("active");
    }

    // Toggle Dark Mode
    darkModeBox.addEventListener("click", function () {
        enableDarkMode();
        darkModeBox.classList.add("active");
        lightModeBox.classList.remove("active");
    });

    // Toggle Light Mode
    lightModeBox.addEventListener("click", function () {
        disableDarkMode();
        lightModeBox.classList.add("active");
        darkModeBox.classList.remove("active");
    });

});
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById("menu-toggle");
    const sidebar = document.getElementById("sidebar");
    const settingsButton = document.getElementById("settingsButton");
    const settingsSidebar = document.getElementById("settingsSidebar");
    const closeSettings = document.getElementById("closeSettings");
    const lightModeBox = document.getElementById("lightModeBox");
    const darkModeBox = document.getElementById("darkModeBox");
    const body = document.body;

    // Sidebar Toggle
    menuToggle?.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
    });

    // Open Settings Panel
    settingsButton?.addEventListener("click", function () {
        settingsSidebar.classList.add("open");
    });

    // Close Settings Panel
    closeSettings?.addEventListener("click", function () {
        settingsSidebar.classList.remove("open");
    });

    // Apply Dark Mode
    function enableDarkMode() {
        body.classList.add("dark-mode");
        sidebar.classList.add("dark-mode");
        localStorage.setItem("theme", "dark-mode");
    }

    // Apply Light Mode
    function disableDarkMode() {
        body.classList.remove("dark-mode");
        sidebar.classList.remove("dark-mode");
        localStorage.setItem("theme", "light-mode");
    }

    // Check saved theme in LocalStorage
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark-mode") {
        enableDarkMode();
        darkModeBox?.classList.add("active");
        lightModeBox?.classList.remove("active");
    } else {
        disableDarkMode();
        lightModeBox?.classList.add("active");
        darkModeBox?.classList.remove("active");
    }

    // Toggle Dark Mode
    darkModeBox?.addEventListener("click", function () {
        enableDarkMode();
        darkModeBox.classList.add("active");
        lightModeBox.classList.remove("active");
    });

    // Toggle Light Mode
    lightModeBox?.addEventListener("click", function () {
        disableDarkMode();
        lightModeBox.classList.add("active");
        darkModeBox.classList.remove("active");
    });
});
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
        // Backend Connection Point 1: Fetch initial data
        // Replace this static data with API call to your backend
        // Example: fetch('/api/chart-data').then(...)

        const departmentData = {
            labels: ['CEA', 'CITE', 'CMA', 'CAHS', 'CCJE', 'CELA'], // Department names
            datasets: [{
                label: 'Number of Scholars',
                data: [125, 95, 80, 115, 90, 65], // Number of scholars per department
                backgroundColor: [
                    'rgba(54, 162, 235, 0.5)', // CEA
                    'rgba(255, 99, 132, 0.5)', // CITE
                    'rgba(75, 192, 192, 0.5)', // CMA
                    'rgba(153, 102, 255, 0.5)', // CAHS
                    'rgba(255, 159, 64, 0.5)', // CCJE
                    'rgba(201, 203, 207, 0.5)' // CELA
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)'
                ],
                borderWidth: 1
            }]
        };

        // Chart configuration for a bar graph
        const config = {
            type: 'bar',
            data: departmentData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Number of Scholars in Each Department'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 200, // Increase this value
                        ticks: {
                            stepSize: 20 // Ensure ticks increment by 20
                        },
                        grid: {
                            color: '#e0e0e0'
                        },
                        title: {
                            display: true,
                            text: 'Number of Scholars'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Departments'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        };

        // Initialize chart
        const ctx = document.getElementById('scholarChart').getContext('2d');
        const scholarChart = new Chart(ctx, config);

        // Backend Connection Point 2: Real-time updates
        // Add WebSocket or periodic fetch here to update chart data
        // Example: setInterval(() => fetchNewData(), 5000)

        // Sample update function (replace with actual backend call)
        function updateChart(newData) {
            scholarChart.data.labels = newData.labels;
            scholarChart.data.datasets[0].data = newData.data;
            scholarChart.update();
        }

        // Example of updating the chart with new data
        setTimeout(() => {
            const newData = {
                labels: ['CEA', 'CITE', 'CMA', 'CAHS', 'CCJE', 'CELA'],
                data: [140, 100, 85, 120, 95, 70] // Updated scholar counts
            };
            updateChart(newData);
        }, 5000); // Simulate an update after 5 seconds

        

