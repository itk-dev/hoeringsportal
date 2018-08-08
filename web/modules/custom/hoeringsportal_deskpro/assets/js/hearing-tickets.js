import Vue from 'vue'
import makeI18n from './i18n'
import HearingTickets from './components/HearingTickets'

const container = document.getElementById('hearing-tickets')
if (container !== null) {
  // https://codeburst.io/passing-configuration-to-vue-js-1b96fa8f959
  const config = JSON.parse(container.getAttribute('data-configuration'))
  const i18n = makeI18n(config.locale || 'da')

  /* eslint-disable no-new */
  new Vue({
    el: container,
    config,
    i18n,
    components: { HearingTickets },
    template: '<HearingTickets/>'
  })
}
