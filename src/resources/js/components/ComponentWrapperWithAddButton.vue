<template>
    <div>
        <div style="display: flex">
            <div style="flex-basis: 80%">
                <component is="subComponent"
                           v-bind="subComponentProps"
                           v-on:input="emitInput($event)"
                ></component>
            </div>
            <button :class="addButtonClass" v-html="addButtonHTML"></button>
        </div>
        <div class="trix-wrapper-modal-overlay" v-if="showPopup">
            <div class="trix-wrapper-modal">
                <edit-form
                        v-bind:data-url="formUrl"
                        v-bind:save-url="storeUrl"
                        v-bind:ajax-operations-url="formAjaxOperationsUrl"
                        v-on:submit-success="showPopup=false"
                        v-on:editing-canceled="showPopup=false"
                        redirect-to-response-on-success="false"
                ></edit-form>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        props: {
            subComponent: {type: String},
            defaultSubComponentProps: {type: Object},
            subComponentValuesetProp: {type: String},
            addButtonClass: {type: String, default: 'btn btn-primary'},
            addButtonHTML: {type: String, default: '+'},
            formUrl: {type: String},
            storeUrl: {type: String},
            formAjaxOperationsUrl: {type: String, default: ''},
            fetchUrl: {type: String},
            value: {},
        },
        data: function() {
            return {
                showPopup: false,
                subComponentProps: {},
            }
        },
        methods: {
            emitInput: function(payload) {
                this.$emit('input', payload);
            },
            fetchValueset: function(callback) {
                window.axios.get(this.fetchUrl)
                    .then((response) => {
                        this.subComponentProps[this.subComponentValuesetProp] = response.data.valueset;
                        if (typeof(callback) != 'undefined') {
                            this.callback(response.data);
                        }
                    })
            }
        },
        mounted() {
            this.subComponentProps =  {...this.defaultSubComponentProps};
        }
    }
</script>
<style>
    .component-wrapper-modal-overlay {
        z-index:1000;
        width: 100%;
        height: 100%;
        position: fixed;
        left: 0px;
        top: 0px;
        background-color: rgba(192,192,192,.5);
    }
    .component-wrapper-modal {
        width: 85%;
        max-width: 85%;
        min-width: 85%;
        height: 80vh;
        max-height: 80vh;
        min-height: 80vh;
        left: 7%;
        top: 10vh;
        padding: 1em;
        background-color: white;
        box-shadow: 5px 5px darkgrey;
        position:absolute;
        display: flex;
        flex-direction: column;
    }

</style>
