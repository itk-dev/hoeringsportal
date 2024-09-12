/*
// Add fontawesome icons
*/

// Import the svg core
import { library, dom } from '@fortawesome/fontawesome-svg-core'

// To keep the package size as small as possible we only import icons we use

// Import the icons from the free solid package.
import {
  faArrowRight,
  faCopy,
  faComment,
  faCircleCheck,
  faFilePdf,
  faEnvelope,
  faMagnifyingGlass,
  faPrint,
  faSort,
  faTimes,
  faCircle,
  faFileWord,
  faFileAlt,
  faFileImage,
  faFileExcel,
  faTicketAlt,
  faTriangleExclamation,
} from '@fortawesome/free-solid-svg-icons'

// Import icons from the free brands package
import {
  faDigg,
  faEvernote,
  faFacebook,
  faFacebookMessenger,
  faLinkedin,
  faPinterest,
  faReddit,
  faTumblr,
  faWhatsapp,
  faXTwitter
} from '@fortawesome/free-brands-svg-icons'

// Add the icons to the library for replacing <i class="fa-solid fa-sort"></i> with the intended svg.
library.add(
  // Solid
  faArrowRight,
  faCircle,
  faCircleCheck,
  faComment,
  faCopy,
  faEnvelope,
  faFilePdf,
  faFileWord,
  faFileAlt,
  faFileImage,
  faFileExcel,
  faMagnifyingGlass,
  faPrint,
  faSort,
  faTimes,
  faTicketAlt,
  // Brand
  faDigg,
  faEvernote,
  faFacebook,
  faFacebookMessenger,
  faLinkedin,
  faPinterest,
  faReddit,
  faTumblr,
  faWhatsapp,
  faXTwitter,
  faTriangleExclamation,
)
dom.i2svg()
