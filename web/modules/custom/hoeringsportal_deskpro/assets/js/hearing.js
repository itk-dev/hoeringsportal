import Vue from 'vue'
import makeI18n from './i18n'
import Hearing from './components/Hearing'

// https://codeburst.io/passing-configuration-to-vue-js-1b96fa8f959
const config = JSON.parse(document.getElementById('hearing-config').innerHTML);
const i18n = makeI18n(config.locale);

/* eslint-disable no-new */
new Vue({
  el: '#hearing-content',
  config,
  i18n,
  components: { Hearing },
  template: '<Hearing/>'
})
