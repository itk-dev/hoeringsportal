/**
 * @file
 * Encore config.
 *
 * @type {Encore}
 */

var Encore = require('@symfony/webpack-encore')

Encore
  // The project directory where all compiled assets will be stored.
  .setOutputPath('build/')

  // The public path used by the web server to access the previous directory.
  .setPublicPath('/build')

  .addEntry('hearing-edit', './assets/js/hearing-edit.js')

  // Bootstrap expects jQuery to be available as a global variable.
  // (cf. http://symfony.com/doc/current/frontend/encore/bootstrap.html#importing-bootstrap-javascript).
  .autoProvidejQuery()

  // Enable source maps during development.
  .enableSourceMaps(!Encore.isProduction())

  // Empty the outputPath dir before each build.
  .cleanupOutputBeforeBuild()

  // Allow sass/scss files to be processed.
  .enableSassLoader()

// Export the final configuration.
module.exports = Encore.getWebpackConfig()
