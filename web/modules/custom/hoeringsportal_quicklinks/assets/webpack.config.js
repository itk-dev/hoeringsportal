/**
 * @file
 * Encore config.
 *
 * @type {Encore}
 */

var Encore = require('@symfony/webpack-encore');

Encore
  // The project directory where all compiled assets will be stored.
  .setOutputPath('public/build/')

  // The public path used by the web server to access the previous directory.
  .setPublicPath('/build')

  // Will create public/build/app.js and public/build/app.css.
  .addEntry('project-quicklinks', './js/project-quicklinks.js')

  // Enable source maps during development.
  .enableSourceMaps(!Encore.isProduction())

  // Empty the outputPath dir before each build.
  .cleanupOutputBeforeBuild();

// Export the final configuration.
module.exports = Encore.getWebpackConfig();
