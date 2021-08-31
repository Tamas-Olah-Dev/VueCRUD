<template>
    <div :class="getCSSClass('model-manager-component-wrapper-container')">
        <component :is="subComponent"
                   v-bind="subComponentProps"
                   v-on:input="emitInput($event)"
                   v-model="subComponentValue"
        ></component>
        <button :class="getCSSClass('model-manager-component-add-button')"
                type="button"
                v-html="addButtonHTML"
                v-on:click="showForm"
        ></button>
        <popup :visible="showPopup"
               :show-close-button="false">
            <edit-form
                    v-if="showPopup"
                    v-bind:data-url="formUrl"
                    v-bind:save-url="storeUrl"
                    v-bind:ajax-operations-url="formAjaxOperationsUrl"
                    v-on:submit-success="hideFormAndSelect($event)"
                    v-on:editing-canceled="hideForm"
                    redirect-to-response-on-success="false"
                    :buttons="mainButtons"
            ></edit-form>
        </popup>
    </div>
</template>

<script>
    export default {
        inject: ['loadingIndicator', 'getCSSClass', 'translate', 'icon', 'mainButtons'],
        props: {
            subComponent: {type: String},
            defaultSubComponentProps: {type: Object},
            subComponentValuesetProp: {type: String},
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
                        this.$set(this.subComponentProps, this.subComponentValuesetProp, response.data)
                        this.$emit('valueset', this.subComponentProps[this.subComponentValuesetProp]);
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
                    this.$emit('input', this.subComponentValue)
                });
                this.hideForm();
            },
            hideForm: function() {
                //this.fetchValueset();
                this.showPopup = false;
            },
        },
        created() {
            this.$set(this, 'subComponentValue', this.value);
            this.subComponentProps = JSON.parse(JSON.stringify(this.defaultSubComponentProps));
        },
        mounted() {
            this.fetchValueset();
        }
    }
</script>