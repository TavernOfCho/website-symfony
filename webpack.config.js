const Encore = require('@symfony/webpack-encore');
const TerserPlugin = require('terser-webpack-plugin');

Encore
    .enableSingleRuntimeChunk()
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    // .enableVersioning(Encore.isProduction())

    // uncomment to define the assets of the project
    .addEntry('app', './assets/js/app.js')
    .addStyleEntry('error', './assets/css/error.scss')

    // uncomment if you use Sass/SCSS files
    .enableSassLoader()

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()

    .copyFiles({
        from: './assets/img/classes',
        to: 'images/classes/[path][name].[ext]'
    })

    .addPlugin(new TerserPlugin({
        extractComments: true,
        cache: true,
        parallel: true
    }));


module.exports = Encore.getWebpackConfig();