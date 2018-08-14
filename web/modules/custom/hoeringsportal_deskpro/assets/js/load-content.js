/* global jQuery drupalSettings */

(($) => {
  if (typeof drupalSettings.deskpro_hoeringsportal !== 'undefined') {
    const configuration = drupalSettings.deskpro_hoeringsportal;
    if (configuration.container_id && configuration.content_url) {
      $('#' + configuration.container_id).load(configuration.content_url)
    }
  }
})(jQuery)
