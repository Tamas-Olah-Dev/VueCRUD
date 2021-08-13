<template>
    <div :class="getCSSClass('model-manager-container')">
        <div :class="getCSSClass('model-manager-header-container')">
            <div :class="getCSSClass('model-manager-title')" v-html="title"></div>
            <div :class="getCSSClass('model-manager-main-buttons')">
                <component v-if="subComponents.headerButtonsComponent != ''"
                           :is="subComponents.headerButtonsComponent"
                           :buttons="mainButtons"
                           v-on:new="createElement"
                           v-on:component="handleComponentChangeMessage($event)"
                ></component>

            </div>
        </div>
        <div :class="getCSSClass('model-manager-filters-container')">
            <component v-if="subComponents.filterComponent != ''"
                       :is="subComponents.filterComponent"
                       :value="filters"
                       :buttons="mainButtons"
                       v-on:input="resetPageAndReload"
                       v-on:component="handleComponentChangeMessage($event)"
            ></component>
        </div>
        <div :class="getCSSClass('model-manager-list-container')">
            <div :class="getCSSClass('model-manager-list-header')">
                <div :class="getCSSClass('model-manager-list-header-title')"
                     v-html="totalLabel"
                ></div>
                <div :class="getCSSClass('model-manager-list-highlight-input')">
                    <input type="text" v-model="inlineSearchText">
                </div>
            </div>
            <div v-if="paginationType == 'top'" :class="getCSSClass('model-manager-list-pagination-top-container')">
                <template v-if="JSON.stringify(counts) != '{}'">
                    <span :class="getCSSClass('model-manager-items-count-label')">
                        {{ counts['start'] }}&nbsp;-&nbsp;{{ counts['end'] }}&nbsp;/&nbsp;{{ counts['filtered'] }}
                    </span>
                    <button :class="getCSSClass('model-manager-pagination-prev-page-button')"
                            v-on:click="previousPage"
                    ></button>
                    <select :class="getCSSClass('model-manager-pagination-dropdown')"
                            v-model="currentPage"
                    >
                        <option v-for="p in pageOptions"
                                v-bind:value="p"
                                v-html="p"
                        ></option>
                    </select>
                    <button :class="getCSSClass('model-manager-pagination-next-page-button')"
                            v-on:click="nextPage"
                    ></button>
                    <span :class="getCSSClass('model-manager-pagination-items-per-page-drowdown')">
                        <select v-model="itemsPerPage">
                            <option v-for="option in paginationItemsPerPageOptions"
                                    :value="option"
                                    v-html="option"
                            ></option>
                        </select>
                        <span>
                            {{ translate('Items/page') }}
                        </span>

                    </span>
                </template>

            </div>
            <div :class="getCSSClass('model-manager-list-body')">
                <component  :is="subComponents.listComponent"
                            :mode="mode"
                            :id-property="idProperty"
                            :operations-url="ajaxOperationsUrl"
                            :columns="columns"
                            :elements="elements"
                            :inline-search-text="inlineSearchText"
                            :sorting-columns="sortingColumns"
                            v-on:update="handleListMessage($event)"
                            v-on:operation="handleListMessage($event)"
                            :context-menu-component="subComponents.contextMenuComponent"
                            :clearCurrentElementFlag="modelManagerCurrentElementFlag"
                ></component>
            </div>
            <div v-if="paginationType == 'bottom'" :class="getCSSClass('model-manager-list-pagination-top-container')">
                <template v-if="JSON.stringify(counts) != '{}'">
                    <span :class="getCSSClass('model-manager-items-count-label')">
                        {{ counts['start'] }}&nbsp;-&nbsp;{{ counts['end'] }}&nbsp;/&nbsp;{{ counts['filtered'] }}
                    </span>

                    <button :class="getCSSClass('model-manager-pagination-prev-page-button')"
                            v-on:click="previousPage"
                    ></button>
                    <template v-if="pageOptions.length < 6">
                        <button v-for="p in pageOptions"
                                :class="getCSSClass('model-manager-pagination-page-button')"
                                v-html="p"
                                @click="currentPage = p"></button>
                    </template>
                    <template v-if="pageOptions.length > 6">
                        <button v-for="p in firstTwoPageOptions"
                                :class="getCSSClass('model-manager-pagination-page-button')"
                                v-html="p"
                                @click="currentPage = p"></button>
                        <span>...</span>
                        <select :class="getCSSClass('model-manager-pagination-dropdown')"
                                v-model="currentPage"
                        >
                            <option v-for="p in pageOptions"
                                    v-bind:value="p"
                                    v-html="p"
                            ></option>
                        </select>
                        <span>...</span>
                        <button v-for="p in lastTwoPageOptions"
                                :class="getCSSClass('model-manager-pagination-page-button')"
                                v-html="p"
                                @click="currentPage = p"></button>
                    </template>

                    <button :class="getCSSClass('model-manager-pagination-next-page-button')"
                            v-on:click="nextPage"
                    ></button>
                    <span :class="getCSSClass('model-manager-pagination-items-per-page-drowdown')">
                        <select v-model="itemsPerPage">
                            <option v-for="option in paginationItemsPerPageOptions"
                                    :value="option"
                                    v-html="option"
                            ></option>
                        </select>
                        <span>
                            {{ translate('Items/page') }}
                        </span>

                    </span>
                </template>
            </div>
        </div>
        <popup :visible="showPopup" :show-close-button="false">
            <component v-if="mode == 'edit'"
                       :is="subComponents.editFormComponent"
                       v-bind:data-url="currentEditUrl"
                       v-bind:save-url="currentUpdateUrl"
                       v-bind:ajax-operations-url="currentAjaxOperationsUrl"
                       v-on:submit-success="confirmEditSuccess"
                       v-on:editing-canceled="hidePopup()"
                       redirect-to-response-on-success="false"
                       v-bind:buttons="mainButtons"
            ></component>
            <component v-if="mode == 'create'"
                       :is="subComponents.editFormComponent"
                       v-bind:data-url="createUrlWithFilters"
                       v-bind:save-url="storeUrl"
                       v-bind:ajax-operations-url="ajaxOperationsUrl"
                       v-on:submit-success="confirmCreationSuccess"
                       v-on:editing-canceled="hidePopup()"
                       redirect-to-response-on-success="false"
                       v-bind:buttons="mainButtons"
            ></component>
            <component v-if="mode == 'delete'"
                    :is="subComponents.deleteConfirmationComponent"
                    :buttons="mainButtons"
                    :label="currentDeleteLabel"
                    v-on:confirmed="deleteElement"
                    v-on:canceled="hidePopup"
            ></component>
        </popup>
        <notification :message="notificationMessage"
                      :display="notificationDisplayFlag"
                      :color-class="notificationMessageClass"
                      :timeout="notificationTimeout"
                      :button-class="getCSSClass('model-manager-notification-button')"
                      :close-button-label="'OK'"
        ></notification>
    </div>
