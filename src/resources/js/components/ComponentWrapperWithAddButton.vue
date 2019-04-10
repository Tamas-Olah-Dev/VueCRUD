<template>
    <div>
        <div style="display: flex">
            <div style="flex-basis: 80%">
                <component :is="subComponent"
                           v-bind="subComponentProps"
                           v-on:input="emitInput($event)"
                           v-bind:value="subComponentValue"
                ></component>
            </div>
            <button :class="addButtonClass"
                    type="button"
                    v-html="addButtonHTML"
                    style="flex-basis: 20%"
                    v-on:click="showForm"
            ></button>
        </div>
        <div class="component-wrapper-modal-overlay"
             v-if="showPopup"
             v-on:click.self="hideForm"
        >
            <div class="component-wrapper-modal">
                <edit-form
                        v-bind:data-url="formUrl"
                        v-bind:save-url="storeUrl"
                        v-bind:ajax-operations-url="formAjaxOperationsUrl"
                        v-on:submit-success="hideFormAndSelect($event)"
                        v-on:editing-canceled="hideForm"
                        redirect-to-response-on-success="false"
                        :buttons="buttons"
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
            buttons: {type: Object}
        },
        data: function() {
            return {
                showPopup: false,
                subComponentProps: {},
                subComponentValue: null,
            }
        },
        methods: {
            emitInput: function(payload) {
                this.$emit('input', payload);
            },
            fetchValueset: function(callback) {
                window.axios.get(this.fetchUrl)
                    .then((response) => {
                        Vue.set(this.subComponentProps, this.subComponentValuesetProp, response.data.elements)
                        if (typeof(callback) != 'undefined') {
                            callback(response.data);
                        }
                    })
            },
            showForm: function() {
                this.showPopup = true;
            },
            hideFormAndSelect: function(subject) {
                this.fetchValueset(() => {
                    this.subComponentValue = subject.id;
                });
                this.hideForm();
            },
            hideForm: function() {
                this.fetchValueset();
                this.showPopup = false;
            },
        },
        mounted() {
            this.subComponentValue = this.value;
            this.subComponentProps =  {...this.defaultSubComponentProps};
            this.fetchValueset();
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
