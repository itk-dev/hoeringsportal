/**
 * @file
 * Encore config.
 *
 * @type {Encore}
 */

var Encore = require('@symfony/webpack-encore');

Encore
  // Directory where compiled assets will be stored.
  .setOutputPath('build/')
  // Public path used by the web server to access the output path.
  .setPublicPath('/build')

  .addEntry('form-ticket-add', './assets/js/form-ticket-add.js')

  // Will require an extra script tag for runtime.js but, you probably want this, unless you're building a single-page app.
  .disableSingleRuntimeChunk()

  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())

  // Uncomment if you use Sass/SCSS files.
  .enableSassLoader();

module.exports = Encore.getWebpackConfig();
