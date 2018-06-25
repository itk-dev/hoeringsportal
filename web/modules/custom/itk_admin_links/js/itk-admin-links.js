/**
 * @file
 * Toggle admin menu.
 */
(function ($) {
  "use strict";

  var toggle_button = $('.js-admin-toggle');
  var admin_menu = $('.js-admin-menu');
  var overlay = $('.js-admin-overlay');

  $(toggle_button).click(function() {
    admin_menu.toggleClass('is-open');
    toggle_button.toggleClass('is-open');

    // Toggle overlay.
    overlay.toggleClass('is-visible');
  });
})(jQuery);
