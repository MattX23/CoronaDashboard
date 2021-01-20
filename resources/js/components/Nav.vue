<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 navbar-container">
                <span class="nav-item">Country Search</span>
                <span class="nav-item">Comparisons</span>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Nav"
    }
</script>

<style lang="scss" scoped>
.navbar-container {
    padding-top: 10px;
}
.nav-item {
    cursor: pointer;
    margin-left: 20px;
    font-size: 1rem;
}
</style>