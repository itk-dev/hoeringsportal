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

  .addEntry('form-ticket-add', './assets/js/form-ticket-add.js')

  // Enable source maps during development.
  .enableSourceMaps(!Encore.isProduction())

  // Empty the outputPath dir before each build.
  .cleanupOutputBeforeBuild()

  // Allow sass/scss files to be processed.
  .enableSassLoader()

  // Enable css-loader and autoprefixer.
  .enablePostCssLoader()

  .disableSingleRuntimeChunk()

// Export the final configuration.
module.exports = Encore.getWebpackConfig();
