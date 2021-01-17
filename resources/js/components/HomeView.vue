<template>
    <div class="container">
        <div class="col-12">
            <img id="country-flag" :src="flagPath" width="64" height="64" :alt="$props.country">
            {{ $props.country }}
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
                flagPath: this.code ? `https://www.countryflags.io/${this.$props.code}/shiny/64.png` : '',
            }
        },
        methods: {
            fetchStatistics() {
                axios.get(`${INITIAL_STATS}${this.$props.searchName}`).then(response => console.log(response.data));
            },
        }
    }
</script>

<style scoped>
.container {
    position: relative;
    top: 10vh;
}
#country-flag {
    display: block;
    margin: 0 auto 15px;
}
</style>