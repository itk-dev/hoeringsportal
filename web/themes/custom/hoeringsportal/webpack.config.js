/**
 * @file
 * Encore config.
 *
 * @type {Encore}
 */

var Encore = require('@symfony/webpack-encore');

Encore
  // The project directory where all compiled assets will be stored.
  .setOutputPath('build/')

  // The public path used by the web server to access the previous directory.
  .setPublicPath('/build')

  // Will create public/build/hoeringsportal.js and public/build/hoeringsportal.css and public/build/deskpro-custom-css.css.
  .addEntry('hoeringsportal', './assets/js/hoeringsportal.js')
  .addEntry('deskpro-custom-css', './assets/js/deskpro-custom-css.js')
  .addEntry('show-map', './assets/js/show-map.js')

  // Bootstrap expects jQuery to be available as a global variable.
  // (cf. http://symfony.com/doc/current/frontend/encore/bootstrap.html#importing-bootstrap-javascript).
  .autoProvidejQuery()

  // Enable source maps during development.
  .enableSourceMaps(!Encore.isProduction())

  // Empty the outputPath dir before each build.
  .cleanupOutputBeforeBuild()

  // Allow sass/scss files to be processed.
  .enableSassLoader()

  // Enable css-loader and autoprefixer.
  .enablePostCssLoader()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

// Export the final configuration.
module.exports = Encore.getWebpackConfig();
