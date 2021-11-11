let tailwindcss = require("tailwindcss");

module.exports = {
  plugins: [
    tailwindcss("./assets/tailwind.config.js"),
    require("autoprefixer"),
    require("postcss-import"),
  ],
};
