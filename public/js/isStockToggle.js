document.addEventListener("DOMContentLoaded", () => {
  const isStockCheckbox = document.querySelector('input[name$="[isStock]"]');
  const localisationField = document.querySelector(
    '[data-ea-field-name="localisation"] select'
  );
  const utilisateurField = document.querySelector(
    '[data-ea-field-name="utilisateur"] select'
  );

  if (!isStockCheckbox || !localisationField || !utilisateurField) return;

  const toggleFields = () => {
    const checked = isStockCheckbox.checked;

    // Désactiver les selects natifs
    localisationField.disabled = checked;
    utilisateurField.disabled = checked;

    // Désactiver les Select2 si initialisés
    if ($(localisationField).hasClass("select2-hidden-accessible")) {
      $(localisationField).select2(checked ? "disable" : "enable");
    }

    if ($(utilisateurField).hasClass("select2-hidden-accessible")) {
      $(utilisateurField).select2(checked ? "disable" : "enable");
    }
  };

  isStockCheckbox.addEventListener("change", toggleFields);
  toggleFields();
});
