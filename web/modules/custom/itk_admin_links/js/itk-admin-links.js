/**
 * @file
 * Add quicklinks.
 */
(function ($, Drupal) {
  Drupal.behaviors.addQuicklinks= {
    attach: function (context, settings) {
      "use strict";

      function addAnchorsAndLinks(){
        //loop through all your headers
        $.each($('h2'),function(index,value){
          //append the text of your header to a list item in a div, linking to an anchor we will create on the next line
          $('#box-anchors').append('<li><a href="#anchor-'+index+'">'+$(this).html()+'</a></li>');
          //add an a tag to the header with a sequential name
          $(this).html('<a name="anchor-'+index+'">'+$(this).html()+'</a>');
        });
      }

      // Add anchors and create links.
      $(document).ready(function () {
        addAnchorsAndLinks();
      });
    }
  }
})(jQuery, Drupal);