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

  document.addEventListener('keydown', function (event) {
    if (document.activeElement.id === 'edit-keys') {
      if (event.key === 'Enter') {
        event.preventDefault()
      }
    }
  })
})(jQuery)
