<template>
    <div class="container-fluid vue-editform-container" style="position:relative">
        <div v-if="!loaded" v-html="spinnerSrc" style="width:100%; display:flex; justify-content: center"></div>
        <div v-if="loaded && (typeof(formTitle != 'undefined'))"><h4 v-html="formTitle"></h4></div>
        <div v-if="formDisabled" class="disabled-overlay"></div>
        <form role="form" class="margin-b-20"  v-on:submit.prevent="submitForm"  v-if="loaded">
            <template v-for="step in stepsToRender">
                <h4 v-if="typeof(config.stepLabels[step]) != 'undefined'"
                    class="form-step-header"
                    v-html="config.stepLabels[step]"></h4>
                <div class="row" style="position:relative">
                    <div v-if="currentStep != step" class="disabled-overlay"></div>
                    <div v-for="data, fieldname in subjectDataForStep(step)"
                         v-bind:style="{height: typeof(data.customOptions['cssHeight']) == 'undefined' ? 'auto' : data.customOptions['cssHeight']}"
                         v-bind:class="data.containerClass">
                        <label v-if="data.label != null">
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
                        <div v-if="data.kind == 'text' && data.type == 'richtext-trix'" v-bind:class="data.class" style="min-height:95%; height:95%; margin-bottom: 2em">
                            <trix-wrapper v-model="subjectData[fieldname].value"
                                          v-bind:fieldname="fieldname"
                                          v-bind="JSON.parse(data.props)"
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
                        <multi-select v-if="data.kind == 'vue-multiselect'"
                                      v-bind="JSON.parse(data.props)"
                                      :valueset="data.valuesetSorted"
                                      v-model="subjectData[fieldname].value"
                        ></multi-select>
                        <treeselect v-if="data.kind == 'vue-treeselect'"
                                    v-bind="JSON.parse(data.props)"
                                    :options="data.valuesetSorted"
                                    v-model="subjectData[fieldname].value"
                        ></treeselect>
                        <component v-if="data.kind == 'custom-component'"
                                   v-bind:is="data.type"
                                   v-bind="JSON.parse(data.props)"
                                   v-model="subjectData[fieldname].value"
                                   v-bind:errors="componentError(fieldname)"
                                   :buttons="buttons"
                        ></component>
                        <select v-if="data.kind == 'multiselect'"
                                style="height: 200px; min-height: 200px"
                                class="form-control"
                                v-bind:class="data.class"
                                multiple="multiple"
                                v-model="subjectData[fieldname].value"
                        >
                            <option v-for="valuesetvalue, valuesetitem in data.valuesetSorted"
                                    v-bind:value="valuesetvalue" v-html="valuesetitem"
                            ></option>
                        </select>

                    </div>
                </div>
            </template>
        </form>
        <div class="row" v-if="false">
            <div class="alert alert-danger col col-12"
                 v-for="error in errors" v-html="error[0]"></div>
        </div>
        <div class="row" v-if="resultMessage != ''">
            <div class="alert alert-success col col-12"
                 v-html="resultMessage"></div>
        </div>
        <div class="row" v-if="!formDisabled">
            <div class="col">
                <button type="button"
                        v-bind:class="buttons['save']['class']"
                        v-on:click="submitForm"
                >
                    <span v-if="loading" class="button-loading-indicator" v-html="spinnerSrc"></span>
                    <span v-if="currentStep != lastStep">{{ buttons['proceed']['html'] }}</span>
                    <span v-if="currentStep == lastStep">{{ buttons['save']['html'] }}</span>
                </button>
            </div>
            <div class="col">
                <button type="button"
                        v-bind:class="buttons['cancel']['class']"
                        v-on:click="cancelEditing"
                >{{ buttons['cancel']['html'] }}</button>
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
            redirectToOnCancel: {type: String},
            buttons: {type: Object, default: () => {
                return {
                    'save': {'class': 'btn-outline-primary', 'html': 'Save'},
                    'cancel': {'class': 'btn-outline-secondary', 'html': 'Cancel'},
                }
            }},
            formDisabled: {type: Boolean, default: false},
            extraUrlParameters: {type: Object, default: () => {return {}}}
        },
        data: function() {
            return {
                subjectData: {},
                errors: {},
                loaded: false,
                resultMessage: '',
                dirty: false,
                loading: false,
                config: {},
                currentStep: -1,
            }
        },
        mounted() {
            this.getFormData();
        },
        computed: {
            stepsToRender: function() {
                return this.config.steps.filter((step) => {
                    return step <= this.currentStep;
                })
            },
            lastStep: function() {
                if (typeof(this.config['steps']) != 'undefined') {
                    return this.config.steps.length > 0
                        ? this.config.steps[this.config.steps.length - 1]
                        : -1;
                }
            },
            formdata: function() {
                let result = {};
                for (let key in this.subjectData) {
                    if (this.subjectData.hasOwnProperty(key)) {
                        result[key] = this.subjectData[key].value;
                    }
                }
                result['respondWith'] = this.redirectToResponseOnSuccess == 'true'
                    ? 'url'
                    : 'subject';
                result['currentStep'] = this.currentStep;

                return result;
            },
            rootShowInPlaceEditor: function() {
                return this.$root.showInPlaceEditor;
            },
            parentShowInPlaceEditor: function() {
                return this.$parent.showInPlaceEditor;
            },
            stepConditionData: function() {
                let result = {};
                for (var i in this.subjectData) {
                    if ((this.subjectData.hasOwnProperty(i)) && (this.config.conditionFields.indexOf(i) > -1)) {
                        result[i] = this.subjectData[i].value;
                    }
                }

                return result;
            }
        },
        methods: {
            subjectDataForStep: function(step) {
                let result = {};
                for (var item in this.subjectData) {
                    if ((this.subjectData.hasOwnProperty(item)) && (this.subjectData[item].step == step)) {
                        result[item] = this.subjectData[item];
                    }
                }

                return result;
            },
            removeConfigFromSubjectData: function(subjectData) {
                let result = {};
                for (var item in subjectData) {
                    if ((subjectData.hasOwnProperty(item)) && (item != 'config')) {
                        result[item] = subjectData[item];
                    }
                }

                return result;
            },
            componentError: function(fieldId) {
                if (typeof(this.errors[fieldId]) != 'undefined') {
                    return this.errors[fieldId];
                }

                return [];
            },
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
                window.axios.get(this.dataUrl, {params: this.extraUrlParameters})
                    .then((response) => {
                        this.subjectData = this.removeConfigFromSubjectData(response.data);
                        this.config = response.data.config;
                        this.currentStep = this.config.mode == 'creating' ? 1 : this.lastStep;
                        this.loaded = true;
                        this.dirty = false;
                    })
                    .catch((error) => {
                    });
            },
            updateFormData: function() {
                this.loaded = false;
                window.axios.get(this.dataUrl, {params: {subjectdata: this.stepConditionData, currentStep: this.currentStep}})
                    .then((response) => {
                        let additional = this.removeConfigFromSubjectData(response.data);
                        for (var i in additional) {
                            if (additional.hasOwnProperty(i)) {
                                Vue.set(this.subjectData, i, {...additional[i]});
                            }
                        }
                        this.loaded = true;
                        this.dirty = false;
                    })
                    .catch((error) => {
                    });
            },
            submitForm: function() {
                this.errors = {};
                this.loading = true;
                this.$emit('submit-pending', this.formdata);
                window.axios.post(this.saveUrl, this.formdata)
                    .then((response) => {
                        if (this.currentStep != this.lastStep) {
                            this.currentStep++;
                            this.updateFormData();
                        } else {
                            if (typeof(this.successCallback) != 'undefined') {
                                window[this.successCallback]();
                            }
                            this.$emit('submit-success', response.data);
                            if (this.redirectToResponseOnSuccess == 'true') {
                                window.location.href = response.data;
                            }
                            this.resultMessage = 'Változások elmentve';
                            setTimeout(() => {this.resultMessage = ''}, 3000);
                        }
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
                const a = 'àáäâãåăæçèéëêǵḧìíïîḿńǹñòóöôőœṕŕßśșțùúüûǘűẃẍÿź·/_,:;'
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
<style>
    .disabled-overlay {
        position: absolute;
        top: -2px;
        left: -2px;
        width: 101%;
        height: 101%;
        z-index: 2000;
        background-color: rgba(210, 210, 210, .2);
    }
    h4.form-step-header {
        margin-top:20px;
        margin-left:-5px;
        margin-right:-5px;
        border-top: 2px solid lightgrey;
        border-left: 1px solid lightgrey;
        padding: 5px;
    }
</style>