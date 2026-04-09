const config = require('flarum-webpack-config')();

// Disable automatic chunk splitting to prevent anonymous shared chunks
// that Flarum's asset loader can't resolve
config.optimization = {
  ...config.optimization,
  splitChunks: false,
};

module.exports = config;
