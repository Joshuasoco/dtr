document.addEventListener("DOMContentLoaded", () => {
  const hamburger = document.querySelector('.hamburger');
  const navMenu = document.querySelector('.nav-menu');
  const navLinks = document.querySelectorAll(".nav-link");
  const logoLink = document.querySelector(".nav-logo");

  //update active state
  const updateActiveState = (href) => {
    navLinks.forEach(link => link.classList.remove('active'));
    navLinks.forEach(link => {
      if (link.getAttribute('href') === href) {
        link.classList.add('active');
      }
    });
  };

  
  const currentPage = window.location.pathname.split("/").pop() || "homepage.html";
  updateActiveState(currentPage);

  
  hamburger?.addEventListener('click', () => {
    hamburger.textContent = hamburger.textContent === '☰' ? '✕' : '☰';
    navMenu.classList.toggle('active');
    document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : 'auto';
  });

  
  document.addEventListener('click', (e) => {
    if (!navMenu?.contains(e.target) && !hamburger?.contains(e.target)) {
      navMenu?.classList.remove('active');
      if (hamburger) hamburger.textContent = '☰';
      document.body.style.overflow = 'auto';
    }
  });

  // Handle navigation clicks
  navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      // Close mobile menu if open
      navMenu?.classList.remove('active');
      if (hamburger) hamburger.textContent = '☰';
      document.body.style.overflow = 'auto';

      
      const href = link.getAttribute('href');
      sessionStorage.setItem('activeLink', href);
    });
  });

  // Logo click handler
  logoLink?.addEventListener('click', () => {
    sessionStorage.setItem('activeLink', 'homepage.html');
  });

  // Check sessionStorage for active link on page load
  const storedActiveLink = sessionStorage.getItem('activeLink');
  if (storedActiveLink) {
    updateActiveState(storedActiveLink);
  }
});