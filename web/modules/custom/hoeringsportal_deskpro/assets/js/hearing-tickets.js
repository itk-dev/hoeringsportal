import Vue from 'vue'
import makeI18n from './i18n'
import HearingTickets from './components/HearingTickets'

// https://codeburst.io/passing-configuration-to-vue-js-1b96fa8f959
const config = JSON.parse(document.getElementById('hearing-tickets-config').innerHTML);
const i18n = makeI18n(config.locale || 'da');

/* eslint-disable no-new */
new Vue({
  el: '#hearing-tickets-content',
  config,
  i18n,
  components: { HearingTickets },
  template: '<HearingTickets/>'
})
