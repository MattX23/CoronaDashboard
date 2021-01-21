<template>
    <div class="container">
        <div class="row">
            <div v-if="shouldShowSearchBar" class="col-12 margin-bottom">
                <select @change="changeCountry(selectedCountry, country.name)" class="form-control country-select" v-model="selectedCountry">
                    <option :value="null" disabled selected>-- Select a Country --</option>
                    <option
                        v-for="country in countries"
                        :value="{'search': country.searchTerm, 'name': country.name}"
                        :key="country.searchTerm"
                    >
                        {{country.name}}
                    </option>
                </select>
            </div>
            <div class="col-12 text-center margin-bottom">
                <img v-if="countryCode" id="country-flag" :src="flagPath" width="64" height="64" :alt="countryName">
                <div class="text-center country-name margin-bottom">{{ countryName }}</div>
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
                <div v-show="!isLoading && current.population" class="text-center margin-bottom">
                    <div id="stat-text" class="alert alert-light">

                    </div>
                </div>
                <div class="text-center margin-bottom">
                    <button @click="toggleTotals($event)" data-totals="true" class="btn btn-sm btn-primary btn-toggle">Totals</button>
                    <button @click="toggleTotals($event)" data-updates="true" class="btn btn-sm btn-dark btn-toggle">Daily Change</button>
                </div>
                <div v-if="dataUnavailable && !isLoading" class="alert alert-dark text-center">Sorry, some data is unavailable right now</div>
                <loader v-if="isLoading"></loader>
            </div>
        </div>
        <div class="row">
            <div v-show="!isLoading" class="col-lg-8 margin-bottom">
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
            </div>
        </div>
    </div>
</template>

<script>
    import {EventBus} from "../eventbus/event-bus";

    const GET_STATS = '/api/statistics/';
    const GET_COUNTRIES = '/api/countries/';
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
        created() {
            EventBus.$on('show-search-bar', () => this.shouldShowSearchBar = true);
        },
        mounted() {
            this.countrySearchTerm = this.$props.searchName;
            this.countryName = this.$props.country;
            this.countryCode = this.$props.code;
            this.fetchStatistics();
            this.fetchCountries();
        },
        computed: {
            flagPath() {
                return this.countryCode ? `https://www.countryflags.io/${this.countryCode}/shiny/64.png` : '';
            }
        },
        data() {
            return {
                isLoading: false,
                countrySearchTerm: null,
                countryName: null,
                countryCode: null,
                dataUnavailable: false,
                monthlyView: true,
                selectedCountry: null,
                shouldShowSearchBar: false,
                countries: [],
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
                chartData: {},
            }
        },
        methods: {
            fetchStatistics() {
                this.isLoading = true;
                this.loadStatistics();
            },
            fetchCountries() {
                axios.get(GET_COUNTRIES)
                    .then(response => {
                        this.countries = response.data;
                    });
            },
            loadStatistics() {
                axios.get(`${GET_STATS}${this.countrySearchTerm}`)
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

                        this.countryCode = response.data.countryCode;

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
            changeCountry(country) {
                this.isLoading = true;
                this.countryCode = null;
                this.countryName = country.name;
                this.countrySearchTerm = country.search;
                this.loadStatistics();
                this.resetTotalBtn();
                this.setMainStats();
                this.shouldShowSearchBar = false;
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
                       if (this.current[btn] !== null) {
                           if (!set) {
                               const activeBtn = document.querySelector(`[data-${btn}]`);
                               if (activeBtn) {
                                   this.toggleClass(activeBtn, SECONDARY_BTN_CLASS, SUCCESS_BTN_CLASS);
                                   this.setStatsText(btn);
                                   set = true;
                               }
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
                alert.innerText = '';
                const countryPrefix = this.countryName !== 'World Wide' ? 'in ' : '';

                switch (stat) {
                    case 'recovered':
                        alert.innerText =
                            `Of the ${this.formatNumber(this.current.totalCases)} confirmed covid-19 cases ${countryPrefix}${this.countryName}, ${this.getPercentage(this.current.totalCases, this.current.recovered)}% (${this.formatNumber(this.current.recovered)}) have now recovered.`;
                        break;
                    case 'deaths':
                        alert.innerText =
                            `Of the ${this.formatNumber(this.current.totalCases)} people infected with covid-19 ${countryPrefix}${this.countryName}, ${this.formatNumber(this.current.totalDeaths)} have died.
                            (${this.getPercentage(this.current.population, this.current.totalDeaths)}% of the total population)`;
                        break;
                    case 'currentlyInfected':
                        alert.innerText =
                            `Of the ${this.formatNumber(this.current.totalCases)} covid-19 cases ${countryPrefix}${this.countryName}, ${this.formatNumber(this.current.activeCases)} are still active.
                            (${this.getPercentage(this.current.population, this.current.activeCases)}% of the total population)`;
                        break;
                    case 'totalInfected':
                        alert.innerText =
                            `Of the ${this.formatNumber(this.current.population)} people living ${countryPrefix}${this.countryName}, ${this.formatNumber(this.current.totalCases)} have been infected.
                            (${this.getPercentage(this.current.population, this.current.totalCases)}% of the total population)`;
                        break;
                    default:
                        alert.innerText = `No additional information could be found for ${this.countryName}.`;
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
            },
            resetTotalBtn() {
                const totalsBtn = document.querySelector('[data-totals]');
                const secondaryBtn = document.querySelector('[data-updates]');
                this.toggleBtnClass(totalsBtn, true);
                this.toggleBtnClass(secondaryBtn, false);
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
    width: 75%;
    margin: auto;
}
</style>