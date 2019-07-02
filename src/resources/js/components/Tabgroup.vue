<template>
    <div style="display: flex; flex-direction: column; width: 100%">
        <div style="display: flex; min-height: 1em; white-space: nowrap"
             role="tablist"
             v-if="tabsToShow.length > 1"
        >
            <a href="#"
               v-for="tab, tabIndex in tabsToShow"
               class="tab-activator default-tab"
               v-bind:class="{'selected-tab': tabIndex == currentTab, 'inactive-tab': tabIndex != currentTab}"
               :key="tabIndex"
               :aria-selected="tabIndex == currentTab ? 'true' : 'false'"
               :id="uId+'-tabgroup-tab-'+tabIndex"
               role="tab"
               v-on:click="currentTab = tabIndex"
               v-html="tab"
            ></a>
        </div>
        <div v-bind:class="{'tab-contents': tabsToShow.length > 1}">
            <template v-for="tab, tabIndex in tabsToShow">
                <div v-if="tabIndex == currentTab"
                     role="tabpanel"
                     :aria-labelledby="uId+'-tabgroup-tab-'+tabIndex"
                >
                    <slot :name="tabIndex"
                    ></slot>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            tabs: {type: Array},
            maxTabsShown: {type: Number, default: 4},
        },
        data: function () {
            return {
                currentTab: 0,
                uId: 0,
            }
        },
        computed: {
            tabsToShow: function () {
                if (this.tabs.length > 1) {
                    return this.tabs;
                }
                return [this.tabs[0]];
            }
        },
        mounted() {
            this.uId = Math.floor(Math.random() * 1000000)
        },
        watch: {
            currentTab: function(value) {
                this.$emit('tab-changed', value);
            }
        }

    }
</script>
<style>
    .default-tab {
        padding: 3px;
        padding-left: 1em;
        padding-right: 1em;
        border-top: 1px solid darkgrey;
        border-left: 1px solid darkgrey;
        border-right: 1px solid darkgrey;
        border-bottom: 1px solid darkgrey;
        flex-grow: 1;
        cursor: pointer;
    }

    .selected-tab {
        border-bottom: none !important;
        border-left: 2px solid darkgrey !important;
        border-right: 2px solid darkgrey !important;
        font-weight: bold;
    }

    .tab-activator,
    .tab-activator:visited {
        text-decoration: none;
        color: inherit;
    }

    .tab-activator:hover {
        opacity: .7;
    }

    .tab-contents {
        border: 1px solid darkgrey;
        border-top: none;
        padding: 5px;
    }
    .inactive-tab {
        opacity: .5;
    }
</style>