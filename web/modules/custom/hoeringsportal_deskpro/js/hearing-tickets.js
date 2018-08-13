(function($) {
  if (typeof drupalSettings.hearing_tickets !== 'undefined') {
    var configuration = drupalSettings.hearing_tickets;
    $('#'+configuration.container_id).load(configuration.content_url);
  }
}(jQuery));
