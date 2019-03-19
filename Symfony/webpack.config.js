var Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableLessLoader()
    .enableSassLoader()

    .enableSingleRuntimeChunk()
    .enableBuildNotifications()

    // suggested in
    // https://medium.com/@lmatte7/how-to-use-react-with-symfony-4-88fb27abf5e5
    //.enablePostCssLoader()

    // regular site
    .addEntry('app', './assets/js/desktop/app.js')
    .addEntry('admin', './assets/js/desktop/admin.js')

    // mobile
    .enableReactPreset()
    .addEntry('mobileApp', './assets/js/mobile/clientSideEntryPoint.js')


/*

    .configureBabel(function(babelConfig) {
      // add additional presets
//      babelConfig.presets.push('es2015');
//      babelConfig.presets.push('stage-0');
      //babelConfig.presets.push('@babel/preset-stage-0')
      //babelConfig.presets.push('@babel/plugin-proposal-function-bind')
      babelConfig.presets.push('@babel/plugin-proposal-function-bind')
    })
*/

;


let config = Encore.getWebpackConfig();

/*
console.log(config);

config.module.rules.push({
  test: /\.modernizrrc$/,
  use: ['modernizr-loader', 'json-loader'],
});

*/

/*
config.module.rules.push({
  test: /inherit.js/,
  use: ['script-loader'],
});
*/

config.module.rules.push({
    test: () => !Encore.isProduction(),
    sideEffects: true,
});

config.resolve.alias = {
    'picker': 'pickadate/lib/picker'
};

//console.log(config.module);


module.exports = config;
