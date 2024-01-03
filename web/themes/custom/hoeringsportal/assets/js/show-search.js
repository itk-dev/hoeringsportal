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

  let searchInput = document.querySelector('#edit-keys');
  document.addEventListener("keydown", function (event) {
    if ('edit-keys' === document.activeElement.id) {
      if (event.key === 'Enter') {
        event.preventDefault();
      }
    }
  });
})(jQuery)
