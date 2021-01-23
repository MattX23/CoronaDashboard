<script>
    import { Bar } from 'vue-chartjs'

    export default {
        props: {
            chartData: {
                type: Object
            }
        },
        extends: Bar,
        watch: {
            chartData: function(chartData) {
                if (chartData) {
                    this.$nextTick(() => this.renderChart(chartData, this.options))
                }
            }
        },
        data() {
            return {
                statsData: {},
                options: {
                    tooltips: {
                        callbacks: {
                            label (t, d) {
                                const xLabel = d.datasets[t.datasetIndex].label;
                                const yLabel = t.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                                return xLabel + ': ' + yLabel;
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: (label) => {
                                    return label.toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                                }
                            }
                        }]
                    }
                }
            }
        },
    }
</script>

<style scoped>

</style>