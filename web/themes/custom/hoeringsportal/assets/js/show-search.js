/**
 * @file
 * Toggle ScrollToTop class.
 */

/* eslint-env jquery */
// Add class to body while scrolling.
(function ($) {
  $('.js-show-search').click(function () {
    $('#search-block-form').toggleClass('is-open')
    $('#search-block-form .form-search').focus()
  })
})(jQuery)
