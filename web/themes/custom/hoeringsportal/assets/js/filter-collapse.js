/**
 * @file
 * Hook into collapse event.
 */

/* eslint-env jquery */
(function ($) {
  let width = $(window).width()
  let params = window.location.search
  let collapseFilterId = '#collapseFilters'
  $(document).ready(function () {
    if (width > 768 || params.length > 0) {
      $('#collapseFilters').collapse()
    }
  })

  $(collapseFilterId).on('show.bs.collapse', function () {
    $('.filters-toggle span').text('Skjul filtre')
  })
  $(collapseFilterId).on('hide.bs.collapse', function () {
    $('.filters-toggle span').text('Vis filtre')
  })
})(jQuery)
