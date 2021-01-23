<template>
    <div v-show="!isLoading && current.population" class="text-center margin-bottom">
        <span
            v-if="current.population && current.activeCases"
            @click="setMainStats('currentlyInfected')"
            data-currentlyInfected="true"
            class="btn btn-dark btn-sm main-stats"
        >
            Currently Infected: {{ getPercentage(current.population, current.activeCases) }}%
        </span>
        <span
            v-if="current.population && current.totalCases"
            @click="setMainStats('totalInfected')"
            data-totalInfected="true"
            class="btn btn-dark btn-sm main-stats"
        >
            Total Infected: {{ getPercentage(current.population, current.totalCases) }}%
        </span>
        <span
            v-if="current.totalCases && current.recovered"
            @click="setMainStats('recovered')"
            data-recovered="true"
            class="btn btn-dark btn-sm main-stats"
        >
            Recovered: {{ getPercentage(current.totalCases, current.recovered) }}%
        </span>
        <span
            v-if="current.totalCases && current.totalDeaths"
            @click="setMainStats('deaths')"
            data-deaths="true"
            class="btn btn-dark btn-sm main-stats"
        >
            Deaths: {{ getPercentage(current.totalCases, current.totalDeaths) }}%
        </span>
    </div>
</template>

<script>
    import { formatNumber, getPercentage } from "../helpers/mathematics";
    import { setMainStats, setStatsText, toggleClass } from "../helpers/stats";

    export default {
        name: "StatsButtons",
        props: {
            current: {
                type: Object
            },
            countryName: {
                type: String
            },
            isLoading: {
                type: Boolean
            }
        },
        methods: {
            formatNumber,
            getPercentage,
            setMainStats,
            setStatsText,
            toggleClass,
        }
    }
</script>

<style scoped>
.main-stats {
    margin-top: 5px;
}
</style>