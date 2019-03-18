<template>
    <div class="container-fluid vue-editform-container">
        <div v-if="!loaded"><h4>{{ translate('Betöltés') }}...</h4></div>
        <div v-if="loaded && (typeof(formTitle != 'undefined'))"><h4 v-html="formTitle"></h4></div>
        <form role="form" class="margin-b-20"  v-on:submit.prevent="submitForm">
            <div class="row" v-if="loaded" >
                <div v-for="data, fieldname in subjectData"
                     v-bind:class="data.containerClass"
                     v-bind:style="{'height': typeof(data.customOptions['cssHeight']) == 'undefined' ? 'auto' : data.customOptions['cssHeight']}"
                >
                    <label>
                        {{ data.label }}
                        <span v-if="data.mandatory"> *</span>
                        <span class="edit-form-label-tooltip" v-if="typeof(data.helpTooltip) != 'undefined'" v-html="data.helpTooltip"></span>
                        <span v-if="errorExists(fieldname)" class="text-danger validation-error-label-message" v-html="errors[fieldname][0]"></span>
                    </label>
                    <input v-if="data.kind == 'input'  && data.type != 'password'"
                           v-model="subjectData[fieldname].value"
                           v-bind:class="data.class"
                    >
                    <div v-if="data.kind == 'slug'">
                        <input v-model="subjectData[fieldname].value"
                               v-bind:class="data.class"
                               style="padding-right: 1.5em; display: inline-block; width: 90%">
                        <span style="margin-left: -1.5em; cursor:pointer"
                              v-bind:title="translate('Generate slug')"
                              v-on:click="generateSlug(data.customOptions['source'], fieldname)"
                        >↺</span>
                    </div>
                    <input v-if="data.kind == 'input' && data.type == 'password'"
                           v-model="subjectData[fieldname].value"
                           v-bind:class="data.class"
                           type="password"
                    >
                    <number-field v-if="data.kind == 'numberfield'"
                                  editable="true"
                                  input-class="form-control col-12"
                                  show-currency-label="true"
                                  container-class="col-12"
                                  v-model="subjectData[fieldname].value"
                                  v-bind="JSON.parse(data.props)"
                    ></number-field>

                    <textarea v-if="data.kind == 'text' && data.type == 'simple'"
                              v-model="subjectData[fieldname].value"
                              v-bind:class="data.class"
                    ></textarea>
                    <div v-if="data.kind == 'text' && data.type == 'richtext-trix'" v-bind:class="data.class" style="min-height:90%; max-height:90%">
                        <trix-wrapper v-model="subjectData[fieldname].value"
                                      v-bind:fieldname="fieldname"
                                      v-bind:ajax-operations-url="ajaxOperationsUrl"
                        ></trix-wrapper>
                    </div>
                    <select v-if="data.kind == 'select' && (data.type == null || data.type == 'yesno' || data.type == 'custom')"
                            v-model="subjectData[fieldname].value"
                            v-bind:class="data.class"
                    >
                        <option v-for="valuesetvalue, valuesetitem in data.valuesetSorted"
                                v-bind:value="valuesetvalue"
                                v-html="valuesetitem">
                        </option>
                    </select>
                    <select-or-add-field v-if="data.kind == 'select' && data.type == 'select-or-add-field'"
                                         v-bind="JSON.parse(data.props)"
                                         v-model="subjectData[fieldname].value"
                    ></select-or-add-field>
                    <datepicker v-if="data.kind == 'datepicker'"
                                v-bind="JSON.parse(data.props)"
                                v-model="subjectData[fieldname].value"
                    ></datepicker>
                    <image-picker v-if="data.kind == 'imagepicker'"
                                  v-bind="JSON.parse(data.props)"
                                  v-model="subjectData[fieldname].value"
                                  v-bind:upload-url="ajaxOperationsUrl"
                    ></image-picker>
                    <span v-if="data.kind == 'radio'">
                        <p v-for="valuesetvalue, valuesetitem in data.valuesetSorted">
                            <input
                                    type="radio"
                                    v-model="subjectData[fieldname].value"
                                    :id="fieldname+'_'+valuesetvalue"
                                    :value="valuesetvalue">
                            <label :for="fieldname+'_'+valuesetvalue" v-html="valuesetitem">
                            </label>
                        </p>
                    </span>
                    <span v-if="data.kind == 'checkbox'">
                        <p v-for="valuesetvalue, valuesetitem in data.valuesetSorted">
                            <input
                                    type="checkbox"
                                    v-model="subjectData[fieldname].value[valuesetvalue]"
                                    :id="fieldname+'_'+valuesetvalue"
                                    :value="valuesetvalue">
                            <label :for="fieldname+'_'+valuesetvalue" v-html="valuesetitem">
                            </label>
                        </p>
                    </span>
                </div>
            </div>
        </form>
        <div class="row" v-if="false">
            <div class="alert alert-danger col col-12"
                 v-for="error in errors" v-html="error[0]"></div>
        </div>
        <div class="row" v-if="resultMessage != ''">
            <div class="alert alert-success col col-12"
                 v-html="resultMessage"></div>
        </div>
        <div class="row">
            <div class="col">
                <button type="button"
                        class="btn btn-lg btn-outline-primary btn-block"
                        v-on:click="submitForm"
                >
                    <span v-if="loading" class="button-loading-indicator" v-html="spinnerSrc"></span>
                    <span>{{ translate("Mentés") }}</span></button>
            </div>
            <div class="col">
                <button type="button"
                        class="btn btn-lg btn-outline-secondary btn-block"
                        v-on:click="cancelEditing"
                >{{ translate("Mégsem") }}</button>
            </div>
        </div>
    </div>
