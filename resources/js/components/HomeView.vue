<template>
    <div class="container">
        <div class="col-12">
            <img v-if="$props.code !== 'All'" id="country-flag" :src="flagPath" width="64" height="64" :alt="$props.country">
            <div class="text-center country-name margin-bottom">{{ $props.country }}</div>
            <div class="text-center margin-bottom">
                <button @click="toggleTotals($event)" data-totals="true" class="btn btn-sm btn-success btn-toggle">Totals</button>
                <button @click="toggleTotals($event)" data-updates="true" class="btn btn-sm btn-dark btn-toggle">Today's Data</button>
            </div>
            <bar-chart
                :chart-data="chartData"
            ></bar-chart>
        </div>
    </div>
</template>

<script>
    const INITIAL_STATS = '/api/statistics/';

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
                activeCases: null,
                totalDeaths: null,
                recovered: null,
                totalCases: null,
                critical: null,
                newDeaths: null,
                newCases: null,
                flagPath: this.code ? `https://www.countryflags.io/${this.$props.code}/shiny/64.png` : '',
                chartData: {},
            }
        },
        methods: {
            fetchStatistics() {
                axios.get(`${INITIAL_STATS}${this.$props.searchName}`)
                    .then(response => {
                        this.activeCases = response.data.activeCases;
                        this.totalDeaths = response.data.totalDeaths;
                        this.recovered = response.data.recovered;
                        this.totalCases = response.data.totalCases;
                        this.critical = response.data.critical;
                        this.newDeaths = response.data.newDeaths;
                        this.newCases = response.data.newCases;
                    })
                    .then(() => this.chartData = this.constructChartData(true));
            },
            formatNumber(num) {
                return num ? num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : "";
            },
            constructChartData(showTotals) {

                const stats = showTotals ?
                    {
                        'Deaths': this.totalDeaths,
                        'Active Cases': this.activeCases,
                        'Critical': this.critical,
                        'Recovered': this.recovered,
                        'Total Cases': this.totalCases,
                    } :
                    {
                        'Deaths': this.newDeaths,
                        'New Cases': this.newCases,
                    };

                const labels = [];
                const data = [];

                Object.entries(stats).forEach(([key, value]) => {
                    if (value) {
                        labels.push(key);
                        data.push(value);
                    }
                });

                return {
                    labels: labels,
                    datasets: [
                        {
                            label: showTotals ? 'Totals' : 'Daily Figures',
                            backgroundColor: showTotals ? '#f87979' : '#fff',
                            data: data
                        },
                    ]
                }
            },
            toggleTotals(e) {
                const clickedEl = e.target;

                if (clickedEl.classList.contains('btn-success')) return;

                const showTotals = clickedEl.dataset.totals !== undefined;
                const secondaryBtn = showTotals ? document.querySelector('[data-updates]') : document.querySelector('[data-totals]');
                this.toggleBtnClass(secondaryBtn, false);
                this.toggleBtnClass(clickedEl, true);

                this.chartData = this.constructChartData(showTotals);
            },
            toggleBtnClass(el, shouldBeActive) {
                if (shouldBeActive) {
                    el.classList.remove('btn-dark');
                    el.classList.add('btn-success');
                } else {
                    el.classList.remove('btn-success');
                    el.classList.add('btn-dark');
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
    top: 6vh;
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
    margin-bottom: 2rem;
}
</style>