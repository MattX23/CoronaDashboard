<template>
    <div id="main">
        <nav-bar></nav-bar>
        <home-view
            :code="$props.code"
            :country="$props.country"
            :search-name="$props.searchName"
        ></home-view>
        <site-footer></site-footer>
    </div>
</template>
<script>

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
    }
};
</script>

<style lang="scss">
@import '../../sass/variables';

#main {
    color: $white;
    position: relative;
    min-height: 100vh;
}
</style>