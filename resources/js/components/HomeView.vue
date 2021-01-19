<template>
    <div class="container">
        <div class="col-12">
            <img v-if="$props.code" id="country-flag" :src="flagPath" width="64" height="64" :alt="$props.country">
            <div class="text-center country-name margin-bottom">{{ $props.country }}</div>
            <div v-if="!isLoading" class="text-center margin-bottom">
                <span v-if="current.population && current.activeCases" class="btn btn-dark btn-sm non-btn">Currently Infected: {{ currentlyInfected }}%</span>
                <span v-if="current.population && current.totalCases" class="btn btn-dark btn-sm non-btn">Total Infected: {{ totalInfected }}%</span>
                <span v-if="current.totalCases && current.recovered" class="btn btn-success btn-sm non-btn">Recovered: {{ recovered }}%</span>
                <span v-if="current.totalCases && current.totalDeaths" class="btn btn-dark btn-sm non-btn">Deaths: {{ deaths }}%</span>
            </div>
            <div class="text-center margin-bottom">
                <button @click="toggleTotals($event)" data-totals="true" class="btn btn-sm btn-primary btn-toggle">Totals</button>
                <button @click="toggleTotals($event)" data-updates="true" class="btn btn-sm btn-dark btn-toggle">Daily Change</button>
            </div>
            <div v-if="dataUnavailable" class="alert alert-dark text-center">Sorry, this data isn't available yet</div>
            <div v-if="isLoading" class="text-center">
                <div class="loadingio-spinner-spinner-jxdhf28fic8"><div class="ldio-qrstulbbdv8">
                    <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
                </div></div>
            </div>
            <bar-chart
                v-show="!dataUnavailable"
                :chart-data="chartData"
            ></bar-chart>
        </div>
    </div>
</template>

<script>
    const INITIAL_STATS = '/api/statistics/';
    const ACTIVE_BTN_CLASS = 'btn-primary';
    const SECONDARY_BTN_CLASS = 'btn-dark';

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
        computed: {
            currentlyInfected() {
                if (this.current.population && this.current.activeCases) return (this.current.activeCases / this.current.population * 100).toFixed(2);
            },
            totalInfected() {
                if (this.current.population && this.current.totalCases) return (this.current.totalCases / this.current.population * 100).toFixed(2);
            },
            recovered() {
                if (this.current.totalCases && this.current.recovered) return (this.current.recovered / this.current.totalCases * 100).toFixed(2);
            },
            deaths() {
                if (this.current.totalCases && this.current.totalDeaths) return (this.current.totalDeaths / this.current.totalCases * 100).toFixed(2);
            },
        },
        data() {
            return {
                isLoading: false,
                dataUnavailable: false,
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
                    .finally(() => this.isLoading = false);
            },
            formatNumber(num) {
                return num ? num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : "";
            },
            constructChartData(showTotals) {
                this.dataUnavailable = false;

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
                if (shouldBeActive) {
                    el.classList.remove(SECONDARY_BTN_CLASS);
                    el.classList.add(ACTIVE_BTN_CLASS);
                } else {
                    el.classList.remove(ACTIVE_BTN_CLASS);
                    el.classList.add(SECONDARY_BTN_CLASS);
                }
            },
            formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
    display: block;
    margin: 0 auto 15px;
}
.country-name {
    font-size: 2rem;
}
.btn-toggle {
    margin: 0 5px;
    min-width: 95px;
}
.margin-bottom {
    margin-bottom: 1.5rem
}
.non-btn:hover {
    cursor: default;
}
.alert-dark {
    font-style: italic;
}
@keyframes ldio-qrstulbbdv8 {
    0% { opacity: 1 }
    100% { opacity: 0 }
}
.ldio-qrstulbbdv8 div {
    left: 120.78999999999999px;
    top: 47.544999999999995px;
    position: absolute;
    animation: ldio-qrstulbbdv8 linear 1.3513513513513513s infinite;
    background: #f87979;
    width: 15.419999999999998px;
    height: 17.99px;
    border-radius: 7.709999999999999px / 8.995px;
    transform-origin: 7.709999999999999px 80.955px;
}.ldio-qrstulbbdv8 div:nth-child(1) {
     transform: rotate(0deg);
     animation-delay: -1.2762762762762763s;
     background: #f87979;
 }.ldio-qrstulbbdv8 div:nth-child(2) {
      transform: rotate(20deg);
      animation-delay: -1.2012012012012012s;
      background: #f87979;
}.ldio-qrstulbbdv8 div:nth-child(3) {
   transform: rotate(40deg);
   animation-delay: -1.1261261261261262s;
   background: #f87979;
}.ldio-qrstulbbdv8 div:nth-child(4) {
    transform: rotate(60deg);
    animation-delay: -1.0510510510510511s;
    background: #f87979;
}.ldio-qrstulbbdv8 div:nth-child(5) {
     transform: rotate(80deg);
     animation-delay: -0.975975975975976s;
     background: #f87979;
      }

.ldio-qrstulbbdv8 div:nth-child(6) {
    transform: rotate(100deg);
    animation-delay: -0.9009009009009009s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(7) {
    transform: rotate(120deg);
    animation-delay: -0.8258258258258258s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(8) {
    transform: rotate(140deg);
    animation-delay: -0.7507507507507507s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(9) {
    transform: rotate(160deg);
    animation-delay: -0.6756756756756757s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(10) {
    transform: rotate(180deg);
    animation-delay: -0.6006006006006006s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(11) {
    transform: rotate(200deg);
    animation-delay: -0.5255255255255256s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(12) {
    transform: rotate(220deg);
    animation-delay: -0.45045045045045046s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(13) {
    transform: rotate(240deg);
    animation-delay: -0.37537537537537535s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(14) {
    transform: rotate(260deg);
    animation-delay: -0.3003003003003003s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(15) {
    transform: rotate(280deg);
    animation-delay: -0.22522522522522523s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(16) {
    transform: rotate(300deg);
    animation-delay: -0.15015015015015015s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(17) {
    transform: rotate(320deg);
    animation-delay: -0.07507507507507508s;
    background: #f87979;
}

.ldio-qrstulbbdv8 div:nth-child(18) {
    transform: rotate(340deg);
    animation-delay: 0s;
    background: #f87979;
}

.loadingio-spinner-spinner-jxdhf28fic8 {
    width: 257px;
    height: 257px;
    display: inline-block;
    overflow: hidden;
    background: none;
}

.ldio-qrstulbbdv8 {
    width: 100%;
    height: 100%;
    position: relative;
    transform: translateZ(0) scale(1);
    backface-visibility: hidden;
    transform-origin: 0 0; /* see note above */
}

.ldio-qrstulbbdv8 div {
    box-sizing: content-box;
}

/* generated by https://loading.io/ */
</style>