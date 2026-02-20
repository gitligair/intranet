import "./styles/app.scss";

// start the Stimulus application
import "./bootstrap.js";

import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".ckeditor").forEach((element) => {
    ClassicEditor.create(element).catch((error) => console.error(error));
  });

  // Change navbar style on scroll
  window.addEventListener("scroll", function () {
    const navbar = document.getElementById("mainNavbar");
    if (window.scrollY > 50) {
      navbar.classList.add("scrolled");
    } else {
      navbar.classList.remove("scrolled");
    }
  });
});
