document.addEventListener("DOMContentLoaded", () => {
  const navLinks = document.querySelectorAll(".nav-link");

  // Remove all active classes first
  navLinks.forEach((link) => link.classList.remove("active"));

  // Add click event listeners
  navLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      // Remove active class from all links
      navLinks.forEach((item) => item.classList.remove("active"));
      // Add active class only to clicked link
      link.classList.add("active");
    });
  });
});

navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    // Remove 'active' class from all links
    navLinks.forEach((item) => item.classList.remove("active"));
    // Add 'active' class to the clicked link
    link.classList.add("active");
    // Store the active link's href in localStorage
    localStorage.setItem("activeLink", link.getAttribute("href"));
  });
});
