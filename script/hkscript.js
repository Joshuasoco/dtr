function show_logout() {
    console.log("Show logout popup");
    document.getElementById("logout_warning").style.display = "block";
  }
  function hide_logout() {
    document.getElementById("logout_warning").style.display = "none";
  }
  
  function logout() {
    document.getElementById("logout_warning").style.display = "none";
    console.log("Logout");
    //dito yung link ng login page sa href
    window.location.href = "/htmlsidebar/html_sidebar.html";
  }