module.exports = {
  purge: [],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      animation: {
        'spin-once': 'spin 0.5s linear 1',
        'spin-1': 'spin 1s linear infinite',
        'spin-5': 'spin 5s linear infinite',
        'spin-10': 'spin 10s linear infinite',
        'spin-15': 'spin 15s linear infinite',
        'spin-20': 'spin 20s linear infinite',
        'spin-30': 'spin 30s linear infinite',
        'spin-45': 'spin 45s linear infinite',
        'spin-60': 'spin 60s linear infinite',
        'spin-120': 'spin 120s linear infinite',
        'spin-300': 'spin 300s linear infinite',
        'spin-600': 'spin 600s linear infinite',
      }
    },
  },
  variants: {
    extend: {},
  },
  plugins: [require("tailwind-nord")],
};
