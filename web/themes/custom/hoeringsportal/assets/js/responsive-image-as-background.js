/**
 * Change html responsive images to be used as css background images instead.
 */

class ResponsiveBackgroundImage {
  constructor (element) {
    // Set our variables.
    this.element = element
    this.img = element.querySelector('img')
    this.src = ''

    this.img.addEventListener('load', () => {
      this.update()
    })

    if (this.img.complete) {
      this.update()
    }
  }

  // Update the background image used.
  update () {
    let src = typeof this.img.currentSrc !== 'undefined' ? this.img.currentSrc : this.img.src
    if (this.src !== src) {
      this.src = src
      this.element.style.backgroundImage = 'url("' + this.src + '")'
    }
  }
}

// Look for data attribute.
let elements = document.querySelectorAll('[data-responsive-background-image]')
for (let i = 0; i < elements.length; i++) {
  new ResponsiveBackgroundImage(elements[i]) // eslint-disable-line no-new
}
