<template>
    <div class="container-fluid footer">
        <div class="row">
            <div class="col-12 footer-container text-center">
                <a href="https://github.com/MattX23" title="Matt Wood on Github">© Matt Wood</a>
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
    height: $footerHeight
}
.footer-container {
    margin-top: 15px;
}
</style>