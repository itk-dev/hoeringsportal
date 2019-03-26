var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where all compiled assets will be stored
    .setOutputPath('build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')

    // will create public/build/hoeringsportal.js and public/build/hoeringsportal.css and public/build/deskpro-custom-css.css
    .addEntry('hearing-edit', './assets/js/hearing-edit.js')

    // Bootstrap expects jQuery to be available as a global variable
    // (cf. http://symfony.com/doc/current/frontend/encore/bootstrap.html#importing-bootstrap-javascript)
    .autoProvidejQuery()

    // enable source maps during development
    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // show OS notifications when builds finish/fail
    // .enableBuildNotifications()

    // create hashed filenames (e.g. app.abc123.css)
    // .enableVersioning()

    // allow sass/scss files to be processed
    .enableSassLoader()
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
