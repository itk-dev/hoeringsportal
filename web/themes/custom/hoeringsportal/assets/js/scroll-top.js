/**
 * @file
 * Toggle ScrollToTop class.
 */

/* eslint-env jquery */
// Add class to body while scrolling.
(function ($) {
  $(window).scroll(function () {
    if ($(this).scrollTop() >= 150) {
      $('body').addClass('scrolled')
    } else {
      $('body').removeClass('scrolled')
    }
  })
})(jQuery)
