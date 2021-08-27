<template>
    <div :class="getCSSClass('model-manager-list-table-container')">
        <table :class="getCSSClass('model-manager-list-table', 'table table-striped')">
            <thead>
            <tr :class="getCSSClass('model-manager-table-head-row', '')">
                <th v-if="showMassControls">
                    <!-- '✔' -->
                    <span :class="getCSSClass('model-manager-select-all-table-head')"
                          :title="translate('Select/deselect all')"
                          v-on:click="toggleSelectAll"
                          v-on:keydown.enter="toggleSelectAll"
                          v-on:keydown.space="toggleSelectAll"
                          role="button"
                          style="cursor:pointer; user-select: none"
                          v-html="icon('checkmark')"
                    ></span>

                </th>
                <th v-for="columnName, columnField in columns"
                    v-bind:class="tableHeadClass(columnField)"
                    v-on:click="setSorting(columnField)"
                    v-on:keydown.enter="setSorting(columnField)"
                    v-on:keydown.space="setSorting(columnField)"
                    role="button"
                >
                    <span v-html="columnName"></span>
                    <span style="margin-left: .25rem"
                          :class="getCSSClass('model-manager-sorting-icon')"
                          v-html="icon('sorting')"
                          v-if="columnIsSorting(columnField) && currentSortingColumn != columnField"
                    ></span>
                    <span style="margin-left: .25rem"
                          :class="getCSSClass('model-manager-sorting-icon')"
                          v-html="icon('sorting-'+currentSortingDirection)"
                          v-if="currentSortingColumn == columnField"
                    ></span>
                </th>
                <th :class="getCSSClass('model-manager-table-head-operations')" v-if="allowOperations">{{ translate('Operations') }}</th>
            </tr>
            </thead>
            <tbody :class="loadingClass" class="model-manager-table-body">
            <tr v-for="element, elementIndex in elements"
                :key="elementIndex"
                :ref="elementIndex"
                v-bind:style="elementRowStyle(element)"
                v-bind:class="elementRowClass(element)"
                @contextmenu="showContextMenu($event, elementIndex)"
            >
                <td v-if="showMassControls" style="vertical-align: middle"
                >
                    <label :for="element.id" style="margin:0px; padding:0px">
                        <span class="model-manager-row-checkbox"
                              :class="selectedElements.includes(element[idProperty]) ? getCSSClass('model-manager-row-checkbox-selected') : ''"
                              style="cursor:pointer; user-select: none"></span>
                        <input type="checkbox"
                               :value="element[idProperty]"
                               :id="element[idProperty]"
                               :name="element[idProperty]"
                               v-model="selectedElements"
                               style="opacity: 0; height:0px; width: 0px;"
                        >
                    </label>
                </td>
                <td v-for="columnName, columnField in columns"
                    v-bind:class="'model-manager-list-'+columnField+'-table-cell'"
                    v-bind:style="elementCellStyle(element, columnField)"
                >
                    <component
                            v-if="typeof(element[columnField]) == 'string' && element[columnField].substr(0, 11) == 'component::'"
                            :is="JSON.parse(element[columnField].substr(11)).component"
                            v-bind:subject="element"
                            :key="element[idProperty]+'-'+element[columnField]"
                            v-bind="JSON.parse(element[columnField].substr(11)).componentProps"></component>
                    <span v-else
                          v-html="element[columnField]"
                          v-bind:class="{'model-manager-highlighted-table-cell': fieldContainsInlineSearchText(element[columnField])}"
                    ></span>
                </td>
                <td v-if="Object.keys(availableOperations).length > 0 || contextMenuComponent != ''"
                    :class="getCSSClass('model-manager-operations-table-cell')"
                >
                    <button type="button" v-if="contextMenuComponent != ''"
                            :class="getCSSClass('model-manager-dropdown-menu-button')"
                            @click="showContextMenu($event, elementIndex)"></button>

                    <template v-for="operationData, operationFunction in availableOperations">
                        <button type="button" v-if="operationData.type == 'button' && showButton(operationFunction, element)"
                                :class="getCSSClass('model-manager-'+operationFunction+'-button')"
                                v-on:click="emitOperation(operationFunction, element, elementIndex)"
                                v-html="operationData['label']"
                                :title="operationData['title'] || ''"
                        ></button>
                        <component v-if="operationData.type == 'component' && showButton(operationFunction, element)"
                                   :is="operationData.component"
                                   :class="getCSSClass('model-manager-'+operationFunction+'-button')"
                                   v-bind:subject="element"
                                   v-bind="operationData['props']"
                                   v-bind:title="operationData['title'] || ''"
                                   v-html="operationData['html']"
                                   :key="element[idProperty]+'-'+operationData['props']['action']"
                        ></component>
                    </template>
                </td>
            </tr>
            </tbody>
        </table>
        <template v-if="contextMenuComponent != ''">
            <component :is="contextMenuComponent"
                       v-bind="contextMenuComponentProps"
                       v-bind:show="showContextMenuComponent"
                       v-bind:cursor-position="currentCursorPosition"
                       v-bind:subject="currentElement"
                       v-on:hide="hideContextMenu"
            ></component>
        </template>
    </div>
</template>

