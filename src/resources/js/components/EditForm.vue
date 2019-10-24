<template><template>
    <div class="full-width-div container-fluid">
        <div class="row full-width-div model-manager-container">
            <div class="col-12 full-width-div">
                <div v-if="mode == 'loading'" v-html="spinnerSrc" style="width:100%; display:flex; justify-content: center"></div>
                <div v-if="JSON.stringify(mainButtons) != '{}' && (mode == 'list' || mode == 'elements-loading')" class="row">
                    <div class="full-width-div col-12"
                         style="margin-bottom: 25px; padding: 0px; display: flex; justify-content: space-between; align-items: center"
                    >
                        <div style="font-size: 1.8em; font-weight: bold" v-if="title != ''"
                             v-html="title"
                             v-bind:class="getClassOverrideOrDefaultClass('model-manager-title', 'model-manager-title')"
                        ></div>
                        <button v-bind:class="mainButtons['add']['class']"
                                v-on:click="createElement"
                                v-html="mainButtons['add']['html']"
                                v-if="allowAdding"
                        ></button>
                    </div>
                    <div v-if="filtersExist"
                         class="full-width-div model-manager-filter-container portlet"
                         v-bind:class="getClassOverrideOrDefaultClass('model-manager-filter-box', 'model-manager-filter-box')"
                    >
                        <div class="portlet-heading"
                             style="display: flex; justify-content: space-between; align-items:baseline"
                             v-bind:class="getClassOverrideOrDefaultClass('model-manager-filters-heading', 'bg-inverse')"
                        >
                            <div>
                                <span v-bind:class="iconClasses.filter"></span>
                                {{ translate('Filters') }}
                            </div>
                            <button v-bind:class="mainButtons['resetFilters']['class']"
                                    v-on:click="resetFilters"
                                    v-html="mainButtons['resetFilters']['html']"
                            ></button>
                        </div>
                        <div class="portlet-body model-manager-filters-list-container"
                             v-bind:class="getClassOverrideOrDefaultClass('model-manager-filters-body', 'model-manager-filters-body')"
                        >
                            <tabgroup :tabs="filterTabs" v-on:tab-changed="resetFilters">
                                <template v-for="tabFilters, index in filterTabContents"
                                          v-slot:[index]
                                >
                                    <div class="row model-manager-filters-list"
                                         style="display: flex; justify-content: start"
                                    >
                                        <div v-for="filterData, filterName in tabFilters"
                                             class="form-group m-1 model-manager-filter-block"
                                             v-bind:class="filterData['containerClass']"
                                        >
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
                                                <option v-for="data in filterData['valueset']"
                                                        v-bind:value="data.value"
                                                        v-html="data.label"
                                                ></option>
                                            </select>
                                            <treeselect v-if="filterData['type'] == 'treeselect'"
                                                        v-bind="filterData['props']"
                                                        v-model="filterData['value']">
                                            </treeselect>
                                            <component v-if="filterData['type'] == 'custom-component'"
                                                       :is="filterData['component']"
                                                       v-bind="filterData['props']"
                                                       v-model="filterData['value']">
                                            </component>
                                        </div>
                                    </div>
                                    <div v-if="!autoFilter" class="row p-1" style="min-width: 100%; display: flex; justify-content: start">
                                        <button style="width: 33%"
                                                v-bind:class="mainButtons['search']['class']"
                                                v-html="mainButtons['search']['html']"
                                                v-on:click="saveFilterState(); currentPage = 1; fetchMode = 'search'; fetchElements(true)"
                                        ></button>
                                    </div>

                                </template>
                            </tabgroup>
                        </div>
                    </div>
                    <div class="portlet full-width-div">
                        <div class="portlet-heading"
                             style="display: flex; justify-content: space-between"
                             v-bind:class="getClassOverrideOrDefaultClass('model-manager-main-heading', 'bg-primary')"
                        >
                            <div class="portlet-title">
                                <span v-bind:class="iconClasses.list"></span>
                                <span v-html="totalLabel"></span>
                            </div>
                            <div class="model-manager-paging-controls"
                                 v-if="typeof(counts['total']) != 'undefined'"
                            >
                                <span v-if="pageOptions.length > 1 || showAllInOnePage"
                                      style="flex-basis: 60%;"
                                >
                                    <label v-if="false">
                                        <input type="checkbox" v-model="showAllInOnePage">
                                        {{ translate('Single page') }}
                                    </label>
                                </span>
                                <template v-if="JSON.stringify(counts) != '{}'">
                                    <span>{{ counts['start'] }}&nbsp;-&nbsp;{{ counts['end'] }}&nbsp;/&nbsp;{{ counts['filtered'] }}&nbsp;&nbsp;</span>
                                    <button v-bind:class="mainButtons['prevPage']['class']"
                                            v-on:click="previousPage"
                                            v-html="mainButtons['prevPage']['html']"
                                            style="height: 2.3em; margin-right: 3px;"
                                    ></button>
                                    <select class="form-control model-manager-page-select"
                                            v-model="currentPage"
                                            style="max-width: 5em; height: 2.3em;"
                                    >
                                        <option v-for="p in pageOptions"
                                                v-bind:value="p"
                                                v-html="p"
                                        ></option>
                                    </select>
                                    <button v-bind:class="mainButtons['nextPage']['class']"
                                            v-on:click="nextPage"
                                            v-html="mainButtons['nextPage']['html']"
                                            style="height: 2.3em; margin-left: 3px;"
                                    ></button>
                                    <span style="margin-left: 1em; white-space: nowrap; display: flex; align-items: center;">
                                        <select class="form-control"
                                                style="max-width: 5em; height: 2.3em; margin-right: 3px"
                                                v-model="itemsPerPage">
                                            <option v-for="option in itemsPerPageOptions"
                                                    :value="option"
                                                    v-html="option"
                                            ></option>
                                        </select>
                                        {{ translate('Items/page') }}
                                    </span>
                                </template>
                            </div>
                        </div>
                        <div class="portlet-body"
                             v-bind:class="getClassOverrideOrDefaultClass('model-manager-main-body', 'model-manager-main-body')"
                        >
                            <div v-show="mode == 'elements-loading'" v-html="spinnerSrc" style="width:100%; display:flex; justify-content: center"></div>
                            <template v-show="mode != 'elements-loading'">
                                <template v-if="showMassControls">
                                    <div style="width: 100%; display: flex; justify-content: start; margin-bottom: 1em;">
                                        <dropdown-button :main-button-class="mainButtons['massOperations']['class']"
                                                         :items="massOperationsForDropdown"
                                                         @clicked="handleMassOperation($event)"
                                                         :disabled="selectedElements.length == 0 || massOperationLoading">
                                            <span v-if="massOperationLoading" v-html="spinnerSrc"></span>&nbsp;
                                            {{ mainButtons['massOperations']['html'] + (selectedElements.length > 0 ? '&nbsp;('+selectedElements.length+')' : '') }}
                                        </dropdown-button>
                                    </div>
                                </template>
                                <table v-show="mode != 'elements-loading'" class="table table-striped" v-bind:class="elementTableClass">
                                    <thead>
                                    <tr v-bind:class="getClassOverrideOrDefaultClass('model-manager-table-head', '')">
                                        <th v-if="showMassControls">
                                                <span v-html="'✔'"
                                                      :title="translate('Select/deselect all')"
                                                      v-on:click="toggleSelectAll"
                                                      v-on:keydown.enter="toggleSelectAll"
                                                      v-on:keydown.space="toggleSelectAll"
                                                      role="button"
                                                      style="cursor:pointer; font-size:1.7em; user-select: none"></span>
                                        </th>
                                        <th v-for="columnName, columnField in columns"
                                            v-bind:class="{'sorting-column': columnIsSorting(columnField)}"
                                            v-on:click="setSorting(columnField)"
                                            v-on:keydown.enter="setSorting(columnField)"
                                            v-on:keydown.space="setSorting(columnField)"
                                            role="button"
                                        >
                                            <span v-html="columnName"></span>
                                            <span style="margin-left: 3px"
                                                  v-if="columnIsSorting(columnField)"
                                                  v-bind:style="{color: currentSortingColumn == columnField ? 'black': 'darkgrey'}"
                                                  v-html="currentSortingColumn == columnField ? sortingChevron : '⇵'"
                                            ></span>
                                        </th>
                                        <th style="min-width: 15%" v-if="allowOperations">{{ translate('Operations') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="element, elementIndex in elements"
                                        :key="elementIndex"
                                        :ref="elementIndex"
                                        v-bind:style="elementRowStyle(element)"
                                        v-bind:class="element.hasOwnProperty('row_class') ? element.row_class : null"
                                    >
                                        <td v-if="showMassControls" style="vertical-align: middle"
                                        >
                                            <label :for="element.id" style="margin:0px; padding:0px">
                                                <span v-html="selectedElements.indexOf(element.id) > -1 ? '▣' : '▢'"
                                                      style="cursor:pointer; font-size:1.7em; user-select: none"></span>
                                                <input type="checkbox"
                                                       :value="element.id"
                                                       :id="element.id"
                                                       :name="element.id"
                                                       v-model="selectedElements"
                                                       style="opacity: 0; height:0px; width: 0px;"
                                                >
                                            </label>
                                        </td>
                                        <td v-for="columnName, columnField in columns">
                                            <component v-if="typeof(element[columnField]) == 'string' && element[columnField].substr(0, 11) == 'component::'"
                                                       :is="JSON.parse(element[columnField].substr(11)).component"
                                                       v-bind="JSON.parse(element[columnField].substr(11)).componentProps"></component>
                                            <span v-else v-html="element[columnField]"></span>
                                        </td>
                                        <td v-if="allowOperations" style="white-space: nowrap; text-align: right">
                                            <button type="button" v-if="showButton('details')"
                                                    v-bind:class="buttons['details']['class']"
                                                    v-on:click="showDetails(element[idProperty], elementIndex)"
                                                    v-html="buttons['details']['html']"
                                                    :title="buttons['details']['title'] || ''"
                                            ></button>
                                            <button type="button"
                                                    v-if="showButton('edit') && (typeof(element['vuecrud_edit_allowed']) == 'undefined' || element['vuecrud_edit_allowed'] == true)"
                                                    v-bind:class="buttons['edit']['class']"
                                                    v-on:click="editElement(element[idProperty], elementIndex)"
                                                    v-html="buttons['edit']['html']"
                                                    :title="buttons['edit']['title'] || ''"
                                            ></button>
                                            <button type="button"
                                                    v-if="showButton('delete') && (typeof(element['vuecrud_delete_allowed']) == 'undefined' || element['vuecrud_delete_allowed'] == true)"
                                                    v-bind:class="buttons['delete']['class']"
                                                    v-on:click="confirmElementDeletion(element[idProperty], element[nameProperty], elementIndex)"
                                                    v-html="buttons['delete']['html']"
                                                    :title="buttons['delete']['title'] || ''"
                                            ></button>
                                            <button type="button" v-if="showButton('moveUp') && elementIndex > 0"
                                                    v-bind:class="buttons['moveUp']['class']"
                                                    v-on:click="moveElementUp(element[idProperty])"
                                                    v-html="buttons['moveUp']['html']"
                                                    :title="buttons['moveUp']['title'] || ''"
                                            ></button>
                                            <button type="button" v-if="showButton('moveDown') && elementIndex < elements.length - 1"
                                                    v-bind:class="buttons['moveDown']['class']"
                                                    v-on:click="moveElementDown(element[idProperty])"
                                                    v-html="buttons['moveDown']['html']"
                                                    :title="buttons['moveDown']['title'] || ''"
                                            ></button>
                                            <ajax-button v-for="ajaxButton, ajaxButtonKey in ajaxButtons"
                                                         v-bind:class="ajaxButton['class']"
                                                         v-bind:subject="element"
                                                         :key="element[idProperty]+'-'+ajaxButton['props']['action']"
                                                         v-bind="ajaxButton['props']"
                                                         v-bind:title="ajaxButton['title'] || ''"
                                                         v-on:submit-failed="alert($event);returnToList()"
                                                         v-on:submit-success="confirmEditSuccess($event)"
                                                         v-html="ajaxButton['html']"
                                            ></ajax-button>
                                            <button type="button" v-for="customComponentButton, customComponentButtonKey in customComponentButtons"
                                                    v-bind:class="customComponentButton['class']"
                                                    v-on:click="activateCustomComponent(customComponentButtonKey)"
                                                    v-html="customComponentButton['html']"
                                                    v-on:component-canceled="returnToList"
                                                    v-on:submit-success="confirmEditSuccess($event)"
                                                    :title="customComponentButton['title'] || ''"
                                            ></button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <template v-if="showExportControls">
                                    <div style="width: 100%; display: flex; justify-content: start; margin-bottom: 1em;">
                                        <dropdown-button :main-button-class="mainButtons['exportOperations']['class']"
                                                         :items="exportOperations"
                                                         @clicked="handleExportOperation($event)"
                                                         :disabled="elements.length == 0">
                                            {{ mainButtons['exportOperations']['html'] }}
                                        </dropdown-button>
                                        <div style="margin-left: 10px">
                                            <label>
                                                <input type="checkbox" v-model="exportAll">
                                                <span>{{ translate('Export all, not just search results') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
                <div  v-if="mode == 'details-component'">
                    <component :is="buttons['details']['component']"
                               v-bind:subject-id="currentSubjectId"
                               v-bind="buttons['details']['componentProps']"
                               v-on:component-canceled="mode = 'list'"
                    >
                    </component>
                </div>
                <div  v-if="mode == 'details'">
                    <div class="row full-width-div">
                        <button type="button" class="float-right"
                                v-bind:class="mainButtons['backToList']['class']"
                                v-on:click="mode = 'list'"
                                v-html="mainButtons['backToList']['html']"
                        ></button>
                    </div>
                    <div class="row full-width-div">
                        <dl>
                            <template v-for="fieldName, fieldProperty in fields">
                                <dt v-html="fieldName"></dt>
                                <dd v-html="model[fieldProperty]"></dd>
                            </template>
                        </dl>
                    </div>
                    <div class="row full-width-div">
                        <div class="col full-width-div"
                             v-if="model.hasOwnProperty('additional_details_rendered')"
                             v-html="model['additional_details_rendered']"
                        ></div>
                    </div>
                </div>
                <div  v-if="mode == 'edit'">
                    <div class="portlet full-width-div"
                         v-bind:class="getClassOverrideOrDefaultClass('model-manager-edit-window', 'model-manager-edit-window')"
                         v-if="typeof(buttons['edit']['component']) == 'undefined'"
                    >
                        <div class="portlet-heading"
                             style="display:flex; justify-content: space-between; align-items: baseline"
                             v-bind:class="getClassOverrideOrDefaultClass('model-manager-edit-window-heading', 'bg-primary')"
                        >
                            {{ translate('Edit element') }}
                            <button v-on:click="returnToList"
                                    v-bind:class="mainButtons['backToList']['class']"
                            >X</button>
                        </div>
                        <div class="portlet-body">
                            <edit-form
                                v-bind:data-url="currentEditUrl"
                                v-bind:save-url="currentUpdateUrl"
                                v-bind:ajax-operations-url="currentAjaxOperationsUrl"
                                v-on:submit-success="confirmEditSuccess"
                                v-on:editing-canceled="returnToList"
                                redirect-to-response-on-success="false"
                                v-bind:buttons="mainButtons"
                                v-bind:class-overrides="classOverrides"
                            ></edit-form>
                        </div>
                    </div>
                    <component
                        v-if="typeof(buttons['edit']['component']) != 'undefined'"
                        :is="buttons['edit']['component']"
                        :subject-id="currentSubjectId"
                        v-bind="buttons['edit']['props']"
                        v-on:submit-success="confirmEditSuccess"
                        v-on:editing-canceled="returnToList"
                    >
                    </component>
                </div>
                <div  v-if="mode == 'create'">
                    <div class="portlet full-width-div"
                         v-bind:class="getClassOverrideOrDefaultClass('model-manager-create-window', 'model-manager-create-window')"
                         v-if="typeof(mainButtons['add']['component']) == 'undefined'"
                    >
                        <div class="portlet-heading"
                             style="display:flex; justify-content: space-between; align-items: baseline"
                             v-bind:class="getClassOverrideOrDefaultClass('model-manager-create-window-heading', 'bg-primary')"
                        >
                            {{ translate('Add element') }}
                            <button v-on:click="returnToList"
                                    v-bind:class="mainButtons['backToList']['class']"
                            >X</button>
                        </div>
                        <div class="portlet-body">
                            <edit-form
                                v-bind:data-url="createUrl"
                                v-bind:save-url="storeUrl"
                                v-bind:ajax-operations-url="ajaxOperationsUrl"
                                v-on:submit-success="confirmCreationSuccess"
                                v-on:editing-canceled="returnToList"
                                redirect-to-response-on-success="false"
                                v-bind:buttons="mainButtons"
                                v-bind:class-overrides="classOverrides"
                            ></edit-form>
                        </div>
                    </div>
                    <component
                        v-if="typeof(mainButtons['add']['component']) != 'undefined'"
                        :is="mainButtons['add']['component']"
                        subject-id="-1"
                        v-bind="mainButtons['add']['props']"
                        v-on:submit-success="confirmCreationSuccess"
                        v-on:editing-canceled="returnToList"
                    >
                    </component>
                </div>
                <div v-if="mode == 'delete-confirmation'">
                    <div class="alert alert-danger">{{ translate('Are you sure you want to delete this element') }}? <br><span v-html="currentSubjectName"></span></div>
                    <div style="display: flex; justify-content: space-between">
                        <button type="button"
                                v-bind:class="mainButtons['confirmDeletion']['class']"
                                v-on:click="deleteElement"
                                v-html="translate('Yes')"
                        ></button>
                        <button type="button"
                                v-bind:class="mainButtons['cancelDeletion']['class']"
                                v-on:click="returnToList"
                                v-html="translate('Cancel')"
                        ></button>
                    </div>
                </div>
                <div  v-if="mode == 'custom-component'">
                    <component
                        v-bind:is="activeCustomComponent.componentName"
                        v-bind="activeCustomComponent.props"
                        v-bind:selected-elements="selectedElements"
                        v-on:submit-success="closeCustomComponent"
                        v-on:component-canceled="returnToList"
                    ></component>
                </div>

            </div>
        </div>
        <div class="model-manager-notification"
             v-bind:class="{'model-manager-notification-show': showNotification}"
        >
            <div v-html="notificationContent" v-bind:class="modelManagerContentClass"></div>
            <button style="height: 100%"
                    class="btn"
                    v-on:click="showNotification = false"
            >OK</button>
        </div>
    </div>
</template>

<script>
    import {translateMixin} from './mixins/translateMixin.js'
    import {spinner} from './mixins/spinner.js'
    import {classOverridesMixin} from './mixins/classOverridesMixin.js'
    export default {
        mixins: [translateMixin, spinner, classOverridesMixin],
        props: {
            indexUrl: {type: String, required: true},
            detailsUrl: {type: String, required: true},
            createUrl: {type: String, required: true},
            editUrl: {type: String, required: true},
            storeUrl: {type: String, required: true},
            updateUrl: {type: String, required: true},
            deleteUrl: {type: String, required: true},
            ajaxOperationsUrl: {type: String, required: true},
            allowOperations: {type: Boolean, default: true},
            allowAdding: {type: Boolean, default: true},
            autoFilter: {type: Boolean, default: false},
            nameProperty: {type: String, default: 'name'},
            idProperty: {type: String, default: 'id'},
            iconClasses: {type: Object, default: function() {
                    return {
                        "filter": "ti-filter",
                        "list": "ti-list",
                        "leftArrow": "ti-angle-double-left",
                        "rightArrow": "ti-angle-double-right"
                    }
                }},
            subjectName: {type: String, default: () => {return this.translate('Item')}},
            useSweetAlert: {type: Boolean, default: false},
            defaultFilters: {default: () => {return {}}},
            itemsPerPageOptions: {type: Array, default: () => {return [20, 50, 100]}},
            itemsPerPageDefault: {type: Number, default: 20}
        },
        data: function() {
            return {
                exportAll: false,
                itemsPerPage: 20,
                mode: 'loading',
                elements: {},
                columns: {},
                sortingColumns: {},
                currentSortingColumn: null,
                currentSortingDirection: 'asc',
                fields: {},
                model: {},
                filters: {},
                currentEditUrl: '',
                currentStoreUrl: '',
                currentUpdateUrl: '',
                currentDeleteUrl: '',
                currentAjaxOperationsUrl: '',
                currentSubjectName: '',
                currentSubjectId: null,
                fetchTimeout: -1,
                watches: {},
                currentPage: 1,
                counts: {},
                disablePageWatch: false,
                activeCustomComponent: {},
                positionedView: false,
                buttons: {},
                elementTableClass: '',
                title: '',
                mainButtons: {},
                showNotification: false,
                notificationContent: '',
                notificationType: '',
                selectedElements: [],
                massOperations: {},
                exportOperations: {},
                showAllInOnePage: false,
                initialLoading: true,
                urlParameters: {},
                fetchMode: 'list',
                massOperationLoading: false,
                currentElementIndex: -1,
            }
        },
        mounted() {
            this.disablePageWatch = true;
            this.itemsPerPage = this.itemsPerPageDefault;
            this.disablePageWatch = false;
            let urlparts = window.location.href.split('?');
            let keyvalue = [];
            if (urlparts.length > 1) {
                let queryparts = urlparts[1].split('&');
                for (let i = 0; i < queryparts.length; i++) {
                    keyvalue = queryparts[i].split('=');
                    this.urlParameters[keyvalue[0]] = keyvalue[1];
                }
                if (Object.keys(this.urlParameters).length > 0) {
                    window.localStorage.removeItem(this.indexUrl+'_filters');
                }
            }
            this.loadFilters(this.defaultFilters);
            for (let key in this.urlParameters) {
                if (this.urlParameters.hasOwnProperty(key)) {
                    if (typeof(this.filters[key]) != 'undefined') {
                        Vue.set(this.filters[key], 'value', this.urlParameters[key]);
                    }
                }
            }

            this.fetchMode = 'initial';
            this.fetchElements();
        },
        computed: {
            filtersExist: function() {
                return !['{}', '[]'].includes(JSON.stringify(this.filters));
            },
            massOperationsForDropdown: function() {
                let result = {};
                for (let key in this.massOperations) {
                    if (this.massOperations.hasOwnProperty(key)) {
                        result[key] = this.massOperations[key].label;
                    }
                }
                return result;
            },
            showMassControls: function() {
                return JSON.stringify(this.massOperations) != '{}';
            },
            showExportControls: function() {
                return JSON.stringify(this.exportOperations) != '{}';
            },
            modelManagerContentClass: function() {
                if (this.notificationType == 'error') {
                    return 'text-danger';
                }

                return null;
            },
            pageOptions: function() {
                let result = [];
                if (JSON.stringify(this.counts) != '{}') {
                    if (typeof(this.counts.pagesMax) != 'undefined') {
                        for (var i = 1; i <= this.counts.pagesMax; i++) {
                            result.push(i);
                        }
                    }
                }

                return result;
            },
            totalLabel: function() {
                if (this.mode != 'loading') {
                    if (typeof(this.counts.filtered) == 'undefined') {
                        return this.translate('Results');
                    }
                    if (this.counts.filtered == this.counts.total) {
                        return this.translate('Results')+'&nbsp;('+this.counts.filtered+')';
                    } else {
                        return this.translate('Results')+'&nbsp;('+this.counts.filtered+' / '+this.counts.total+')';
                    }
                }
            },
            sortingChevron: function() {
                return this.currentSortingDirection == 'asc'
                    ? '⬆'
                    : '⬇';
            },
            customComponentButtons: function() {
                let result = {};
                for (var i in this.buttons) {
                    if ((this.buttons.hasOwnProperty(i)) && (typeof(this.buttons[i].componentName) != 'undefined')) {
                        if (this.buttons[i].componentName != 'ajax-button') {
                            result[i] = this.buttons[i];
                        }
                    }
                }

                return result;
            },
            ajaxButtons: function() {
                let result = {};
                for (var i in this.buttons) {
                    if ((this.buttons.hasOwnProperty(i)) && (typeof(this.buttons[i].componentName) != 'undefined')) {
                        if (this.buttons[i].componentName == 'ajax-button') {
                            result[i] = this.buttons[i];
                        }
                    }
                }

                return result;
            },
            filterTabs: function() {
                if (!this.filtersExist) {
                    return [];
                }
                let result = [];
                for (let key in this.filters) {
                    if (this.filters.hasOwnProperty(key)) {
                        if (!result.includes(this.filters[key].tab)) {
                            result.push(this.filters[key].tab);
                        }
                    }
                }
                return result;
            },
            filterTabContents: function() {
                let result = {};
                for (let key in this.filters) {
                    if (this.filters.hasOwnProperty(key)) {
                        if (!result.hasOwnProperty(this.filters[key].tab)) {
                            result[this.filters[key].tab] = {};
                        }
                        result[this.filters[key].tab][key] = this.filters[key];
                    }
                }
                let resultArray = [];
                for (let key in result) {
                    if (result.hasOwnProperty(key)) {
                        resultArray.push(result[key]);
                    }
                }
                return resultArray;
            }
        },
        methods: {
            returnToList: function() {
                this.mode = 'list';
                this.scrollCurrentElementIntoView();
            },
            scrollCurrentElementIntoView: function() {
                if (typeof(this.$refs[this.currentElementIndex]) != 'undefined') {
                    this.$nextTick(() => this.$refs[this.currentElementIndex][0].scrollIntoView());
                }
            },
            elementRowStyle: function(element) {
                let result ={
                    //'border-left': this.selectedElements.indexOf(element.id) > -1 ? '6px solid #CAE1F6' : null
                }
                if (element.hasOwnProperty('row_background_color')) {
                    result['background-color'] = element.row_background_color;
                }
                if (element.hasOwnProperty('row_color')) {
                    result['color'] = element.row_color;
                }

                return result;
            },
            confirmEditSuccess: function(payload) {
                if ((typeof(payload) == 'undefined') || (typeof(payload) == 'object')) {
                    this.successNotification(this.subjectName + ' ' + this.translate('updated successfully'));
                } else {
                    this.successNotification(payload);
                }
                this.fetchMode = 'update';
                this.fetchElements();
            },
            confirmCreationSuccess: function(payload) {
                if ((typeof(payload) == 'undefined') || (typeof(payload) == 'object')) {
                    this.successNotification(this.subjectName + ' ' + this.translate('created successfully'));
                } else {
                    this.successNotification(payload);
                }
                this.fetchMode = 'update';
                this.fetchElements();
            },
            confirmDeletionSuccess: function(payload) {
                if (typeof(payload) == 'undefined') {
                    this.successNotification(this.subjectName + ' ' + this.translate('deleted'));
                } else {
                    this.successNotification(payload);
                }
                this.fetchMode = 'update';
                this.fetchElements();
            },
            successNotification: function(content) {
                if (this.useSweetAlert) {
                    window.Swal.fire({
                        text: content,
                        type: 'success',
                        timer: 3000
                    });
                } else {
                    this.notificationContent = content;
                    this.notificationType = 'success',
                        this.showNotification = true;
                    window.setTimeout(() => {
                        this.showNotification = false;
                    }, 3000);
                }
            },
            errorNotification: function(content) {
                if (this.useSweetAlert) {
                    window.Swal.fire({
                        text: content,
                        type: 'error'
                    });
                } else {
                    this.notificationContent = content;
                    this.notificationType = 'error',
                        this.showNotification = true;
                }
            },

            saveFilterState: function() {
                let filterState = {};
                for (var filterName in this.filters) {
                    if (this.filters.hasOwnProperty(filterName)) {
                        filterState[filterName] = typeof(this.filters[filterName]['value']) == 'undefined' ? null : this.filters[filterName]['value'];
                    }
                }
                window.localStorage.setItem(this.indexUrl+'_filters', JSON.stringify(filterState));
            },
            restoreFilterState: function() {
                let filterState = JSON.parse(window.localStorage.getItem(this.indexUrl+'_filters'));
                if (filterState !== null) {
                    for (var filterName in this.filters) {
                        if (filterState.hasOwnProperty(filterName)) {
                            Vue.set(this.filters[filterName], 'value', filterState[filterName]);
                        }
                    }
                } else {
                    if (Object.keys(this.urlParameters).length == 0) {
                        for (var filter in this.filters) {
                            if (this.filters.hasOwnProperty(filter)) {
                                Vue.set(this.filters[filter], 'value', this.filters[filter].default);
                            }
                        }
                    }
                }
            },
            columnIsSorting: function(columnField) {
                return typeof(this.sortingColumns[columnField]) != 'undefined';
            },
            setSorting: function(field) {
                if (this.columnIsSorting(field)) {
                    if (this.currentSortingColumn == field) {
                        if (this.currentSortingDirection == 'asc') {
                            this.currentSortingDirection = 'desc'
                        } else {
                            this.currentSortingDirection = 'asc';
                        }
                    } else {
                        this.currentSortingColumn = field;
                        this.currentSortingDirection = 'asc';
                    }
                    this.disablePageWatch = true;
                    this.currentPage = 1;
                    this.disablePageWatch = false;
                    this.fetchMode = 'sorting';
                    this.fetchElements(true);
                }
            },
            showButton: function(button) {
                return this.buttons.hasOwnProperty(button);
            },
            activateCustomComponent: function(key) {
                this.activeCustomComponent = this.customComponentButtons[key];
                this.mode = 'custom-component';
            },
            getFilterTimeoutByType: function(type) {
                if (type == 'datepicker') {
                    return 1;
                }
                if (type == 'text') {
                    return 500;
                }

                return 1;
            },
            getFilterData: function() {
                let result = {
                    token: Math.random().toString(36),
                    page: this.currentPage,
                    items_per_page: this.showAllInOnePage ? 99999999 : this.itemsPerPage,
                    sorting_field: this.sortingColumns[this.currentSortingColumn],
                    sorting_direction: this.currentSortingDirection,
                    fetchMode: this.fetchMode,
                };
                for (var filterName in this.filters) {
                    if (this.filters.hasOwnProperty(filterName)) {
                        result[filterName] = this.filters[filterName]['value'];
                    }
                }
                if (this.initialLoading) {
                    for (let key in this.urlParameters) {
                        if (this.urlParameters.hasOwnProperty(key)) {
                            result[key] = this.urlParameters[key];
                        }
                    }
                }

                return result;
            },
            loadFilters: function(filters) {
                this.filters = {...filters};
                if (this.autoFilter) {
                    for (var filterName in this.filters) {
                        if (this.filters.hasOwnProperty(filterName)) {
                            this.watches[filterName] = this.$watch(
                                'filters.'+filterName+'.value',
                                (newValue, oldValue) => {
                                    if (newValue != oldValue) {
                                        window.clearTimeout(this.fetchTimeout);
                                        this.fetchTimeout = window.setTimeout(() => {
                                            this.disablePageWatch = true;
                                            this.currentPage = 1;
                                            this.disablePageWatch = false;
                                            this.fetchMode = 'search';
                                            this.fetchElements(true);
                                        }, this.getFilterTimeoutByType(this.filters[filterName].type));
                                    }
                                }, {deep: true});
                        }
                    }
                } else {
                    this.restoreFilterState();
                }
            },
            findSortingColumnKey: function(column) {
                for (var i in this.sortingColumns) {
                    if (this.sortingColumns.hasOwnProperty(i)) {
                        if (this.sortingColumns[i] == column) {
                            return i;
                        }
                    }
                }

                return null;
            },
            fetchElements: function(onlyElements, suppressLoading) {
                if (typeof(onlyElements) == 'undefined') {
                    onlyElements = false;
                }
                if (typeof(suppressLoading) == 'undefined') {
                    suppressLoading = false;
                }
                if (!this.autoFilter) {
                    this.restoreFilterState();
                }
                if (!suppressLoading) {
                    if (!onlyElements) {
                        this.mode = 'loading';
                    } else {
                        this.mode = 'elements-loading';
                    }
                }
                let filterData = this.getFilterData();
                this.initialLoading = false;
                window.axios.get(this.indexUrl, {params: filterData})
                    .then((response) => {
                        this.title = response.data.title;
                        this.elements = response.data.elements;
                        this.counts = response.data.counts;
                        document.getElementsByTagName('title')[0].innerHTML = response.data.pageTitle;
                        this.sortingColumns = response.data.sortingColumns;
                        this.currentSortingColumn = this.findSortingColumnKey(response.data.sortingField);
                        this.currentSortingDirection = response.data.sortingDirection;
                        this.massOperations = response.data.massOperations;
                        this.exportOperations = response.data.exportOperations;
                        this.buttons = response.data.buttons;
                        if (this.positionedView != response.data.positionedView) {
                            this.columns = response.data.columns;
                        }
                        if (!onlyElements) {
                            this.mainButtons = response.data.mainButtons;
                            this.columns = response.data.columns;
                            if (JSON.stringify(this.filters) == '{}') {
                                this.loadFilters(response.data.filters);
                            }
                        }
                        this.positionedView = response.data.positionedView;
                        this.mode = 'list';
                        this.fetchMode = 'search';
                    });
            },
            showDetails: function(elementId, elementIndex) {
                this.currentElementIndex = elementIndex;
                if ((typeof(this.buttons['details']['isComponent']) == 'undefined')
                    || (this.buttons['details']['isComponent'] == false))
                {
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
                } else {
                    this.currentSubjectId = elementId;
                    this.mode="details-component";
                }
            },
            editElement: function(elementId, elementIndex) {
                this.currentElementIndex = elementIndex;
                this.currentSubjectId = elementId;
                this.mode = 'loading';
                this.currentEditUrl = this.replaceIdParameterWithElementIdInUrl(this.editUrl, elementId);
                this.currentUpdateUrl = this.replaceIdParameterWithElementIdInUrl(this.updateUrl, elementId);
                this.currentAjaxOperationsUrl = this.replaceIdParameterWithElementIdInUrl(this.ajaxOperationsUrl, elementId);
                this.mode = 'edit';
            },
            confirmElementDeletion: function(elementId, elementName, elementIndex) {
                this.currentElementIndex = elementIndex;
                this.currentDeleteUrl = this.replaceIdParameterWithElementIdInUrl(this.deleteUrl, elementId);
                this.currentSubjectName = elementName;
                this.mode = 'delete-confirmation';
            },
            deleteElement: function() {
                window.axios.delete(this.currentDeleteUrl)
                    .then((response) => {
                        this.confirmDeletionSuccess();
                    }).catch((response) => {
                    this.errorNotification(response);
                })
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
                this.fetchMode = 'search';
                this.fetchElements(true)
            },
            moveElementUp: function(id) {
                this.elementTableClass = 'element-table-muted';
                window.axios.post(this.ajaxOperationsUrl, {id: id, action: 'move', direction: -1})
                    .then((response) => {
                        this.fetchMode = 'update';
                        this.fetchElements(true, true);
                        this.elementTableClass = '';
                    }).catch((error) => {this.elementTableClass = '';});
            },
            moveElementDown: function(id) {
                this.elementTableClass = 'element-table-muted';
                window.axios.post(this.ajaxOperationsUrl, {id: id, action: 'move', direction: 1})
                    .then((response) => {
                        this.fetchMode = 'update';
                        this.fetchElements(true, true);
                        this.elementTableClass = '';
                    }).catch((error) => {this.elementTableClass = '';});
            },
            handleMassOperation: function(action) {
                if (this.massOperations[action].type == 'method') {
                    let proceed = this.massOperations[action].confirm == null
                        ? true
                        : window.confirm(this.massOperations[action].confirm);
                    if (proceed) {
                        this.massOperationLoading = true;
                        window.axios.post(this.ajaxOperationsUrl, {
                            selectedElements: this.selectedElements,
                            action: action
                        }).then((response) => {
                            this.successNotification(response.data)
                            this.fetchMode = 'update';
                            this.selectedElements = [];
                            this.fetchElements(true, true);
                            this.elementTableClass = '';
                            this.massOperationLoading = false;
                        }).catch((error) => {
                            if (typeof(error.response.data.exception) != 'undefined') {
                                this.errorNotification(error.response.data.message);
                            } else {
                                this.errorNotification(error.response.data);
                            }
                            this.elementTableClass = '';
                            this.massOperationLoading = false;
                        });
                    }
                }
                if (this.massOperations[action].type == 'component') {
                    this.activeCustomComponent = {
                        componentName: this.massOperations[action].component,
                        props: {...this.massOperations[action].componentProps},
                    }
                    this.activeCustomComponent['props']['selectedElements'] = this.selectedElements
                    this.mode = 'custom-component';
                }
            },
            handleExportOperation: function(action) {
                let selectedElements = this.selectedElements.length > 0
                    ? this.selectedElements
                    : this.elements.map((item) => {return item.id});
                let filterData = this.exportAll ? {} : this.getFilterData();
                filterData['exportIds'] = selectedElements;
                filterData['action'] = action;
                filterData['sorting_field'] = this.sortingColumns[this.currentSortingColumn];
                filterData['sorting_direction'] = this.currentSortingDirection;
                window.axios.post(this.ajaxOperationsUrl, filterData, {responseType: 'blob'}).then((response) => {
                    var blob = new Blob([response.data], { type: response.headers['Content-Type'] });
                    var filename = response.headers['filename'];
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }).catch((error) => {
                    this.errorNotification(error.response.data);
                });
            },
            toggleSelectAll: function() {
                if (this.selectedElements.length > 0) {
                    this.selectedElements = [];
                } else {
                    this.selectedElements = []
                    for (let i = 0; i < this.elements.length; i++) {
                        this.selectedElements.push(this.elements[i].id);
                    }
                }
            },
            closeCustomComponent: function() {
                this.selectedElements = [];
                this.fetchMode = 'update';
                this.fetchElements();
            }
        },
        watch: {
            itemsPerPage: function() {
                if (!this.disablePageWatch) {
                    this.fetchMode = 'pagination';
                    this.fetchElements(true);
                }
            },
            currentPage: function() {
                if (!this.disablePageWatch) {
                    this.fetchMode = 'pagination';
                    this.fetchElements(true);
                }
            },
            showAllInOnePage: function() {
                this.currentPage = 1;
                this.fetchElements(true);
            }
        }
    }
</script>
<style>
    .full-width-div {
        width: 100%
    }
    .sorting-column {
        white-space: nowrap;
        cursor:pointer
    }
    .element-table-muted {
        opacity: .7;
    }
    .model-manager-notification {
        position: fixed;
        bottom: 6vh;
        right: 6em;
        min-width: 0%;
        max-width: 0%;
        z-index: 500;
        opacity: 0;
        padding: 0px;
        transition: opacity 300ms ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: white;
        box-shadow: 8px 8px rgba(6,6,6,.3);
        height: 0px;
    }

    .model-manager-notification > button {
        flex-basis: 10%;
        margin-left: 10px;
    }
    .model-manager-notification-show {
        opacity: 1;
        height:auto;
        padding: 1.5em;
        max-width: 60%;
        min-width: 20%;
    }
    .model-manager-paging-controls {
        display: flex;
        flex-basis: 60%;
        align-items:center;
        justify-content: flex-end;
    }
    .model-manager-page-select {
        width: 5em;
    }
</style>

    <div class="container-fluid vue-editform-container"
         style="position:relative"
         v-bind:class="getClassOverrideOrDefaultClass('edit-form-container', 'edit-form-container')"
    >
        <div v-if="!loaded" v-html="spinnerSrc" style="width:100%; display:flex; justify-content: center"></div>
        <div v-if="loaded && (typeof(formTitle != 'undefined'))"
             v-bind:class="getClassOverrideOrDefaultClass('edit-form-form-title-container', 'edit-form-form-title-container')"
        ><h4 v-html="formTitle"></h4></div>
        <div v-if="formDisabled" class="disabled-overlay"></div>
        <form role="form" class="margin-b-20"  v-on:submit.prevent="submitForm"  v-if="loaded"
              v-bind:class="getClassOverrideOrDefaultClass('edit-form-form', 'edit-form-form')"
        >
            <div v-for="step in stepsToRender"
                 v-bind:class="getClassOverrideOrDefaultClass('edit-form-step', 'edit-form-step')"
            >
                <div v-if="typeof(config.stepLabels[step]) != 'undefined'"
                     class="form-step-header"
                     v-bind:class="formHeadClass(step)"
                     v-html="config.stepLabels[step]"></div>
                <div class="w-full row" style="position:relative; display:flex; flex-wrap: wrap"
                     v-bind:class="getClassOverrideOrDefaultClass('edit-form-step-body', 'edit-form-step-body')"
                >
                    <div v-if="currentStep != step" class="disabled-overlay"></div>
                    <div v-for="data, fieldname in subjectDataForStep(step)"
                         v-bind:style="{height: typeof(data.customOptions['cssHeight']) == 'undefined' ? 'auto' : data.customOptions['cssHeight']}"
                         v-bind:class="data.containerClass">
                        <template v-if="!shouldHideField(fieldname)">
                            <label v-if="data.label != null">
                                {{ data.label }}
                                <span v-if="data.mandatory"> *</span>
                                <span class="edit-form-label-tooltip" v-if="typeof(data.helpTooltip) != 'undefined'" v-html="data.helpTooltip"></span>
                                <span v-if="errorExists(fieldname)" class="text-danger validation-error-label-message" v-html="errors[fieldname][0]"></span>
                            </label>
                            <input v-if="data.kind == 'input' && data.type != 'password'  && data.type != 'number'"
                                   v-model="subjectData[fieldname].value"
                                   v-bind:placeholder="subjectData[fieldname].placeholder"
                                   v-bind:class="data.class"
                                   v-bind:list="Object.keys(subjectData[fieldname].valueset).length > 0 ? fieldname+'-lookup' : null"
                                   type="text"
                            >
                            <datalist v-if="data.kind == 'input' && data.type != 'password'  && data.type != 'number' && Object.keys(subjectData[fieldname].valueset).length > 0"
                                      v-bind:id="fieldname+'-lookup'"
                            >
                                <option v-for="valuesetvalue, valuesetitem in data.valueset"
                                        v-bind:value="valuesetvalue"
                                ></option>
                            </datalist>
                            <input v-if="data.kind == 'input' && data.type == 'number'"
                                   v-model="subjectData[fieldname].value"
                                   type="number"
                                   v-bind:placeholder="subjectData[fieldname].placeholder"
                                   v-bind:class="data.class"
                                   v-bind:min="numberFieldMin(fieldname)"
                                   v-bind:max="numberFieldMax(fieldname)"
                                   v-bind:step="numberFieldStep(fieldname)"
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
                                   v-bind:placeholder="subjectData[fieldname].placeholder"
                                   v-bind:class="data.class"
                                   autocomplete="off"
                                   type="password"
                            >
                            <number-field v-if="data.kind == 'numberfield'"
                                          editable="true"
                                          input-class="form-control col-12 w-full"
                                          show-currency-label="true"
                                          container-class="col-12 w-full"
                                          v-model="subjectData[fieldname].value"
                                          v-bind="JSON.parse(data.props)"
                            ></number-field>

                            <textarea v-if="data.kind == 'text' && data.type == 'simple'"
                                      v-model="subjectData[fieldname].value"
                                      v-bind:placeholder="subjectData[fieldname].placeholder"
                                      v-bind:class="data.class"
                            ></textarea>
                            <div v-if="data.kind == 'text' && data.type == 'static'"
                            >
                                <div v-html="subjectData[fieldname].staticValue"></div>
                            </div>
                            <div v-if="data.kind == 'text' && data.type == 'richtext-trix'" v-bind:class="data.class" style="min-height:95%; height:95%; margin-bottom: 2em">
                                <trix-wrapper v-model="subjectData[fieldname].value"
                                              v-bind:fieldname="fieldname"
                                              v-bind="JSON.parse(data.props)"
                                              v-bind:ajax-operations-url="ajaxOperationsUrl"
                                ></trix-wrapper>
                            </div>
                            <select v-if="data.kind == 'select' && (data.type == null || data.type == 'yesno' || data.type == 'custom')"
                                    v-model="subjectData[fieldname].value"
                                    v-bind:style="subjectData[fieldname].value == -1 ? {'color': 'lightgray'} : {}"
                                    v-bind:class="data.class"
                            >
                                <option v-for="valuesetvalue, valuesetitem in data.valuesetSorted"
                                        v-bind:value="valuesetvalue"
                                        v-bind:style="valuesetvalue == -1 ? {'color': 'lightgray'} : {'color': 'black'}"
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
                                        v-bind:class-overrides="classOverrides"
                            ></datepicker>
                            <image-picker v-if="data.kind == 'imagepicker'"
                                          v-bind="JSON.parse(data.props)"
                                          v-model="subjectData[fieldname].value"
                                          v-bind:upload-url="ajaxOperationsUrl"
                                          v-bind:class-overrides="classOverrides"
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
                                          v-bind:class-overrides="classOverrides"
                            ></multi-select>
                            <treeselect v-if="data.kind == 'vue-treeselect'"
                                        v-bind="JSON.parse(data.props)"
                                        :options="data.valuesetSorted"
                                        v-model="subjectData[fieldname].value"
                                        v-bind:class-overrides="classOverrides"
                            ></treeselect>
                            <component v-if="data.kind == 'custom-component'"
                                       v-bind:is="data.type"
                                       v-bind="JSON.parse(data.props)"
                                       v-model="subjectData[fieldname].value"
                                       v-bind:errors="componentError(fieldname)"
                                       :buttons="buttons"
                                       v-bind:class-overrides="classOverrides"
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
                            <recaptcha-component v-if="data.kind == 'recaptcha'"
                                                 :g-key="JSON.parse(data.props).key"
                                                 v-model="subjectData[fieldname].value">

                            </recaptcha-component>
                        </template>
                    </div>
                </div>
            </div>
        </form>
        <div class="row w-full" v-if="false">
            <div class="alert alert-danger col col-12 w-full"
                 v-for="error in errors" v-html="error[0]"></div>
        </div>
        <div class="row w-full" v-if="resultMessage != ''">
            <div class="alert col col-12 w-full"
                 v-bind:class="resultMessageClass"
                 v-html="resultMessage"></div>
        </div>
        <div class="row w-full" v-if="!formDisabled"
             v-bind:class="getClassOverrideOrDefaultClass('edit-form-form-buttons-container', 'edit-form-form-buttons-container')"
        >
            <div v-bind:class="getClassOverrideOrDefaultClass('edit-form-form-button-container', 'col')"
            >
                <button type="button"
                        v-bind:class="buttons['save']['class']"
                        v-on:click="submitForm"
                        v-bind:disabled="loading"
                >
                    <span v-if="loading" class="button-loading-indicator" v-html="spinnerSrc"></span>
                    <span v-if="currentStep != lastStep" v-html="buttons['proceed']['html']"></span>
                    <span v-if="currentStep == lastStep" v-html="buttons['save']['html']"></span>
                </button>
            </div>
            <div v-bind:class="getClassOverrideOrDefaultClass('edit-form-form-button-container', 'col')"
            >
                <button type="button"
                        v-bind:class="buttons['cancel']['class']"
                        v-on:click="cancelEditing"
                        v-html="buttons['cancel']['html']"
                ></button>
            </div>
        </div>
    </div>
</template>

<script>
    import {translateMixin} from './mixins/translateMixin.js'
    import {spinner} from './mixins/spinner.js'
    import {classOverridesMixin} from './mixins/classOverridesMixin.js'
    export default {
        mixins: [translateMixin, spinner, classOverridesMixin],
        props: {
            dataUrl: {type: String},
            saveUrl: {type: String},
            ajaxOperationsUrl: {type: String, default: ''},
            successCallback: {type: String},
            formTitle: {type: String},
            showResponseMessage: {type: Boolean, default: false},
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
                resultMessageClass: '',
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
                        if (this.subjectData[key].type == 'text') {
                            result[key] = this.subjectData[key].value == null
                                ? ''
                                : this.subjectData[key].value.toString();
                        } else {
                            result[key] = this.subjectData[key].value;
                        }
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
            formHeadClass: function(step) {
                if (this.currentStep == step) {
                    return this.getClassOverrideOrDefaultClass('edit-form-step-head', 'edit-form-step-head')
                } else {
                    return this.getClassOverrideOrDefaultClass('edit-form-step-head-inactive', 'edit-form-step-head-inactive')
                }
            },
            numberFieldMin: function(fieldname) {
                if ((typeof(this.subjectData[fieldname]) != 'undefined')
                    && (typeof(this.subjectData[fieldname].customOptions['min']) != 'undefined')) {
                    return this.subjectData[fieldname].customOptions['min'];
                }
                return false;
            },
            numberFieldMax: function(fieldname) {
                if ((typeof(this.subjectData[fieldname]) != 'undefined')
                    && (typeof(this.subjectData[fieldname].customOptions['max']) != 'undefined')) {
                    return this.subjectData[fieldname].customOptions['max'];
                }
                return false;
            },
            numberFieldStep: function(fieldname) {
                if ((typeof(this.subjectData[fieldname]) != 'undefined')
                    && (typeof(this.subjectData[fieldname].customOptions['step']) != 'undefined')) {
                    return this.subjectData[fieldname].customOptions['step'];
                }
                return false;
            },
            shouldHideField: function(fieldname) {
                let result = false;
                let conditions = this.subjectData[fieldname].hideIf;
                for (let i = 0; i < conditions.length; i++) {
                    let firstValue = this.literalOrVariableSubjectdataValue(conditions[i][0]);
                    let secondValue = this.literalOrVariableSubjectdataValue(conditions[i][2]);
                    if (((conditions[i][1] == '=') && (firstValue == secondValue))
                        || ((conditions[i][1] == '!=') && (firstValue != secondValue))
                        || ((conditions[i][1] == '<') && (firstValue < secondValue))
                        || ((conditions[i][1] == '>') && (firstValue > secondValue))) {
                        result = true;
                    }
                }

                return result;
            },
            literalOrVariableSubjectdataValue: function(value) {
                if (value === null) {
                    return value;
                }
                if ((value.toString().substr(0, 1) == '$')
                    && (typeof(this.subjectData[value.toString().substr(1)]) != 'undefined'))
                {
                    return this.subjectData[value.toString().substr(1)].value;
                }

                return value;
            },
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
                this.loading = true;
                this.errors = {};
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
                            this.resultMessage = this.showResponseMessage
                                ? response.data
                                : this.translate('Változások elmentve');
                            this.resultMessageClass = 'alert-success';
                            setTimeout(() => {this.resultMessage = ''}, 3000);
                        }
                        this.loading = false;
                    })
                    .catch((error) => {
                        if (error.response.status == 422) {
                            this.errors = error.response.data.errors;
                        }
                        if (error.response.status != 422) {
                            if (error.response.status == 500) {
                                alert(error.response.data.message);
                            }
                            if (error.response.status == 419) {
                                this.resultMessage = error.response.data.message;
                                this.resultMessageClass = 'alert-danger';
                                setTimeout(() => {this.resultMessage = ''}, 6000);
                            }
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
        z-index: 100;
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
