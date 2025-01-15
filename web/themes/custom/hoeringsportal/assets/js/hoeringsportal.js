/**
 * @file
 * Gather theme related assets.
 */

require("../css/hoeringsportal.scss");

const $ = require("jquery");

require("@popperjs/core");
require("bootstrap");
require("./hide_password_help.js");
require("./responsive-image-as-background.js");
require("./scroll-top.js");
require("./hide_spinner.js");
require("./show-search.js");
require("slick-carousel");
require("./slick-slider-config.js");
require("./filter-collapse.js");
require("./icons.js");
require("./show-map.js");

// Enable popovers.
$(function () {
  $('[data-toggle="popover"]').popover();

  // See of we can find a Sign up shortcut element.
  const signUpShortcut = $("#sign-up-shortcut").first();
  if (signUpShortcut.length > 0) {
    // Set target url from first sign up button in the shortcut.
    const firstMeeting = $(".public-meeting-sign-up").first();
    if (firstMeeting.length > 0) {
      signUpShortcut.attr("href", firstMeeting.attr("href")).toggle(true);
    }
  }
});
