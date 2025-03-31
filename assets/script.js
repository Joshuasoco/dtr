document.addEventListener("DOMContentLoaded", function () {
    console.log("Script loaded successfully!");

    // Toggle sidebar menu (for Admin/Student dashboards)
    const toggleSidebar = document.getElementById("menu-toggle");
    if (toggleSidebar) {
        toggleSidebar.addEventListener("click", function () {
            document.body.classList.toggle("sidebar-collapsed");
        });
    }

    // AJAX Request Example: Fetch Notifications in Real-Time
    function fetchNotifications() {
        fetch("notifications.php")
            .then(response => response.json())
            .then(data => {
                let notificationBox = document.getElementById("notification-box");
                if (notificationBox) {
                    notificationBox.innerHTML = "";
                    data.forEach(notification => {
                        let item = document.createElement("div");
                        item.classList.add("notification-item");
                        item.innerHTML = `<p>${notification.message}</p>`;
                        notificationBox.appendChild(item);
                    });
                }
            })
            .catch(error => console.error("Error fetching notifications:", error));
    }

    setInterval(fetchNotifications, 30000); // Auto-refresh notifications every 30 seconds

    // Chart.js Example (Dashboard Analytics)
    if (document.getElementById("dutyChart")) {
        fetch("analytics.php")
            .then(response => response.json())
            .then(data => {
                let ctx = document.getElementById("dutyChart").getContext("2d");
                new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: "Duty Hours",
                            data: data.values,
                            backgroundColor: "rgba(54, 162, 235, 0.5)",
                            borderColor: "rgba(54, 162, 235, 1)",
                            borderWidth: 1
                        }]
                    }
                });
            })
            .catch(error => console.error("Error loading chart:", error));
    }
});
