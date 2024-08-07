module.exports = {
  "extends": "stylelint-config-recommended-scss",
  "rules": {
    "color-no-hex": [true, {
      "message": "Don't use hex colors like \"%s\""
    }],
    "scss/at-extend-no-missing-placeholder": null,

    "color-named": ["never", {
      "message": "Don't use named colors, instead use the predefined colors found in _bootstrap-custom.scss"
    }],
    "no-duplicate-selectors": true
  }
}
