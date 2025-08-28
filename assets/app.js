import "./styles/app.scss";

// start the Stimulus application
import "./bootstrap.js";

// Change navbar style on scroll
window.addEventListener("scroll", function () {
  const navbar = document.getElementById("mainNavbar");
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});
