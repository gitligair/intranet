document.addEventListener("DOMContentLoaded", function () {
  const isStock = document.querySelector("#Ecran_isStock");

  // Identifie les containers des champs localisation et utilisateur
  const lesAssociations = document.querySelector(".enStock");

  function toggleAssociationFields() {
    if (isStock.checked) {
      // Affiche les champs si isStock est coché
      lesAssociations.style.display = "";
    } else {
      // Cache les champs sinon
      lesAssociations.style.display = "none";
    }
  }

  if (isStock && lesAssociations) {
    toggleAssociationFields(); // Initial
    isStock.addEventListener("change", toggleAssociationFields); // Dynamique
  }
});
