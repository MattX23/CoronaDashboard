<template>
    <div class="container">
        <div class="row">
            <div class="col-12 margin-bottom">
                <select class="form-control country-select">
                    <option disabled selected>-- Select a Country --</option>
                </select>
            </div>
            <div class="col-12 text-center margin-bottom">
                <img v-if="$props.code" id="country-flag" :src="flagPath" width="64" height="64" :alt="$props.country">
                <div class="text-center country-name margin-bottom">{{ $props.country }}</div>
                <div class="text-center margin-bottom">
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
                    Recovered: {{ getPercentage(current.population, current.recovered) }}%
                    </span>
                    <span
                        v-if="current.totalCases && current.totalDeaths"
                        @click="setMainStats('deaths')"
                        data-deaths="true"
                        class="btn btn-dark btn-sm main-stats"
                    >
                    Deaths: {{ getPercentage(current.population, current.totalDeaths) }}%
                    </span>
                </div>
                <div v-show="!isLoading" class="text-center margin-bottom">
                    <div id="stat-text" class="alert alert-light">

                    </div>
                </div>
                <div class="text-center margin-bottom">
                    <button @click="toggleTotals($event)" data-totals="true" class="btn btn-sm btn-primary btn-toggle">Totals</button>
                    <button @click="toggleTotals($event)" data-updates="true" class="btn btn-sm btn-dark btn-toggle">Daily Change</button>
                </div>
                <div v-if="dataUnavailable" class="alert alert-dark text-center">Sorry, this data isn't available yet</div>
                <loader v-if="isLoading"></loader>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 margin-bottom">
                <bar-chart
                    v-show="!dataUnavailable"
                    :chart-data="chartData"
                    class="chart-container"
                ></bar-chart>
            </div>
            <div v-show="!isLoading && !dataUnavailable" class="col-lg-4 outline stats-container box-shadow">
                <p class="text-center stats-header">
                    <b v-if="monthlyView">Changes over the last month</b>
                    <b v-else>Changes since yesterday</b>
                </p>
                <div class="container">
                    <div class="row">
                        <div v-if="monthlyView" class="col-lg-12 col-md-6 col-sm-12">
                            <p v-if="current.totalDeaths && previous.totalDeaths">
                                <span class="stat-info"><span class="bullet">></span>Deaths</span> {{getSymbol(getDifference(current.totalDeaths, previous.totalDeaths))}} {{formatNumber(getDifference(current.totalDeaths, previous.totalDeaths))}}
                            </p>
                        </div>
                        <div v-else class="col-lg-12 col-md-6 col-sm-12">
                            <p v-if="current.newDeaths && previous.newDeaths">
                                <span class="stat-info"><span class="bullet">></span>Deaths</span> {{getSymbol(getDifference(current.newDeaths, previous.newDeaths))}} {{formatNumber(getDifference(current.newDeaths, previous.newDeaths))}}
                            </p>
                        </div>
                        <div v-if="monthlyView" class="col-lg-12 col-md-6 col-sm-12">
                            <p v-if="current.activeCases && previous.activeCases">
                                <span class="stat-info"><span class="bullet">></span>Active cases</span> {{getSymbol(getDifference(current.activeCases, previous.activeCases))}} {{formatNumber(getDifference(current.activeCases, previous.activeCases))}}
                            </p>
                        </div>
                        <div v-else class="col-lg-12 col-md-6 col-sm-12">
                            <p v-if="current.newCases && previous.newCases">
                                <span class="stat-info"><span class="bullet">></span>New cases</span> {{getSymbol(getDifference(current.newCases, previous.newCases))}} {{formatNumber(getDifference(current.newCases, previous.newCases))}}
                            </p>
                        </div>
                        <div v-if="monthlyView" class="col-lg-12 col-md-6 col-sm-12">
                            <p v-if="current.critical && previous.critical">
                                <span class="stat-info"><span class="bullet">></span>Critical cases</span> {{getSymbol(getDifference(current.critical, previous.critical))}} {{formatNumber(getDifference(current.critical, previous.critical))}}
                            </p>
                        </div>
                        <div v-if="monthlyView" class="col-lg-12 col-md-6 col-sm-12">
                           <p v-if="current.recovered && previous.recovered">
                               <span class="stat-info"><span class="bullet">></span>Recoveries</span> {{getSymbol(getDifference(current.recovered, previous.recovered))}} {{formatNumber(getDifference(current.recovered, previous.recovered))}}
                           </p>
                        </div>
                        <div v-if="monthlyView" class="col-lg-12 col-md-6 col-sm-12">
                            <p v-if="current.totalCases && previous.totalCases">
                                <span class="stat-info"><span class="bullet">></span>Total infections</span> {{getSymbol(getDifference(current.totalCases, previous.totalCases))}} {{formatNumber(getDifference(current.totalCases, previous.totalCases))}}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    const INITIAL_STATS = '/api/statistics/';
    const ACTIVE_BTN_CLASS = 'btn-primary';
    const SUCCESS_BTN_CLASS = 'btn-success';
    const SECONDARY_BTN_CLASS = 'btn-dark';
    const STATS_BTNS = [
        'recovered',
        'deaths',
        'currentlyInfected',
        'totalInfected',
    ];

    export default {
        props: {
            code: {
                type: String
            },
            country: {
                type: String
            },
            searchName: {
                type: String
            }
        },
        mounted() {
            this.fetchStatistics();
        },
        data() {
            return {
                isLoading: false,
                dataUnavailable: false,
                monthlyView: true,
                current: {
                    activeCases: null,
                    totalDeaths: null,
                    recovered: null,
                    totalCases: null,
                    critical: null,
                    newDeaths: null,
                    newCases: null,
                    date: null,
                    population: null,
                },
                previous: {
                    activeCases: null,
                    totalDeaths: null,
                    recovered: null,
                    totalCases: null,
                    critical: null,
                    newDeaths: null,
                    newCases: null,
                    date: null,
                },
                flagPath: this.code ? `https://www.countryflags.io/${this.$props.code}/shiny/64.png` : '',
                chartData: {},
            }
        },
        methods: {
            fetchStatistics() {
                this.isLoading = true;
                axios.get(`${INITIAL_STATS}${this.$props.searchName}`)
                    .then(response => {
                        this.current.activeCases = response.data.current.activeCases;
                        this.current.totalDeaths = response.data.current.totalDeaths;
                        this.current.recovered = response.data.current.recovered;
                        this.current.totalCases = response.data.current.totalCases;
                        this.current.critical = response.data.current.critical;
                        this.current.newDeaths = response.data.current.newDeaths;
                        this.current.newCases = response.data.current.newCases;
                        this.current.date = response.data.current.date;
                        this.current.population = response.data.current.population;

                        this.previous.activeCases = response.data.previous.activeCases;
                        this.previous.totalDeaths = response.data.previous.totalDeaths;
                        this.previous.recovered = response.data.previous.recovered;
                        this.previous.totalCases = response.data.previous.totalCases;
                        this.previous.critical = response.data.previous.critical;
                        this.previous.newDeaths = response.data.previous.newDeaths;
                        this.previous.newCases = response.data.previous.newCases;
                        this.previous.date = response.data.previous.date;
                    })
                    .then(() => this.chartData = this.constructChartData(true))
                    .then(() => this.setMainStats())
                    .finally(() => this.isLoading = false);
            },
            constructChartData(showTotals) {
                this.dataUnavailable = false;
                this.monthlyView = showTotals;

                const currentStats = showTotals ?
                    {
                        'Deaths': this.current.totalDeaths,
                        'Active Cases': this.current.activeCases,
                        'Critical': this.current.critical,
                        'Recovered': this.current.recovered,
                        'Total Cases': this.current.totalCases,
                    } :
                    {
                        'Deaths': this.current.newDeaths,
                        'New Cases': this.current.newCases,
                    };

                const lastMonthStats = showTotals ?
                    {
                        'Deaths': this.previous.totalDeaths,
                        'Active Cases': this.previous.activeCases,
                        'Critical': this.previous.critical,
                        'Recovered': this.previous.recovered,
                        'Total Cases': this.previous.totalCases,
                    } :
                    {
                        'Deaths': this.previous.newDeaths,
                        'New Cases': this.previous.newCases,
                    };

                const labels = [];
                const currentData = [];
                const lastMonthData = [];

                Object.entries(currentStats).forEach(([key, value]) => {
                    if (value && lastMonthStats[key]) {
                        labels.push(key);
                        currentData.push(value);
                    }
                });

                if (currentData.length < 1) this.dataUnavailable = true;

                Object.entries(lastMonthStats).forEach(([key, value]) => {
                    if (currentStats[key] && value) {
                        lastMonthData.push(value);
                    }
                });

                return {
                    labels: labels,
                    datasets: [
                        {
                            label: showTotals ? `Totals on ${this.previous.date}` : `Daily figures on ${this.previous.date}`,
                            backgroundColor: showTotals ? '#fff' : '#f87979',
                            data: lastMonthData
                        },
                        {
                            label: showTotals ? 'Totals as of today' : 'Daily Figures as of today',
                            backgroundColor: showTotals ? '#f87979' : '#fff',
                            data: currentData
                        }
                    ]
                }
            },
            toggleTotals(e) {
                const clickedEl = e.target;

                if (clickedEl.classList.contains(ACTIVE_BTN_CLASS)) return;

                const showTotals = clickedEl.dataset.totals !== undefined;
                const secondaryBtn = showTotals ? document.querySelector('[data-updates]') : document.querySelector('[data-totals]');
                this.toggleBtnClass(secondaryBtn, false);
                this.toggleBtnClass(clickedEl, true);

                this.chartData = this.constructChartData(showTotals);
            },
            toggleBtnClass(el, shouldBeActive) {
                shouldBeActive ?
                    this.toggleClass(el, SECONDARY_BTN_CLASS, ACTIVE_BTN_CLASS) :
                    this.toggleClass(el, ACTIVE_BTN_CLASS, SECONDARY_BTN_CLASS);
            },
            formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",").replace('-', '- ');
            },
            setMainStats(selected = null) {
                document.querySelectorAll('.main-stats')
                    .forEach(el => {
                        el.classList.remove(SUCCESS_BTN_CLASS);
                        if (!el.classList.contains(SECONDARY_BTN_CLASS)) el.classList.add(SECONDARY_BTN_CLASS);
                    });

                if (selected) {
                    this.toggleClass(document.querySelector(`[data-${selected}]`), SECONDARY_BTN_CLASS, SUCCESS_BTN_CLASS);
                    this.setStatsText(selected)
                } else {
                    let set = false;

                    [...STATS_BTNS].forEach(btn => {
                       if (this.getPercentage(this.current.population, this.current[btn])) {
                           if (!set) {
                               const activeBtn = document.querySelector(`[data-${btn}]`);
                               this.toggleClass(activeBtn, SECONDARY_BTN_CLASS, SUCCESS_BTN_CLASS);
                               this.setStatsText(btn);
                               set = true;
                           }
                       }
                    });
                }
            },
            toggleClass(element, classToRemove, classToAdd) {
                element.classList.remove(classToRemove);
                element.classList.add(classToAdd);
            },
            setStatsText(stat) {
                const alert = document.getElementById('stat-text');

                const countryPrefix = this.$props.country !== 'World Wide' ? 'in ' : '';

                switch (stat) {
                    case 'recovered':
                        alert.innerText = `Of the ${this.formatNumber(this.current.totalCases)} confirmed covid-19 cases ${countryPrefix}${this.$props.country}, ${this.getPercentage(this.current.population, this.current.recovered)}% (${this.formatNumber(this.current.recovered)}) have now recovered.`;
                        break;
                    case 'deaths':
                        alert.innerText = `Of the ${this.formatNumber(this.current.totalCases)} people infected with covid-19 ${countryPrefix}${this.$props.country}, ${this.formatNumber(this.current.totalDeaths)} have died.`;
                        break;
                    case 'currentlyInfected':
                        alert.innerText = `Of the ${this.formatNumber(this.current.totalCases)} covid-19 cases ${countryPrefix}${this.$props.country}, ${this.formatNumber(this.current.activeCases)} are still active.`;
                        break;
                    case 'totalInfected':
                        alert.innerText = `Of the ${this.formatNumber(this.current.population)} people living ${countryPrefix}${this.$props.country}, ${this.formatNumber(this.current.totalCases)} have been infected.`;
                        break;
                    default:
                        alert.innerText = `No additional information could be found for ${this.$props.country}.`;
                }
            },
            getSymbol(num) {
                if (num > 0) {
                    return '+';
                }
            },
            getPercentage(x, y) {
                return (y / x * 100).toFixed(2);
            },
            getDifference(x, y) {
                return x - y;
            }
        }
    }
