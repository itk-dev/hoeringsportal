/**
 * @file
 * Add quicklinks.
 */

/* global jQuery, Drupal */
(function ($, Drupal) {
  Drupal.behaviors.quickLinks = {
    attach: function (context, settings) {
      'use strict'

      // Create anchors and anchor links.
      $.each($('.content__main h2, .group-footer h2'), function (index, value) {
        // Append the text of your header to a list item in a div, linking to an anchor we will create on the next line
        $('#quicklinks').append('<div><a href="#anchor-' + index + '">' + $(this).html() + '</a></div>')
        // Add an a tag to the header with a sequential name
        $(this).attr('id', '#anchor-' + index)
      })

      // Add smooth scrolling.
      $(document).on('click', '#quicklinks a', function (event) {
        event.preventDefault()
        $('html, body').animate({
          scrollTop: $(this).offset().top
        }, 100)
      })
    }
  }
})(jQuery, Drupal)
