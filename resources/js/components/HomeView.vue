<template>
    <div class="container">
        <div class="row">
            <div v-if="shouldShowCountrySearchBar" class="col-6 center-col">
                <span
                    @click="closeSearchBar()"
                    id="close-country-search"
                    class="light-box-shadow close-btn"
                >X</span>
                <select
                    @change="changeCountry(selectedCountry, country.name)"
                    v-model="selectedCountry"
                    class="form-control country-select"
                >
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
            <div v-if="shouldShowDateBar" class="col-4 center-col">
                <span
                    @click="closeSearchBar()"
                    id="close-date-search"
                    class="light-box-shadow close-btn"
                >X</span>
                <input @change="searchByDate" v-model="targetDate" type="date" id="datePicker" class="form-control date-picker" min="2020-01-01" max="">
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
                    <b v-show="monthlyView" id="statsBoxMonthly">{{ monthlyChanges }}</b>
                    <b v-show="!monthlyView" id="statsBoxDaily">{{ dailyChanges }}</b>
                </p>
                <stats-box
                    :current="current"
                    :monthly-view="monthlyView"
                    :previous="previous"
                ></stats-box>
            </div>
        </div>
    </div>
</template>

<script>
    import {EventBus} from "../eventbus/event-bus";
    import { formatNumber, getPercentage } from "../helpers/mathematics";

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
            EventBus.$on('show-search-bar', () => {
                this.closeSearchBar();
                this.shouldShowCountrySearchBar = true;
            });
            EventBus.$on('show-date-bar', () => {
                this.closeSearchBar();
                this.shouldShowDateBar = true;
                this.$nextTick(() => document.getElementById('datePicker').setAttribute('max', this.targetDate));
            });
        },
        mounted() {
            this.countrySearchTerm = this.$props.searchName;
            this.countryName = this.$props.country;
            this.countryCode = this.$props.code;
            this.targetDate = this.getLocalDateAsString();
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
                chartData: {},
                countries: [],
                countryCode: null,
                countryName: null,
                countrySearchTerm: null,
                current: {
                    activeCases: null,
                    critical: null,
                    date: null,
                    newCases: null,
                    newDeaths: null,
                    population: null,
                    recovered: null,
                    totalCases: null,
                    totalDeaths: null,
                },
                dataUnavailable: false,
                dailyChanges: null,
                isLoading: false,
                monthlyChanges: null,
                monthlyView: true,
                previous: {
                    activeCases: null,
                    critical: null,
                    date: null,
                    newCases: null,
                    newDeaths: null,
                    recovered: null,
                    totalCases: null,
                    totalDeaths: null,
                },
                selectedCountry: null,
                shouldShowCountrySearchBar: false,
                shouldShowDateBar: false,
                targetDate: null,
            }
        },
        methods: {
            formatNumber,
            getPercentage,
            fetchStatistics() {
                this.isLoading = true;
                this.loadStatistics();
                this.resetStatsBoxText();
            },
            fetchCountries() {
                axios.get(GET_COUNTRIES)
                    .then(response => this.countries = response.data);
            },
            loadStatistics() {
                axios.get(`${GET_STATS}${this.countrySearchTerm}/${this.targetDate}`)
                    .then(response => this.setProperties(response))
                    .then(() => this.chartData = this.constructChartData(true))
                    .then(() => this.setMainStats())
                    .finally(() => this.isLoading = false);
            },
            changeCountry(country) {
                this.countryCode = null;
                this.countryName = country.name;
                this.countrySearchTerm = country.search;
                this.performNewSearch();
                this.resetStatsBoxText();
            },
            searchByDate() {
                this.performNewSearch();
                this.monthlyChanges = `Changes since ${this.targetDate}`;
                this.dailyChanges = `Daily changes: ${this.targetDate} vs Today`;
            },
            performNewSearch() {
                this.isLoading = true;
                this.loadStatistics();
                this.resetTotalBtn();
                this.setMainStats();
                this.closeSearchBar();
            },
            resetStatsBoxText() {
                this.monthlyChanges = `Changes over the last month`;
                this.dailyChanges = `Daily changes month-on-month`;
            },
            setProperties(response) {
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
                            backgroundColor: '#fff',
                            data: lastMonthData
                        },
                        {
                            label: showTotals ? 'Totals as of today' : 'Daily Figures as of today',
                            backgroundColor: '#f87979',
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
                            `Of the ${this.formatNumber(this.current.totalCases)} people infected with covid-19 ${countryPrefix}${this.countryName}, ${this.formatNumber(this.current.totalDeaths)} (${this.getPercentage(this.current.totalCases, this.current.totalDeaths)}%) have died.
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
            getDifference(x, y) {
                return x - y;
            },
            resetTotalBtn() {
                const totalsBtn = document.querySelector('[data-totals]');
                const secondaryBtn = document.querySelector('[data-updates]');
                this.toggleBtnClass(totalsBtn, true);
                this.toggleBtnClass(secondaryBtn, false);
            },
            closeSearchBar() {
                this.shouldShowCountrySearchBar = false;
                this.shouldShowDateBar = false;
            },
            getLocalDateAsString() {
                const date = new Date();
                const offsetMs = date.getTimezoneOffset() * 60 * 1000;
                const dateLocal = new Date(date.getTime() - offsetMs);

                return dateLocal.toISOString().slice(0, 10).replace(/-/g, "-").replace("T", " ");
            }
        },
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
.light-box-shadow {
    box-shadow: 5px 7px 7px 5px rgba(0,0,0,0.25);
}
.country-select {
    margin: auto;
}
.date-picker {
    margin: auto;
}
.center-col {
    margin: 0 auto 1.5rem;
}
.close-btn {
    position: absolute;
    background: #f87979;
    padding: 10px;
    border-radius: 100px;
    height: 30px;
    line-height: 11px;
    bottom: 24px;
    color: white;
    font-weight: bold;
    cursor: pointer;
    opacity: 0.85;
    right: -5px;
}
.close-btn:hover {
    background: #f86661;
    opacity: 1;
}
.box-shadow {
    -webkit-box-shadow: 5px 7px 7px 5px rgba(0,0,0,0.47);
    box-shadow: 5px 7px 7px 5px rgba(0,0,0,0.47);
}
</style>