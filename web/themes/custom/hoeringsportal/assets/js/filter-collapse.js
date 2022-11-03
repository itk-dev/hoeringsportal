/**
 * @file
 * Hook into collapse event.
 */

/* eslint-env jquery */
(function ($) {
  let collapseFilterId = '#collapseFilters'

  $(collapseFilterId).on('show.bs.collapse', function () {
    $('.filters-toggle span').text('Skjul filtre')
  })
  $(collapseFilterId).on('hide.bs.collapse', function () {
    $('.filters-toggle span').text('Vis filtre')
  })
})(jQuery)
