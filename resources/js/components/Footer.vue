<template>
    <div class="container-fluid footer">
        <div class="row">
            <div class="col-12 footer-container text-center">
                <a href="https://github.com/MattX23" title="Matt Wood on Github" target="_blank">© Matt Wood</a>
            </div>
            <div class="col-12 footer-container text-center additional-info">
                This website was made with data from the <a href="https://rapidapi.com/api-sports/api/covid-193/" title="COVID-19 API" class="subtle-link" target="_blank">COVID-19 API</a> made available by Rapid API.
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Footer",
}
</script>

<style lang="scss" scoped>
@import '../../sass/variables';

.footer {
    padding: 10px 0;
    background: $darkBlue;
    position: absolute;
    bottom: 0;
    overflow: hidden;
    height: $footerHeight;
    border-top: 1px solid $semiTransparentWhite;
}
.footer-container {
    margin-top: 15px;
}
.additional-info {
    font-style: italic;
    color: $lighterGrey;
}
</style>