</template>

<script>
    export default {
        inject: ['loadingIndicator', 'getCSSClass', 'translate', 'icon'],
        props: {
            idProperty: {type: String, default: 'id'},
            indexUrl: {type: String},
            detailsUrl: {type: String},
            createUrl: {type: String},
            editUrl: {type: String},
            storeUrl: {type: String},
            updateUrl: {type: String},
            deleteUrl: {type: String},
            ajaxOperationsUrl: {type: String},
        },
        data: function () {
            return {
                title: '',
                mode: 'loading',
                columns: {},
                elements: [],
                initialLoading: true,
                title: '',
                counts: {},
                sortingColumns: {},
                currentPage: 1,
                currentSortingColumn: null,
                currentSortingDirection: 'asc',
                massOperations: {},
                exportOperations: {},
                buttons: {},
                positionedView: false,
                mainButtons: {},
                mode: 'loading',
                fetchMode: 'list',
                showAllInOnePage: false,
                urlParameters: {},
                itemsPerPage: 0,
                inlineSearchText: '',
                showPopup: false,
                currentEditUrl: '',
                currentStoreUrl: '',
                currentUpdateUrl: '',
                currentDeleteUrl: '',
                currentAjaxOperationsUrl: '',
                currentSubjectName: '',
                currentSubjectId: null,
                modelManagerCurrentElementFlag: 0,
                currentDeleteLabel: '',
                notificationMessage: '',
                notificationDisplayFlag: 0,
                notificationTimeout: 3000,
                notificationMessageClass: '',
                subComponents: {
                    headerButtonsComponent: 'ModelManagerHeaderButtonsComponent',
                    editFormComponent: 'EditForm',
                    filterComponent: 'ModelManagerFilterComponent',
                    contextMenuComponent: '',
                    listComponent: 'ModelManagerList',
                    deleteConfirmationComponent: 'ModelManagerDeleteConfirmationForm'
                },
                paginationType: 'top', //"none", "top", "bottom"
                paginationItemsPerPageOptions: [20,50,100],
                paginationItemsPerPageDefault: 20,
                filters: {},
            }
        },
        created() {
            if (window.vueCRUDCustomizations) {
                Object.keys(this.subComponents).forEach((subComponent) => {
                    if ((window.vueCRUDCustomizations) && (window.vueCRUDCustomizations[subComponent])) {
                        this.subComponents[subComponent] = window.vueCRUDCustomizations[subComponent];
                    }
                });
                Object.keys(window.vueCRUDCustomizations).forEach((o) => {
                    if (this[o]) {
                        this[o] = window.vueCRUDCustomizations[o];
                    }
                });
            }

        },
        provide: function() {
            return {
                mainButtons: this.mainButtons
            }
        },
        mounted() {
            if (this.paginationType == 'none') {
                this.itemsPerPage = 999999999;
            } else {
                this.itemsPerPage = this.paginationItemsPerPageOptions.includes(this.paginationItemsPerPageDefault)
                    ? this.paginationItemsPerPageDefault
                    : this.paginationItemsPerPageOptions[0];
            }
            this.fetchElements();
        },
        methods: {
            handleComponentChangeMessage: function(payload) {

            },
            errorNotification: function(error) {
                this.notificationTimeout = 0;
                this.notificationMessage = error;
                this.notificationMessageClass = this.getCSSClass('model-manager-notification-error');
                this.notificationDisplayFlag = Math.random();
            },
            successNotification: function(message) {
                this.notificationTimeout = 4000;
                this.notificationMessage = message;
                this.notificationMessageClass = this.getCSSClass('model-manager-notification-success');
                this.notificationDisplayFlag = Math.random();
            },
            deleteElement: function() {
                window.axios.delete(this.currentDeleteUrl)
                    .then((response) => {
                        this.confirmDeletionSuccess();
                    }).catch((response) => {
                    this.errorNotification(response);
                })
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
            handleListMessage: function(payload) {
                if (payload.operation == 'sort') {
                    this.currentSortingColumn = payload.currentSortingColumn;
                    this.currentSortingDirection = payload.currentSortingDirection;
                    this.resetPageAndReload();
                }
                if (payload.operation == 'edit') {
                    this.currentSubjectId = payload.element[this.idProperty];
                    this.editElement(this.currentSubjectId, payload.elementIndex);
                }
                if (payload.operation == 'delete') {
                    this.currentSubjectId = payload.element[this.idProperty];
                    this.currentDeleteUrl = this.replaceIdParameterWithElementIdInUrl(this.deleteUrl, this.currentSubjectId);
                    this.showDeleteConfirmation(this.currentSubjectId);
                }
            },
            showDeleteConfirmation: function() {
                this.currentAjaxOperationsUrl = this.replaceIdParameterWithElementIdInUrl(this.ajaxOperationsUrl, this.currentSubjectId);
                window.axios.post(this.currentAjaxOperationsUrl, {action: 'requestDeleteLabel', subject: 'id'}).then((response) => {
                    this.currentDeleteLabel = response.data;
                    this.mode = 'delete';
                    this.showPopup = true;
                });
            },
            getFilterData: function() {
                let result = {
                    token: Math.random().toString(36),
                    page: this.currentPage,
                    items_per_page: this.itemsPerPage,
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
            fetchElements: function(onlyElements = false, suppressLoading = false) {
                // if (!this.autoFilter) {
                //     this.restoreFilterState();
                // }
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
                        this.buttons = response.data.buttons;
                        if (this.positionedView != response.data.positionedView) {
                            this.columns = response.data.columns;
                        }
                        if (!onlyElements) {
                            this.mainButtons = response.data.mainButtons;
                            this.columns = response.data.columns;
                            if (JSON.stringify(this.filters) == '{}') {
                                this.filters = response.data.filters;
                                //this.loadFilters(response.data.filters);
                            }
                        }
                        this.positionedView = response.data.positionedView;
                        Object.keys(this.subComponents).forEach((subComponent) => {
                            if (response.data[subComponent]) {
                                this.subComponents[subComponent] = response.data[subComponent];
                            }
                        });
                        let possibleOverrides = [
                            'massOperations',
                            'exportOperations',
                        ]
                        possibleOverrides.forEach((o) => {
                            if (response.data[o]) {
                                this[o] = response.data[o];
                            }

                        })
                        this.mode = 'list';
                        this.fetchMode = 'search';
                    });
            },
            resetPageAndReload: function() {
                if (this.currentPage == 1) {
                    this.fetchElements(true);
                } else {
                    this.currentPage = 1;
                }
            },
            editElement: function(elementId, elementIndex) {
                this.currentElementIndex = elementIndex;
                this.currentSubjectId = elementId;
                this.currentEditUrl = this.replaceIdParameterWithElementIdInUrl(this.editUrl, elementId);
                this.currentUpdateUrl = this.replaceIdParameterWithElementIdInUrl(this.updateUrl, elementId);
                this.currentAjaxOperationsUrl = this.replaceIdParameterWithElementIdInUrl(this.ajaxOperationsUrl, elementId);
                this.showPopup = true;
                this.mode = 'edit';
            },
            createElement: function() {
                this.currentElementIndex = 0;
                this.currentSubjectId = null;
                this.showPopup = true;
                this.mode = 'create';
            },
            replaceIdParameterWithElementIdInUrl: function(url, elementId) {
                return url.replace('___id___', elementId);
            },
            hidePopup: function() {
                this.mode = 'list';
                this.modelManagerCurrentElementFlag = Math.random();
                this.showPopup = false;
            },
            confirmEditSuccess: function() {
                this.successNotification(this.translate('Changes saved successfully'));
                this.fetchElements(true);
                this.hidePopup();
            },
            confirmCreationSuccess: function() {
                this.successNotification(this.translate('Changes saved successfully'));
                this.fetchElements(true);
                this.hidePopup();
            },
            confirmDeletionSuccess: function() {
                this.successNotification(this.translate('Item deleted successfully'));
                this.fetchElements(true);
                this.hidePopup();
            }
        },
        computed: {
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
            firstTwoPageOptions: function() {
                if (this.pageOptions.length > 1) {
                    return [this.pageOptions[0], this.pageOptions[1]];
                }
                return [];
            },
            lastTwoPageOptions: function() {
                if (this.pageOptions.length > 1) {
                    return [this.pageOptions[this.pageOptions.length - 2], this.pageOptions[this.pageOptions.length - 1]];
                }
                return [];
            },
            loadingIndicator: function() {
                return this.loadingIndicatorSrc;
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
            createUrlWithFilters: function() {
                let currentUrl = new URL(this.createUrl);
                Object.keys(this.filters).forEach((key) => {
                    currentUrl.searchParams.set(key, this.filters[key].value);
                })
                return currentUrl.toString();
            }
        },
        watch: {
            currentPage: function() {
                if (!this.initialLoading) {
                    this.fetchElements(true);
                }
            },
            itemsPerPage: function() {
                this.resetPageAndReload();
            }
        }
    }
</script>
