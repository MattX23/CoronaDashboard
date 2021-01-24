<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 navbar-container text-center">
                <span class="nav-item" @click="showSearchBar">Country Search</span>
                <span class="nav-item" @click="showDateBar">Date Search</span>
                <span class="nav-item" @click="showWorldWideStats">World Wide</span>
            </div>
        </div>
    </div>
</template>

<script>
import {EventBus} from "../eventbus/event-bus";

export default {
    name: "Nav",
    methods: {
        showSearchBar() {
            EventBus.$emit('show-search-bar');
        },
        showDateBar() {
            EventBus.$emit('show-date-bar');
        },
        showWorldWideStats() {
            EventBus.$emit('show-world-wide-stats');
        },
    }
}
</script>

<style lang="scss" scoped>
@import '../../sass/variables';

.navbar-container {
    padding: 10px 0;
    background: $darkBlue;
    border-bottom: 1px solid $semiTransparentWhite;
}
.nav-item {
    cursor: pointer;
    margin: 0 10px;
    font-size: 1rem;
    &:hover {
        color: $lightGrey;
    }
}
</style>