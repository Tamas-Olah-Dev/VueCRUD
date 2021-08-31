<template>
    <div :class="getCSSClass('searchable-select-main')"  ref="container">
        <div :class="getCSSClass('searchable-select-container')">
            <div :class="getCSSClass('searchable-select-selected-container')" v-if="multiple">
                <span v-for="item, index in selectedItems"
                      :class="getCSSClass('searchable-select-item-span')"
                      :key="index"
                >
                    {{ item.label }}
                    <span :class="getCSSClass('searchable-select-remove-button')"
                          v-if="allowRemove"
                          v-on:click="removeItem(index)"
                    >X</span>
                </span>
            </div>
            <div style="position:relative; display: flex; align-items: center">
                <input type="text" :class="getCSSClass('searchable-select-input')"
                       readonly
                       style="width: 100%; padding-right:40px; order: 9999"
                       v-bind:value="selectedItemLabel"
                       :placeholder="placeholderLabel"
                       v-bind:style="{'color': invalidItem ? 'red' : 'black', 'background-color': disabled ? '#e9ecef' : 'white'}"
                       v-on:keyup.arrow-down="moveDropdownSelection(1)"
                       v-on:keyup.arrow-up="moveDropdownSelection(-1)"
                       v-on:click="openDropdown = !openDropdown"
                >
                <span ref="caret"
                      v-if="!disabled"
                      style="position: absolute; right: .3rem; transition: transform 100ms ease-in-out; cursor:pointer; transform-origin:center"
                      v-on:click="openDropdown = !openDropdown"
                      :class="caretClass"
                      v-html="icon('caret-left')"
                ></span>
            </div>
        </div>

        <div :class="getCSSClass('searchable-select-dropdown')"
             style="margin-top: .3rem"
             v-show="shouldShowDropdown" ref="dropdown">
            <input type="text" :class="getCSSClass('searchable-select-input')"
                   ref="input"
                   v-model="filterText"
                   :placeholder="placeholderLabel"
                   v-bind:style="{'color': invalidItem ? 'red' : 'black'}"
                   v-on:keyup.arrow-down="moveDropdownSelection(1)"
                   v-on:keyup.arrow-up="moveDropdownSelection(-1)"
                   v-on:keyup.escape="resetFilterTextIfNeeded"
                   v-on:keyup.enter="addSelectedFromDropdownOrInput"
            >
            <div :class="getCSSClass('searchable-select-dropdown-list')">
                <div v-for="subject, index in filteredAvailableItems"
                     :key="index"
                     ref="index"
                     v-html="subject.label"
                     v-on:mouseover="dropdownSelectedIndex = index"
                     :class="itemClass(subject, index)"
                     :title="subject.disabledTitle"
                     v-on:click="addItem(subject)"></div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        inject: ['loadingIndicator', 'getCSSClass', 'translate', 'icon', 'mainButtons'],
        props: {
            valueset: {type: Array, default: () => []},
            value: {},
            allowAddingNewItem: {type: Boolean, default: false},
            idProperty: {type: String, default: 'id'},
            labelProperty: {type: String, default: 'name'},
            multiple: {type: Boolean, default: true},
            allowRemove: {type: Boolean, default: true},
            respectDisabledAttribute: {type: Boolean, default: false},
            undefinedValue: {type: Number, default: -1},
            undefinedLabel: {type: String, default: ''},
            disabled: {type: Boolean, default: false},
        },
        data: function() {
            return {
                selectedItems: [],
                filterText: '',
                dropdownSelectedIndex: -1,
                openDropdown: false,
                invalidItem: false,
                showPopup: false,
                editFormKey: Math.random().toString(),
            }
        },
        computed: {
            selectedItemLabel: function() {
                if ((this.value == null) || ((Array.isArray(this.value)) && (this.value.length == 0))) {
                    return '';
                }
                if (this.value == this.undefinedValue) {
                    return this.undefinedLabel;
                }
                if (!this.multiple) {
                    return this.valueset.filter((item) => {
                        return item[this.idProperty].toString() == this.value.toString();
                    })[0][this.labelProperty];
                }
            },
            placeholderLabel: function() {
                if (this.valueset.length == 0) {
                    return this.translate('No options');
                }
                if ((this.value == null)
                    || (this.value == -1)
                    || ((Array.isArray(this.value)) && (this.value.length == 0)))
                {
                    return this.translate('Select...');
                }
                return '';
            },
            items: function() {
                let result = [];
                for (let index in this.valueset) {
                    if (this.valueset.hasOwnProperty(index)) {
                        result.push(this.transformItem(this.valueset[index]));
                    }
                }

                return result;
            },
            caretClass: function() {
                return this.shouldShowDropdown
                    ? this.getCSSClass('searchable-select-dropdown-caret') + ' ' + this.getCSSClass('searchable-select-dropdown-caret-open')
                    : this.getCSSClass('searchable-select-dropdown-caret');
            },
            shouldShowDropdown: function() {
                if(this.disabled) {
                    return false;
                }

                return this.openDropdown == true;
                // if (this.multiple) {
                //     return this.filteredAvailableItems.length > 0
                //         && (this.filterText != '' || this.openDropdown == true);
                // } else {
                //     let currentLabel = this.selectedItems.length > 0
                //         ? this.selectedItems[0]['label']
                //         : '';
                //     return this.filteredAvailableItems.length > 0
                //         && (this.openDropdown == true);
                // }
            },
            filteredAvailableItems: function() {
                let result = [];
                if (this.undefinedLabel != '') {
                    result.push({id: this.undefinedValue, label: this.undefinedLabel});
                }
                if (this.multiple) {
                    return result.concat(this.items.filter((item) => {
                        return item.uppercaseLabel.includes(this.filterText.toUpperCase())
                            && this.selectedItemIds.indexOf(item['id']) == -1;
                    }));
                } else {
                    return result.concat(this.items.filter((item) => {
                        return item.uppercaseLabel.includes(this.filterText.toUpperCase());
                    }));
                }
            },
            selectedItemIds: function() {
                return this.selectedItems.map(item => item[this.idProperty]);
            },
            emittedValue: function() {
                if (this.multiple) {
                    return this.selectedItems.map(item => item['id']);
                }
                return this.selectedItems.length > 0
                    ? this.selectedItems[0]['id']
                    : null;
            },
        },
        methods: {
            showAddPopup: function() {
                this.showPopup = true;
            },
            hideAddPopup: function() {
                this.showPopup = false;
                this.editFormKey = Math.random().toString()
            },
            updateValueset: function(event) {
                this.$emit('addtovalueset', event);
                this.$emit('input', event[this.idProperty]);
                this.hideAddPopup();
            },
            itemClass: function(item, index) {
                let result = ['searchable-select-available-item'];
                if (item['id'] == this.undefinedValue) {
                    result.push(this.getCSSClass('searchable-select-undefined-item'));
                }
                if (this.dropdownSelectedIndex == index) {
                    result.push(this.getCSSClass('searchable-select-selected'));
                }
                if ((this.respectDisabledAttribute)
                    && (item.hasOwnProperty('disabled'))
                    && (item.disabled)) {
                    result.push(this.getCSSClass('searchable-select-item-disabled'));
                }
                return result;
            },
            clear: function() {
                this.removeItem(0);
                this.filterText = '';
            },
            resetFilterTextIfNeeded: function() {
                if (this.multiple) {
                    this.filterText = '';
                } else {
                    if (this.selectedItems.length > 0) {
                        //this.filterText = this.selectedItems[0]['label'];
                        this.filterText = '';
                    } else {
                        this.filterText = ''
                    }
                }
                this.invalidItem = false;
            },
            activeInput: function() {
                return window.document.activeElement;
            },
            pushToSelectedItems: function(item) {
                if (!this.multiple) {
                    this.selectedItems = [];
                }
                this.selectedItems.push(item);
                if (!this.multiple) {
                    //this.filterText = item.label;
                    this.filterText = '';
                }
                this.invalidItem = false;
            },
            transformItem: function(originalItem) {
                return {
                    id: originalItem[this.idProperty],
                    label: originalItem[this.labelProperty].toString(),
                    uppercaseLabel: originalItem[this.labelProperty].toString().toUpperCase(),
                    disabled: typeof(originalItem.disabled) != 'undefined' ? originalItem.disabled : false,
                    disabledTitle: typeof(originalItem.disabled_title) != 'undefined' ? originalItem.disabled_title : '',
                };
            },
            handleInputFocus: function() {
                if (this.allowAddingNewItem != 'true') {
                    this.openDropdown = !this.openDropdown;
                }
            },
            moveDropdownSelection: function(direction) {
                if (this.disabled) {
                    return true;
                }
                if (direction < 0) {
                    if (this.dropdownSelectedIndex > 0) {
                        this.dropdownSelectedIndex--;
                        let element = this.$refs['index'][this.dropdownSelectedIndex];
                        if (element != null) {
                            if (element.offsetTop < element.parentElement.scrollTop) {
                                element.parentElement.scrollTop = element.offsetTop;
                            }
                        }
                    }
                } else {
                    if (this.dropdownSelectedIndex < this.filteredAvailableItems.length - 1) {
                        this.dropdownSelectedIndex++;
                        let element = this.$refs['index'][this.dropdownSelectedIndex];
                        if (element != null) {
                            if (element.offsetTop + element.clientHeight > (element.parentElement.scrollTop + element.parentElement.clientHeight)) {
                                element.parentElement.scrollTop = element.parentElement.scrollTop + element.clientHeight + 3;
                            }
                        }
                    }
                }
                if ((this.respectDisabledAttribute)
                    && (this.filteredAvailableItems[this.dropdownSelectedIndex].disabled)) {
                    this.moveDropdownSelection(direction);
                }
            },
            addSelectedFromDropdownOrInput: function() {
                if (this.dropdownSelectedIndex == -1) {
                    if (this.allowAddingNewItem != 'true') {
                        return;
                    }
                    if (this.filterText != '') {
                        this.addNewItem(this.filterText);
                        this.filterText = '';
                        this.$emit('input', this.emittedValue);
                        return;
                    }
                }
                if (typeof(this.filteredAvailableItems[this.dropdownSelectedIndex]) == 'undefined') {
                    return;
                }
                this.addItem(this.filteredAvailableItems[this.dropdownSelectedIndex]);
            },
            addNewItem: function(item) {
                let newItem = {};
                newItem[this.idProperty] = -1;
                newItem[this.labelProperty] = item;
                this.pushToSelectedItems(newItem);
                this.openDropdown = false;
                this.$emit('input', this.emittedValue);
            },
            addItem: function(item) {
                if ((this.respectDisabledAttribute) && (item.disabled)) {
                    return;
                }
                if(this.disabled) {
                    return;
                }
                this.pushToSelectedItems(item);
                if (this.multiple) {
                    this.filterText = '';
                } else {
                    //this.filterText = this.selectedItems[0]['label'];
                    this.filterText = ''
                    this.invalidItem = false;
                }
                this.dropdownSelectedIndex = -1;
                this.openDropdown = false;
                this.$emit('input', this.emittedValue);
            },
            removeItem: function(index, emitInput) {
                emitInput = typeof(emitInput) == 'undefined' ? true : emitInput;
                this.selectedItems.splice(index, 1);
                if (emitInput) {
                    this.$emit('input', this.emittedValue);
                }
            },
            handleClickOutside: function(e) {
                const el = this.$refs.dropdown;
                const caretel = this.$refs.caret;
                if ((!el.contains(e.target)) && ((!caretel.contains(e.target)))) {
                    this.resetFilterTextIfNeeded();
                    this.dropdownSelectedIndex = -1;
                    this.openDropdown = false;
                }
            },
            handleEscape: function(e) {
                if (e.keyCode == 'Esc') {
                    this.resetFilterTextIfNeeded();
                    this.dropdownSelectedIndex = -1;
                    this.openDropdown = false;
                }
            },
            parseValue: function() {
                this.selectedItems = [];
                this.selectedItems = this.valueset.filter((item) => {
                    if ((typeof(this.value) != 'undefined') && (this.value != null)) {
                        if ((this.respectDisabledAttribute)
                            && (typeof(item.disabled) != 'undefined')
                            && (item.disabled)) {
                            return false;
                        }
                        return this.multiple
                            ? this.value.includes(item[this.idProperty])
                            : item[this.idProperty] == this.value;
                    }
                }).map(item => this.transformItem(item));
                if (!this.multiple) {
                    //this.filterText = this.selectedItems.length > 0 ? this.selectedItems[0].label : '';
                    this.filterText = '';
                }
            }
        },
        mounted: function() {
            this.parseValue();
        },
        watch: {
            shouldShowDropdown: function() {
                if (this.shouldShowDropdown) {
                    document.addEventListener('click', this.handleClickOutside, true);
                    document.addEventListener('keyup', this.handleEscape, true);
                    Vue.nextTick(() => {
                        this.$refs['input'].focus();
                    })
                } else {
                    document.removeEventListener('click', this.handleClickOutside, true);
                    document.removeEventListener('keyup', this.handleEscape, true);
                }
            },
            value: function() {
                this.parseValue();
            },
            valueset: function() {
                this.parseValue();
            }
        }
    }
