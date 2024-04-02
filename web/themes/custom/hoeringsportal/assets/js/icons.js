/*
// Add fontawesome icons
*/

// Import the svg core
import { library } from '@fortawesome/fontawesome-svg-core'

// To keep the package size as small as possible we only import icons we use

// Import the icons from the free solid package.
import {
  faSort,
  faEnvelope,
  faSearch,
  faTimes,
  faCopy,
  faPrint
} from '@fortawesome/free-solid-svg-icons'

// Import icons from the free brands package
import {
  faFacebook,
  faXTwitter,
  faLinkedin,
  faWhatsapp,
  faFacebookMessenger,
  faPinterest,
  faDigg,
  faTumblr,
  faReddit,
  faEvernote
} from '@fortawesome/free-brands-svg-icons'

// Add the icons to the library for replacing <i class="fa-solid fa-sort"></i> with the intended svg.
library.add(
  faSort,
  faEnvelope,
  faSearch,
  faTimes,
  faFacebook,
  faXTwitter,
  faLinkedin,
  faWhatsapp,
  faFacebookMessenger,
  faPinterest,
  faDigg,
  faTumblr,
  faReddit,
  faEvernote,
  faCopy,
  faPrint
)
