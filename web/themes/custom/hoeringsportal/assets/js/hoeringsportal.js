require('../css/hoeringsportal.scss')

const $ = require('jquery')

require('popper.js')
require('bootstrap')
require('./hide_password_help.js')
require('./responsive-image-as-background.js')
require('./scroll-top.js')
require('./hide_spinner.js')

// Enable popovers.
$(function () {
  $('[data-toggle="popover"]').popover()
})
