/* eslint-env jquery */
(function ($) {
  $('.slick-slider-gallery').slick({
    slidesToShow: 3,
    slidesToScroll: 2,
    infinite: false,
    prevArrow: '<button type="button" class="slick-prev pull-left"><i class="fas fa-angle-left"></i></button>',
    nextArrow: '<button type="button" class="slick-next pull-right"><i class="fas fa-angle-right"></i></button>',
    responsive: [
      {
        breakpoint: 1200,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          infinite: false
        }
      },
      {
        breakpoint: 576,
        settings: 'unslick'
      }
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ]
  })
})(jQuery)
