<template>
    <div>
        <div>
            <component :is="subComponent"
                       v-bind="subComponentProps"
                       v-on:input="emitInput($event)"
                       v-bind:value="subComponentValue"
            ></component>
        </div>
        <button :class="addButtonClass"
                type="button"
                v-html="addButtonHTML"
                v-on:click="showForm"
        ></button>
        <popup :visible="showPopup"
               :show-close-button="false">
            <edit-form
                    v-bind:data-url="formUrl"
                    v-bind:save-url="storeUrl"
                    v-bind:ajax-operations-url="formAjaxOperationsUrl"
                    v-on:submit-success="hideFormAndSelect($event)"
                    v-on:editing-canceled="hideForm"
                    redirect-to-response-on-success="false"
                    :buttons="buttons"
            ></edit-form>
        </popup>
    </div>
</template>

<script>
    export default {
        inject: ['loadingIndicator', 'getCSSClass', 'translate'],
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