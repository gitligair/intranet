document.addEventListener("DOMContentLoaded", function () {
  //initialisation en affichant que la section services
  showSectionById("services");

  document.querySelectorAll(".lien").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      // Remove active class from all nav links
      document
        .querySelectorAll(".lien")
        .forEach((l) => l.classList.remove("active"));

      // Add active class to clicked nav link
      this.classList.add("active");

      // cacher toutes les sections
      // et afficher la section cible
      const targetId = this.getAttribute("data-target");
      showSectionById(targetId);

      // Scroll en haut si pas deja le cas
      if (!isScrollAtTop()) {
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
    });
  });

  //Fonction qui verifie si on est en haut de la page
  function isScrollAtTop() {
    return window.scrollY === 0;
  }

  // Fonction qui qui cache toutes les sections sauf la premiere
  function hideAllSections() {
    document.querySelectorAll(".section").forEach((section) => {
      section.classList.remove("active");
      $("#" + section.id).hide();
    });
  }

  //Fonction qui affiche la section d'un id donné
  function showSectionById(id) {
    hideAllSections();
    $("#" + id).fadeIn();
    document.getElementById(id).classList.add("active");
  }
});
