document.addEventListener("DOMContentLoaded", () => {
  const navLinks = document.querySelectorAll(".nav-link");
  const logoLink = document.querySelector(".nav-logo");

  // Get the current page URL
  const currentPage = window.location.pathname.split("/").pop();

  // Remove the active class from all links
  navLinks.forEach((link) => link.classList.remove("active"));

  // Set the active class based on the current page
  navLinks.forEach((link) => {
    if (link.getAttribute("href") === currentPage || (currentPage === "" && link.getAttribute("href") === "homepage.html")) {
      link.classList.add("active");
    }
  });

  // Add event listeners to update the active state on click
  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      navLinks.forEach((item) => item.classList.remove("active"));
      link.classList.add("active");

      // Save the clicked link's href to localStorage
      localStorage.setItem("activeLink", link.getAttribute("href"));
    });
  });

  // Handle clicking the logo link
  if (logoLink) {
    logoLink.addEventListener("click", () => {
      navLinks.forEach((item) => item.classList.remove("active"));
      navLinks[0].classList.add("active"); // Set "Home" as active
      localStorage.setItem("activeLink", navLinks[0].getAttribute("href"));
    });
  }
});
