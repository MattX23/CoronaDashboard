<template>
    <div id="main">
        <nav-bar></nav-bar>
        <home-view
            :code="$props.code"
            :country="$props.country"
            :search-name="$props.searchName"
        ></home-view>
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
    },
    data() {
        return {

        }
    }
};
</script>

<style lang="scss">
#main {
    background: #1a202c;
    height: 100vh;
    color: white;
}
</style>