</script>
<style>
    .searchable-select-main {
        position: relative;
    }
    .searchable-select-container {
        display: flex;
        flex-direction:column;
        flex-wrap: wrap;
        /*border: 1px solid darkgrey;*/
        width: 100%;
        max-width: 100%;
    }
    .searchable-select-selected-container {
        display: flex;
        flex-wrap: wrap;
        max-height: 12em;
        overflow-y: auto;
    }
    .searchable-select-item-span {
        margin: 3px;
    }
    .searchable-select-remove-button {
        margin-left: .5rem;
        cursor: pointer;
        user-select: none;
    }
    .searchable-select-remove-button:hover {
        color: white;
    }
    .searchable-select-dropdown {
        z-index: 1000;
        width: 100%;
        max-height: 20rem;
        position:absolute;
        overflow: hidden;
    }

    .searchable-select-dropdown-list {
        overflow-y: scroll;
        height: 80%;
        width: 100%;
    }
    .searchable-select-dropdown-list > div {
        cursor:pointer;
    }
    .searchable-select-dropdown-list > div.searchable-select-selected {
        font-weight:bold;
    }
    .searchable-select-dropdown-caret-open {
        transform: rotate(-90deg);
    }
    .searchable-select-item-disabled {
        cursor: not-allowed !important;
        opacity: .5;
    }
    .searchable-select-undefined-item {
    }
    .searchable-select-available-item {
        line-height: normal;
    }

</style>
