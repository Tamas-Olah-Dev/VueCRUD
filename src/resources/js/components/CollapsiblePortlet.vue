<template>
    <div class="portlet">
        <div class="portlet-heading d-flex justify-content-between align-items-center"
             v-bind:class="headingClass"
             v-on:click="showBody = !showBody"
        >
            <span v-html="title" style="max-width: 80%;"></span>
            <button class="float-right btn btn-secondary text-align-center"
                    v-on:click.stop="showBody = !showBody"
            >
                    <span ref="caret"
                          class="portlet-collapse-caret"
                          v-bind:class="caretClass"
                          v-on:click=""
                    >&#9666;</span>
            </button>
        </div>
        <div class="portlet-body" v-show="showBody">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            title: {type: String},
            headingClass: {type: String, default: 'bg-inverse'},
            defaultState: {type: Boolean, default: true}
        },
        data: function() {
            return {
                showBody: true
            }
        },
        mounted() {
            this.showBody = this.defaultState;
        },
        computed: {
            caretClass: function() {
                return this.showBody ? 'portlet-collapse-caret-open' : ''
            },
        },

    }
</script>
<style>
    .portlet-collapse-caret {
        cursor:pointer;
        font-size: 1.3em;
        transition: transform 200ms ease-in-out;
        margin-left: 5px;
        display: inline-block
    }
    .portlet-collapse-caret-open {
        transform: rotate(-90deg);
    }

</style>
