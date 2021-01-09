<template>
  <table class="table table-striped table-condensed wine-table">
    <thead>
    <tr>
      <th class="text-center">DateiNr</th>
      <th v-if="showCatalogueNumber" class="text-center">KatNr</th>
      <th>Betrieb</th>
      <th>Verein</th>
      <th>Marke</th>
      <th>Sorte</th>
      <th>Jahr</th>
      <th class="text-center">Qualit&auml;t</th>
      <th class="text-center">Alk.</th>
      <th class="text-center">Säure</th>
      <th class="text-center">Zucker</th>
      <th v-if="showRating1" class="text-center">1. Bewertung</th>
      <th v-if="showRating2" class="text-center">2. Bewertung</th>
      <th v-if="showKdB" class="text-center">KdB</th>
      <th v-if="showExcluded" class="text-center">Ex</th>
      <th v-if="showSosi" class="text-center">SoSi</th>
      <th v-if="showChosen" class="text-center">Ausschank</th>
      <th v-if="canEdit">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
      <tr v-for="wine in wines" :key="wine.id">
        <td class="text-center">
          <a v-if="wine.nr" :href="'/wines/' + wine.id">{{wine.nr}}</a>
          <span v-else>-</span>
        </td>
        <td v-if="showCatalogueNumber" class="text-center">
          {{ wine.catalogue_number }}
        </td>
        <td><a :href="'/settings/applicants/' + wine.applicant.id">{{wine.applicant.label}} {{wine.applicant.lastname}}</a></td>
        <td><a :href="'/settings/association/' + wine.applicant.association.id">{{wine.applicant.association.name}}</a></td>
        <td>{{ wine.label }}</td>
        <td>{{ wine.winesort.name }}</td>
        <td>{{ wine.vintage }}</td>
        <td class="text-center">
          {{ wine.winequality ? wine.winequality.abbr : '-' }}
        </td>
        <td class="text-center">{{ wine.alcohol.toLocaleString() }}</td>
        <td class="text-center">{{ wine.acidity.toLocaleString() }}</td>
        <td class="text-center">{{ wine.sugar.toLocaleString() }}</td>
        <td v-if="showRating1" class="text-center">{{ wine.rating1 ? wine.rating1.toLocaleString() : '-' }}</td>
        <td v-if="showRating2" class="text-center">{{ wine.rating2 ? wine.rating2.toLocaleString() : '-' }}</td>
        <td v-if="showKdB" class="text-center">
          <span v-if="wine.kdb" class="glyphicon glyphicon-ok"></span>
          <span v-else>-</span>
        </td>
        <td v-if="showExcluded" class="text-center">
          <span v-if="wine.excluded" class="glyphicon glyphicon-ok"></span>
          <span v-else>-</span>
        </td>
        <td v-if="showSosi" class="text-center">
          <span v-if="wine.sosi" class="glyphicon glyphicon-ok"></span>
          <span v-else>-</span>
        </td>
        <td v-if="showChosen" class="text-center">
          <span v-if="wine.chosen" class="glyphicon glyphicon-ok"></span>
          <span v-else>-</span>
        </td>
        <td v-if="canEdit">
          |
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script>
import axios from 'axios'

export default {
  name: 'WineTable',
  props: {
    fetchUrl: {
      type: String,
      required: true,
    },
    showCatalogueNumber: {
      type: Boolean,
      default: false,
    },
    showRating1: {
      type: Boolean,
      default: false,
    },
    showRating2: {
      type: Boolean,
      default: false,
    },
    showKdB: {
      type: Boolean,
      default: false,
    },
    showExcluded: {
      type: Boolean,
      default: false,
    },
    showSosi: {
      type: Boolean,
      default: false,
    },
    showChosen: {
      type: Boolean,
      default: false,
    },
    canEdit: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      wines: undefined,
    }
  },
  async mounted() {
    await this.load()
  },
  methods: {
    async load() {
      console.debug('load')
      const resp = await axios.get(this.fetchUrl)
      console.debug('loaded', resp.data)

      this.wines = this.$set(this, 'wines', resp.data)
    }
  }
}
</script>

<style scoped>

</style>
