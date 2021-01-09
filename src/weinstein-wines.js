import Vue from 'vue'

import WineTable from './components/competition/WineTable.vue'

const View = Vue.extend(WineTable)

window.renderWineTable = (url, competitionId) => {
    new View({
        propsData: {
            fetchUrl: url + '?competition_id=' + competitionId,
            wines: [],
        },
    }).$mount('#wines-table')
}

document.addEventListener("DOMContentLoaded", () => {

})