<script>
    export default {
        inject: ['loadingIndicator', 'getCSSClass', 'translate', 'icon'],
        props: {
            idProperty: {type: String, default: 'id'},
            operationsUrl: {type: String},
            massOperations: {
                type: Object, default: () => {
                    return {}
                }
            },
            columns: {type: Object, default: () => {return {}}},
            elements: {type: Array, default: () => {return []}},
            sortingColumns: {type: Object, default: () => {return {}}},
            availableOperations: {type: Object, default: () => {return {
                edit: {
                    type: 'button',
                    label: 'E',
                    title: 'Edit',
                    class: ''
                },
                delete: {
                    type: 'button',
                    label: 'D',
                    title: 'Delete',
                    class: ''
                },
            }}},
            contextMenuComponent: {type: String, default: ''},
            contextMenuComponentProps: {type: Object, default: () => {return {}}},
            inlineSearchText: {type: String, default: ''},
            mode: {type: String},
            clearCurrentElementFlag: {type: Number, default: 0}
        },
        data: function () {
            return {
                currentSortingColumn: null,
                currentSortingDirection: 'asc',
                selectedElements: [],
                currentElement: {},
                currentCursorPosition: {x:0, y:0},
                showContextMenuComponent: false,
            }
        },
        mounted() {
        },
        methods: {
            hideContextMenu: function() {
                this.showContextMenuComponent = false;
                this.currentElement = {};
            },
            showContextMenu: function(event, elementIndex) {
                if (this.contextMenuComponent != '') {
                    this.currentElement = this.elements[elementIndex];
                    this.showContextMenuComponent = false;
                    this.$nextTick(() => {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                        this.currentCursorPosition.x = event.clientX;
                        this.currentCursorPosition.y = event.clientY;
                        this.showContextMenuComponent = true;
                    });
                }
            },
            tableHeadClass: function(field) {
                let sortingClass = this.columnIsSorting(field) ? ' model-manager-table-head-sorting ' : '';
                let activeSortingClass = this.currentSortingColumn == field
                    ? ' model-manager-table-head-sorting-active model-manager-table-head-sorting-active-'+this.currentSortingDirection+' '
                    : ''
                return this.getCSSClass('model-manager-table-head')
                    +' model-manager-table-head-'+field
                    +sortingClass
                    +activeSortingClass;
            },
            columnIsSorting: function(columnField) {
                return this.sortingColumns[columnField];
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
                    this.emitUpdateRequest('sort');
                }
            },
            emitUpdateRequest: function(operation = 'update') {
                this.$emit('update', this.viewData({operation: operation}));
            },
            emitOperation: function(operation, element, elementIndex) {
                this.currentElement = element;
                this.$emit('operation', {operation: operation, element: element, elementIndex: elementIndex});
            },
            toggleSelectAll: function() {
                if (this.selectedElements.length > 0) {
                    this.selectedElements = [];
                } else {
                    this.selectedElements = []
                    for (let i = 0; i < this.elements.length; i++) {
                        this.selectedElements.push(this.elements[i][this.idProperty]);
                    }
                }
            },
            elementRowClass: function(element) {
                let base = this.selectedElements.includes(element[this.idProperty]) ? ' model-manager-table-row-selected ' : '';
                if (element.row_class) {
                    base = base + element.row_class
                }
                if ((this.currentElement[this.idProperty]) && (this.currentElement[this.idProperty] == element[this.idProperty])) {
                    base = base + ' model-manager-table-row-selected ';
                }
                return this.getCSSClass('model-manager-table-row')
                    + base;
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
            elementCellStyle: function(element, columnField) {
                return (typeof(element['vuecrud_'+columnField+'_cellstyle']) == 'undefined')
                    ? ''
                    : element['vuecrud_'+columnField+'_cellstyle'];
            },
            fieldContainsInlineSearchText: function(content) {
                if (this.inlineSearchText == '') {
                    return false;
                }
                let t = document.createElement('DIV');
                t.innerHTML = content;
                let strippedContent = t.textContent || t.innerText || content;
                return strippedContent.toString().toLocaleLowerCase().includes(this.inlineSearchText.toLocaleLowerCase());
            },
            showButton: function(button, element) {
                if (!this.availableOperations.hasOwnProperty(button)) {
                    return false;
                }
                if ((this.availableOperations[button].hasOwnProperty('vuecrud_show_button'))
                    && (typeof(element[this.buttons[button]['vuecrud_show_button']]) != 'undefined')) {
                    return element[this.buttons[button]['vuecrud_show_button']] === true;
                }
                return true;
            },
            viewData: function(additional = {}) {
                let result = {
                    currentSortingColumn: this.currentSortingColumn,
                    currentSortingDirection: this.currentSortingDirection,
                    selectedElements: this.selectedElements,
                };
                Object.keys(additional).forEach((k) => {
                    result[k] = additional[k];
                });

                return result;
            },

        },
        computed: {
            loadingClass: function() {
                if (this.mode != 'list') {
                    return this.getCSSClass('model-manager-table-body-loading', 'opacity-25');
                }

                return '';
            },
            showMassControls: function () {
                return JSON.stringify(this.massOperations) != '{}';
            },
            allowOperations: function() {
                return Object.keys(this.availableOperations).length > 0;
            }
        },
        watch: {
            clearCurrentElementFlag: function() {
                this.currentElement = {};
            }
        }

    }
</script>
