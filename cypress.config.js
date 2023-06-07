const { defineConfig } = require('cypress')

module.exports = defineConfig({
  e2e: {
    env: {
      mailHogUrl: "http://mailhog:8025",
    }
  }
})
