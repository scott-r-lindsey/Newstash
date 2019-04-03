var Encore = require('@symfony/webpack-encore');

Encore
  .setOutputPath("var/webpack/")
  .setPublicPath("/")
  .cleanupOutputBeforeBuild()
  .enableReactPreset()
  .disableSingleRuntimeChunk()
  .addEntry("server-bundle", "./assets/js/mobile/entryPoint.js")
  .enableSassLoader()

  .configureBabel(function(babelConfig) {
    babelConfig.plugins.push("@babel/plugin-proposal-class-properties");
  })

;

let config = Encore.getWebpackConfig();

module.exports = config;
