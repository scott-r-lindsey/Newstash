var Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('app', './assets/js/app.js')

    //.autoProvidejQuery()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

Encore.enableLessLoader()

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

console.log(config.module);


module.exports = config;
