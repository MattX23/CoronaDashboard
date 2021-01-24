<template>
    <div class="container">
        <div class="row">
            <div v-if="monthlyView && current.totalDeaths && previous.totalDeaths" class="col-lg-12 col-md-6 col-sm-12">
                <p>
                    <span class="stat-info"><span class="bullet">></span>Deaths</span> {{getSymbol(getDifference(current.totalDeaths, previous.totalDeaths))}} {{formatNumber(getDifference(current.totalDeaths, previous.totalDeaths))}}
                </p>
            </div>
            <div v-if="!monthlyView && current.newDeaths && previous.newDeaths" class="col-lg-12 col-md-6 col-sm-12">
                <p>
                    <span class="stat-info"><span class="bullet">></span>Deaths</span> {{getSymbol(getDifference(current.newDeaths, previous.newDeaths))}} {{formatNumber(getDifference(current.newDeaths, previous.newDeaths))}}
                </p>
            </div>
            <div v-if="monthlyView && current.activeCases && previous.activeCases" class="col-lg-12 col-md-6 col-sm-12">
                <p>
                    <span class="stat-info"><span class="bullet">></span>Active cases</span> {{getSymbol(getDifference(current.activeCases, previous.activeCases))}} {{formatNumber(getDifference(current.activeCases, previous.activeCases))}}
                </p>
            </div>
            <div v-if="!monthlyView && current.newCases && previous.newCases" class="col-lg-12 col-md-6 col-sm-12">
                <p>
                    <span class="stat-info"><span class="bullet">></span>New cases</span> {{getSymbol(getDifference(current.newCases, previous.newCases))}} {{formatNumber(getDifference(current.newCases, previous.newCases))}}
                </p>
            </div>
            <div v-if="monthlyView && current.critical && previous.critical" class="col-lg-12 col-md-6 col-sm-12">
                <p>
                    <span class="stat-info"><span class="bullet">></span>Critical cases</span> {{getSymbol(getDifference(current.critical, previous.critical))}} {{formatNumber(getDifference(current.critical, previous.critical))}}
                </p>
            </div>
            <div v-if="monthlyView && current.recovered && previous.recovered" class="col-lg-12 col-md-6 col-sm-12">
                <p>
                    <span class="stat-info"><span class="bullet">></span>Recoveries</span> {{getSymbol(getDifference(current.recovered, previous.recovered))}} {{formatNumber(getDifference(current.recovered, previous.recovered))}}
                </p>
            </div>
            <div v-if="monthlyView && current.totalCases && previous.totalCases" class="col-lg-12 col-md-6 col-sm-12">
                <p>
                    <span class="stat-info"><span class="bullet">></span>Total infections</span> {{getSymbol(getDifference(current.totalCases, previous.totalCases))}} {{formatNumber(getDifference(current.totalCases, previous.totalCases))}}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import {
    formatNumber,
    getDifference,
    getPercentage,
    getSymbol,
} from "../helpers/mathematics";

export default {
    name: "StatsBox",
    props: {
        current: {
            type: Object,
        },
        monthlyView: {
            type: Boolean,
        },
        previous: {
            type: Object,
        }
    },
    methods: {
        formatNumber,
        getPercentage,
        getSymbol,
        getDifference,
    }
}
</script>

<style scoped>
.stat-info {
    display: inline-block;
    width: 130px;
    margin-top: 10px;
    margin-left: 20px;
}
.bullet {
    margin-right: 10px;
    font-size: .9rem;
    font-weight: bold;
    position: relative;
    bottom: 1px;
}
</style>