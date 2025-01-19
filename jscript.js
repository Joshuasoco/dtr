document.addEventListener("DOMContentLoaded", () => {
  const navLinks = document.querySelectorAll(".nav-link");

  navLinks.forEach((link) => link.classList.remove("active"));
/*navigation hovering*/
  navLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      navLinks.forEach((item) => item.classList.remove("active"));
      link.classList.add("active");
    });
  });
});
/*logoutprocess*/
navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    navLinks.forEach((item) => item.classList.remove("active"));
    link.classList.add("active");
    localStorage.setItem("activeLink", link.getAttribute("href"));
  });
});
