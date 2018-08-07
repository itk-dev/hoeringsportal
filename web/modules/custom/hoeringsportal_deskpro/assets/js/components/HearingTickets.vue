<template>
  <div class="hearing-tickets">
    <div class="row">
      <div class="col">
        <h3>Høringssvar <span v-if="tickets">({{ tickets.length }})</span></h3>
      </div>
      <div class="col" v-if="ticket_add_url">
        <div class="float-right">
          <a :href="ticket_add_url" class="btn btn-primary">
            Skriv et nyt høringssvar →
          </a>
        </div>
      </div>
    </div>

    <div class="loading" v-if="loading">{{ $t('Loading tickets …') }}</div>

    <div v-if="tickets" class="list-group-item ticket" v-for="(ticket, index) in tickets" v-bind:key="ticket.id">
      <div class="subject">{{ ticket.subject }}</div>
      <div>
        {{ $t('Answer #{number} by {name}', {number: index+1, name: ticket.person.display_name}) }}
        | <span class="ticket-date_created">{{ $d(new Date(ticket.date_created), 'short') }}</span>
      </div>
      <div class="ticket-read-more">
        <a class="read-more" :href="ticket['@details_url']">Details</a>
      </div>
    </div>
  </div>
</template>

<script>
// https://codeburst.io/dependency-injection-with-vue-js-f6b44a0dae6d
import Vue from 'vue'

Vue.mixin({
  beforeCreate () {
    const options = this.$options
    if (options.config) {
      this.$config = options.config
    } else if (options.parent && options.parent.$config) {
      this.$config = options.parent.$config
    }
  }
})

export default {
  name: 'hearing-tickets',
  data () {
    return {
      loading: false,
      tickets: null,
      ticket_add_url: this.$config.ticket_add_url,
      error: null
    }
  },
  created () {
    this.fetchData()
  },
  methods: {
    fetchData () {
      this.error = this.tickets = null
      this.loading = true

      const self = this
      fetch(this.$config.data_url)
        .then(response => {
          self.loading = false
          if (!response.ok) {
            throw (response.statusText)
          }
          return response.json()
        })
        .then(data => { self.setData(null, data) })
        .catch(error => { self.setData(error, null) })
    },
    setData (error, data) {
      if (error) {
        this.error = error.toString()
      } else {
        data.data.forEach(ticket => {
          ticket['@details_url'] = this.$config['ticket_view_url'].replace('{ticket}', ticket.id)
        })
        this.data = data
        this.tickets = this.data.data
      }
    }
  }
}
</script>
