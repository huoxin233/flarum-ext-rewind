const config = require('flarum-webpack-config')();

// Disable automatic chunk splitting to prevent anonymous shared chunks
// that Flarum's asset loader can't resolve
config.optimization = {
  ...config.optimization,
  splitChunks: false,
};

// Exclude node_modules from babel-loader so dependencies (e.g. animejs) aren't broken by ES5 class transform
if (config.module && config.module.rules) {
  config.module.rules.forEach((rule) => {
    rule.exclude = /node_modules/;
  });
}

module.exports = config;
