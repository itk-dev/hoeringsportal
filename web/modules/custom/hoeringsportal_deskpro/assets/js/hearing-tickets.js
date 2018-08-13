/* global jQuery drupalSettings */

(($) => {
  if (typeof drupalSettings.hearing_tickets !== 'undefined') {
    const configuration = drupalSettings.hearing_tickets
    $('#' + configuration.container_id).load(configuration.content_url)
  }
})(jQuery)