</script>

<style scoped>
.container {
    position: relative;
    top: 3vh;
}
#country-flag {
    display: inline-block;
    margin: 0 10px 10px 0;
    position: relative;
    bottom: 3px;
}
.country-name {
    display: inline-block;
    font-size: 2rem;
}
.btn-toggle {
    margin: 0 5px;
    min-width: 95px;
}
.margin-bottom {
    margin-bottom: 1.5rem
}
.alert-dark {
    font-style: italic;
}
.alert-light {
    color: #a8a8b5;
    background-color: transparent;
}
.outline {
    border: 1px solid white;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 25px;
}
.chart-container {
    height: 350px;
}
.stats-container {
    height: 325px;
}
.stat-info {
    display: inline-block;
    width: 130px;
    margin-top: 10px;
    margin-left: 20px;
}
.stats-header {
    background: white;
    margin: -10px -10px -5px;
    height: 45px;
    color: #1a202c;
    padding-top: 10px;
}
.main-stats {
    margin-top: 5px;
}
.bullet {
    margin-right: 10px;
    font-size: .9rem;
    font-weight: bold;
    position: relative;
    bottom: 1px;
}
.box-shadow {
    -webkit-box-shadow: 5px 7px 7px 5px rgba(0,0,0,0.47);
    box-shadow: 5px 7px 7px 5px rgba(0,0,0,0.47);
}
.country-select {
    width: 50%;
    margin: auto;
}
</style>