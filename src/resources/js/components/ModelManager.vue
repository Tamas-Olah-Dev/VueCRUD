<template>
    <div class="container-fluid model-manager-container">
        <div class="row">
            <div class="col-12">
                <div v-if="mode == 'loading'">{{ translate('Loading') }}...</div>
                <div v-if="mode == 'list'" class="row">
                    <div class="col-12"
                         style="margin-bottom: 5px"
                    >
                        <button class="btn btn-primary float-right" v-on:click="createElement">{{ translate('New') }}...</button>
                    </div>
                    <div v-if="JSON.stringify(filters) != '{}'" class="portlet full-width-div model-manager-filter-container">
                        <div class="portlet-heading  bg-inverse d-flex justify-content-between">
                            <div>
                                <span v-bind:class="iconClasses.filter"></span>
                                {{ translate('Filters') }}
                            </div>
                            <button class="btn btn-outline-light"
                                    v-on:click="resetFilters"
                                    v-html="translate('Reset')"
                            ></button>
                        </div>
                        <div class="d-flex portlet-body">
                            <div v-for="filterData, filterName in filters" class="form-group m-1 model-manager-filter-block">
                                <label v-html="filterData['label']"></label>
                                <datepicker v-if="filterData['type'] == 'datepicker'"
                                            locale="hu"
                                            v-model="filterData['value']"
                                ></datepicker>
                                <input v-if="filterData['type'] == 'text'"
                                       type="text"
                                       class="form-control"
                                        v-model="filterData['value']"
                                >
                                <select v-if="filterData['type'] == 'select'"
                                        class="form-control"
                                        v-model="filterData['value']">
                                    <option v-for="name, id in filterData['valueset']"
                                            v-bind:value="id"
                                            v-html="name"
                                    ></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet full-width-div">
                        <div class="portlet-heading bg-primary d-flex justify-content-between">
                            <div class="portlet-title">
                                <span v-bind:class="iconClasses.list"></span>
                                <span v-html="totalLabel"></span>
                            </div>
                            <div class="model-manager-paging-controls d-flex align-items-center"
                                 v-if="typeof(counts['total']) != 'undefined'"
                            >
                                <span>{{ counts['filtered'] }}&nbsp;/&nbsp;{{ counts['total'] }}&nbsp;&nbsp;</span>
                                <button class="btn btn-outline-secondary"
                                        v-on:click="previousPage"
                                ><span v-bind:class="iconClasses.leftArrow"></span></button>
                                <select class="form-control" v-model="currentPage">
                                    <option v-for="p in pageOptions"
                                            v-bind:value="p"
                                            v-html="p"
                                    ></option>
                                </select>
                                <button class="btn btn-outline-secondary"
                                        v-on:click="nextPage"
                                ><span v-bind:class="iconClasses.rightArrow"></span></button>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th v-for="columnName, columnField in columns" v-html="columnName"></th>
                                    <th v-if="allowOperations == 'true'">{{ translate('Operations') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="element in elements">
                                    <td v-for="columnName, columnField in columns" v-html="element[columnField]"></td>
                                    <td v-if="allowOperations == 'true'">
                                        <button v-if="showButton('details')"
                                                v-bind:class="buttons['details']['class']"
                                                v-on:click="showDetails(element[idProperty])"
                                                v-html="buttons['details']['html']"
                                        ></button>
                                        <button v-if="showButton('edit')"
                                                v-bind:class="buttons['edit']['class']"
                                                v-on:click="editElement(element[idProperty])"
                                                v-html="buttons['edit']['html']"
                                        ></button>
                                        <button v-if="showButton('delete')"
                                                v-bind:class="buttons['delete']['class']"
                                                v-on:click="confirmElementDeletion(element[idProperty], element[nameProperty])"
                                                v-html="buttons['delete']['html']"
                                        ></button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div  v-if="mode == 'details'">
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-info float-right" v-on:click="fetchElements">{{ translate('Back to the list') }}</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <dl>
                                <template v-for="fieldName, fieldProperty in fields">
                                    <dt v-html="fieldName"></dt>
                                    <dd v-html="model[fieldProperty]"></dd>
                                </template>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col"
                             v-if="model.hasOwnProperty('additional_details_rendered')"
                             v-html="model['additional_details_rendered']"
                        ></div>
                    </div>
                </div>
                <div  v-if="mode == 'edit'">
                    <div class="portlet full-width-div">
                        <div class="portlet-heading bg-primary">
                            {{ translate('Edit element') }}
                        </div>
                        <div class="portlet-body">
                            <edit-form
                                    v-bind:data-url="currentEditUrl"
                                    v-bind:save-url="currentUpdateUrl"
                                    v-on:submit-success="fetchElements"
                                    v-on:editing-canceled="fetchElements"
                                    redirect-to-response-on-success="false"
                            ></edit-form>
                        </div>
                    </div>
                </div>
                <div  v-if="mode == 'create'">
                    <edit-form
                            v-bind:data-url="createUrl"
                            v-bind:save-url="storeUrl"
                            v-on:submit-success="fetchElements"
                            v-on:editing-canceled="fetchElements"
                            redirect-to-response-on-success="false"
                    ></edit-form>
                </div>
                <div v-if="mode == 'delete-confirmation'">
                    <div class="alert alert-danger">{{ translate('Are you sure you want to delete this element') }}: {{ currentSubjectName }} ?</div>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-danger" v-on:click="deleteElement">{{ translate('Yes') }}</button>
                        <button class="btn btn-default" v-on:click="fetchElements">{{ translate('Cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            indexUrl: {type: String, required: true},
            detailsUrl: {type: String, required: true},
            createUrl: {type: String, required: true},
            editUrl: {type: String, required: true},
            storeUrl: {type: String, required: true},
            updateUrl: {type: String, required: true},
            deleteUrl: {type: String, required: true},
            allowOperations: {type: String, default: 'true'},
            nameProperty: {type: String, default: 'name'},
            idProperty: {type: String, default: 'id'},
            itemsPerPage: {type: Number, default: 20},
            buttons: {type:Object, default: function() {
                return {
                    details: {
                        class: 'btn btn-primary btn-block',
                        html: 'Részletek',
                        adminNeeded: false,
                    },
                    edit: {
                        class: 'btn btn-info btn-block',
                        html: 'Szerkesztés',
                        adminNeeded: true,
                    },
                    delete: {
                        class: 'btn btn-danger btn-block',
                        html: 'Törlés',
                        adminNeeded: true,
                    },
                }
            }},
            iconClasses: {type: Object, default: function() {
                return {
                    "filter": "ti-filter",
                    "list": "ti-list",
                    "leftArrow": "ti-angle-double-left",
                    "rightArrow": "ti-angle-double-right"
                }
            }}
        },
        data: function() {
            return {
                mode: 'loading',
                elements: {},
                columns: {},
                fields: {},
                model: {},
                filters: {},
                currentEditUrl: '',
                currentStoreUrl: '',
                currentUpdateUrl: '',
                currentDeleteUrl: '',
                currentSubjectName: '',
                fetchTimeout: -1,
                watches: {},
                currentPage: 1,
                counts: {},
            }
        },
        mounted() {
            this.fetchElements();
        },
        computed: {
            pageOptions: function() {
                let result = [];
                if (typeof(this.counts.pagesMax) != 'undefined') {
                    for (var i = 1; i <= this.counts.pagesMax; i++) {
                        result.push(i);
                    }
                }

                return result;
            },
            totalLabel: function() {
                if (typeof(this.counts.filtered) == 'undefined') {
                    return this.translate('Results');
                }

                return this.translate('Results')+'&nbsp;('+this.counts.filtered+')';
            }
        },
        methods: {
            translate: function(string) {
                if (typeof(this.$root.translate) != 'undefined') {
                    return this.$root.translate(string);
                }

                return string;
            },
            showButton: function(button) {
                return this.buttons.hasOwnProperty(button)
                    && (this.userIsAdmin || !this.buttons[button]['adminNeeded']);
            },
            getFilterTimeoutByType: function(type) {
                if (type == 'datepicker') {
                    return 1;
                }
                if (type == 'text') {
                    return 300;
                }

                return 1;
            },
            getFilterData: function() {
                let result = {
                    token: Math.random().toString(36),
                    page: this.currentPage,
                    items_per_page: this.itemsPerPage,
                };
                for (var filterName in this.filters) {
                    if (this.filters.hasOwnProperty(filterName)) {
                        result[filterName] = this.filters[filterName]['value'];
                    }
                }

                return result;
            },
            loadFilters: function(filters) {
                this.filters = filters;
                for (var filterName in this.filters) {
                    if (this.filters.hasOwnProperty(filterName)) {
                        this.watches[filterName] = this.$watch(
                            'filters.'+filterName+'.value',
                            (newValue, oldValue) => {
                                if (newValue != oldValue) {
                                    window.clearTimeout(this.fetchTimeout);
                                    this.fetchTimeout = window.setTimeout(() => {
                                        this.currentPage = 1;
                                        this.fetchElements(true);
                                    }, this.getFilterTimeoutByType(this.filters[filterName].type));
                                }
                            }, {deep: true});
                    }
                }

            },
            fetchElements: function(onlyElements) {
                if (typeof(onlyElements) == 'undefined') {
                    onlyElements = false;
                }
                if (!onlyElements) {
                    this.mode = 'loading';
                }
                window.axios.get(this.indexUrl, {params: this.getFilterData()})
                    .then((response) => {
                        this.elements = response.data.elements;
                        this.counts = response.data.counts;
                        if (!onlyElements) {
                            this.columns = response.data.columns;
                            if (JSON.stringify(this.filters) == '{}') {
                                this.loadFilters(response.data.filters);
                            }
                        }
                        this.mode = 'list';
                    });
            },
            showDetails: function(elementId) {
                this.mode = 'loading';
                window.axios.get(
                    this.replaceIdParameterWithElementIdInUrl(this.detailsUrl, elementId),
                    {params: {token: Math.random().toString(36)}}
                )
                    .then((response) => {
                        this.fields = response.data.fields;
                        this.model = response.data.model;
                        this.mode = 'details';
                    });
            },
            editElement: function(elementId) {
                this.mode = 'loading';
                this.currentEditUrl = this.replaceIdParameterWithElementIdInUrl(this.editUrl, elementId);
                this.currentUpdateUrl = this.replaceIdParameterWithElementIdInUrl(this.updateUrl, elementId);
                this.mode = 'edit';
            },
            confirmElementDeletion: function(elementId, elementName) {
                this.currentDeleteUrl = this.replaceIdParameterWithElementIdInUrl(this.deleteUrl, elementId);
                this.currentSubjectName = elementName;
                this.mode = 'delete-confirmation';
            },
            deleteElement: function() {
                window.axios.delete(this.currentDeleteUrl)
                    .then((response) => {
                        this.fetchElements();
                    });
            },
            createElement: function() {
                this.mode = 'create';
            },
            replaceIdParameterWithElementIdInUrl: function(url, elementId) {
                return url.replace('___id___', elementId);
            },
            nextPage: function() {
                if (this.currentPage < this.counts.pagesMax) {
                    this.currentPage++;
                }
            },
            previousPage: function() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },
            resetFilters: function() {
                for (var filter in this.filters) {
                    if (this.filters.hasOwnProperty(filter)) {
                        Vue.set(this.filters[filter], 'value', this.filters[filter].default);
                    }
                }
            }
        },
        watch: {
            currentPage: function() {
                this.fetchElements(true);
            }
        }
    }
</script>
