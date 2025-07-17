const Encore = require("@symfony/webpack-encore");

Encore
  // répertoire de sortie final (les fichiers compilés iront ici)
  .setOutputPath("public/build/")

  // URL publique utilisée dans le navigateur
  .setPublicPath("/build")

  // point d'entrée principal
  .addEntry("app", "./assets/app.js")

  // Stimulus (si utilisé)
  .enableStimulusBridge("./assets/controllers.json")

  // SCSS
  .enableSassLoader()

  // PostCSS (utile si tu veux Tailwind, autoprefixer, etc.)
  .enablePostCssLoader()

  // un seul fichier runtime au lieu de plusieurs petits
  .enableSingleRuntimeChunk()

  // nettoyage avant compilation
  .cleanupOutputBeforeBuild()

  // source maps pour le debug
  .enableSourceMaps(!Encore.isProduction())

  // hash des fichiers en prod (pour le cache busting)
  .enableVersioning(Encore.isProduction())

  // configuration Babel
  .configureBabel((config) => {});

module.exports = Encore.getWebpackConfig();