</template>

<script>
    import {translateMixin} from './mixins/translateMixin.js'
    import {spinner} from './mixins/spinner.js'
    export default {
        mixins: [translateMixin, spinner],
        props: {
            dataUrl: {type: String},
            saveUrl: {type: String},
            ajaxOperationsUrl: {type: String, default: ''},
            successCallback: {type: String},
            formTitle: {type: String},
            redirectToResponseOnSuccess: {type: String},
            redirectToOnCancel: {type: String}

        },
        data: function() {
            return {
                subjectData: {},
                errors: {},
                loaded: false,
                resultMessage: '',
                dirty: false,
                loading: false,
            }
        },
        mounted() {
            this.getFormData();
        },
        computed: {
            formdata: function() {
                let result = {};
                for (let key in this.subjectData) {
                    if (this.subjectData.hasOwnProperty(key)) {
                        result[key] = this.subjectData[key].value;
                    }
                }

                return result;
            },
            rootShowInPlaceEditor: function() {
                return this.$root.showInPlaceEditor;
            },
            parentShowInPlaceEditor: function() {
                return this.$parent.showInPlaceEditor;
            }
        },
        methods: {
            translate: function(string) {
                if ((typeof(window.laravelLocale) != 'undefined')
                    && (typeof(window.laravelLocales[window.laravelLocale]) != 'undefined')) {
                    if (typeof(window.laravelLocales[window.laravelLocale][string]) != 'undefined') {
                        return window.laravelLocales[window.laravelLocale][string];
                    }
                }
                if (typeof(this.$root.translate) != 'undefined') {
                    return this.$root.translate(string);
                }

                return string;
            },
            errorExists: function(fieldname) {
                return this.errors.hasOwnProperty(fieldname) && Array.isArray(this.errors[fieldname]);
            },
            isItemValid: function(fieldId) {
                let item = this.subjectData[fieldId];
                result = true;
                if ((item.mandatory) &&
                    ((item.value == null) || (item.value == -1) || (item.value == ''))) {
                    result = false;
                }
                return result;
            },
            getFormData: function() {
                this.loaded = false;
                window.axios.get(this.dataUrl)
                    .then((response) => {
                        this.subjectData = (response.data);
                        this.loaded = true;
                        this.dirty = false;
                    })
                    .catch((error) => {
                    });

            },
            submitForm: function() {
                this.errors = {};
                this.loading = true;
                window.axios.post(this.saveUrl, this.formdata)
                    .then((response) => {
                        if (typeof(this.successCallback) != 'undefined') {
                            window[this.successCallback]();
                        }
                        this.$emit('submit-success', response.data);
                        if (this.redirectToResponseOnSuccess == 'true') {
                            window.location.href = response.data;
                        }
                        this.resultMessage = 'Változások elmentve';
                        setTimeout(() => {this.resultMessage = ''}, 3000);
                        this.loading = false;
                    })
                    .catch((error) => {
                        if (error.response.status == 422) {
                            this.errors = error.response.data.errors;
                        }
                        this.loading = false;
                    });
            },
            cancelEditing: function() {
                this.subjectData = {};
                this.$emit('editing-canceled');
                if (typeof(this.redirectToOnCancel) != 'undefined') {
                    window.location.href = this.redirectToOnCancel;
                }
            },
            slugify: function(string) {
                //credit to https://medium.com/@mhagemann/the-ultimate-way-to-slugify-a-url-string-in-javascript-b8e4a0d849e1
                const a = 'àáäâãåăæçèéëêǵḧìíïîḿńǹñòőóöôœṕŕßśșțùúüűûǘẃẍÿź·/_,:;'
                const b = 'aaaaaaaaceeeeghiiiimnnnooooooprssstuuuuuuwxyz------'
                const p = new RegExp(a.split('').join('|'), 'g')
                return string.toString().toLowerCase()
                    .replace(/\s+/g, '-') // Replace spaces with -
                    .replace(p, c => b.charAt(a.indexOf(c))) // Replace special characters
                    .replace(/&/g, '-and-') // Replace & with ‘and’
                    .replace(/[^\w\-]+/g, '') // Remove all non-word characters
                    .replace(/\-\-+/g, '-') // Replace multiple - with single -
                    .replace(/^-+/, '') // Trim - from start of text
                    .replace(/-+$/, '') // Trim - from end of text
            },
            generateSlug: function(field, fieldname) {
                let sourceText = this.subjectData[field].value;
                this.subjectData[fieldname].value = this.slugify(sourceText);

            }
        },
        watch: {
            subjectData: function() {
                this.dirty = true;
            },
            rootShowInPlaceEditor: function() {
                if (this.rootShowInPlaceEditor)  {
                    this.getFormData();
                }
            },
            parentShowInPlaceEditor: function() {
                if (this.parentShowInPlaceEditor)  {
                    this.getFormData();
                }
            }
        }
    }
</script>