<template>
    <div class="multi-select-main"  ref="container">
        <div class="multi-select-container">
            <div class="multi-select-selected-container" v-if="multiple">
                <span v-for="item, index in selectedItems"
                      class="multi-select-item-span"
                      :key="index"
                >
                    {{ item.label }}
                    <span class="multi-select-remove-button"
                          v-if="allowRemove"
                          v-on:click="removeItem(index)"
                    >X</span>
                </span>
            </div>
            <div style="position:relative">
                <input type="text" class="multi-select-input"
                       readonly
                       style="background-color: white"
                       v-bind:class="inputClass"
                       v-bind:value="selectedItemLabel"
                       :placeholder="placeholderLabel"
                       v-bind:style="{'color': invalidItem ? 'red' : 'black', 'background-color': disabled ? '#e9ecef' : 'white'}"
                       v-on:keyup.arrow-down="moveDropdownSelection(1)"
                       v-on:keyup.arrow-up="moveDropdownSelection(-1)"
                       v-on:click="openDropdown = !openDropdown"
                >
                <span ref="caret"
                      v-if="!disabled"
                      v-on:click="openDropdown = !openDropdown"
                      class="multi-select-dropdown-caret"
                      v-bind:class="caretClass"
                >&#9666;</span>

            </div>
        </div>

        <div class="multi-select-dropdown" v-show="shouldShowDropdown" ref="dropdown">
            <input type="text" class="multi-select-input"
                   v-bind:class="inputClass"
                   ref="input"
                   v-model="filterText"
                   :placeholder="placeholderLabel"
                   v-bind:style="{'color': invalidItem ? 'red' : 'black'}"
                   v-on:keyup.arrow-down="moveDropdownSelection(1)"
                   v-on:keyup.arrow-up="moveDropdownSelection(-1)"
                   v-on:keyup.escape="resetFilterTextIfNeeded"
                   v-on:keyup.enter="addSelectedFromDropdownOrInput"
            >
            <div class="multi-select-dropdown-list">
                <div v-for="subject, index in filteredAvailableItems"
                     :key="index"
                     ref="index"
                     v-html="subject.label"
                     class="multi-select-available-item"
                     v-on:mouseover="dropdownSelectedIndex = index"
                     v-bind:class="itemClass(subject, index)"
                     :title="subject.disabledTitle"
                     v-on:click="addItem(subject)"></div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            valueset: {type: Array, default: () => []},
            value: {},
            allowAddingNewItem: {type: Boolean, default: false},
            idProperty: {type: String, default: 'id'},
            labelProperty: {type: String, default: 'name'},
            multiple: {type: Boolean, default: true},
            inputClass: {type: String, default: 'form-control'},
            labels: {type: Object, default: () => {
                    return {
                        'No options': 'No options',
                        'Select...': 'Select...'
                    }
                }},
            allowRemove: {type: Boolean, default: true},
            respectDisabledAttribute: {type: Boolean, default: false},
            undefinedValue: {type: Number, default: -1},
            undefinedLabel: {type: String, default: ''},
            disabled: {type: Boolean, default: false}
        },
        data: function() {
            return {
                selectedItems: [],
                filterText: '',
                dropdownSelectedIndex: -1,
                openDropdown: false,
                invalidItem: false,
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
                    return this.labels['No options'];
                }
                if ((this.value == null)
                    || (this.value == -1)
                    || ((Array.isArray(this.value)) && (this.value.length == 0)))
                {
                    return this.labels['Select...'];
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
                return this.shouldShowDropdown ? 'multi-select-dropdown-caret-open' : ''
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
            itemClass: function(item, index) {
                let result = [];
                if (item['id'] == this.undefinedValue) {
                    result.push('multi-select-undefined-item');
                }
                if (this.dropdownSelectedIndex == index) {
                    result.push('multi-select-selected');
                }
                if ((this.respectDisabledAttribute)
                    && (item.hasOwnProperty('disabled'))
                    && (item.disabled)) {
                    result = ['multi-select-item-disabled']
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
            // filterText: function(value) {
            //     if (this.multiple) {
            //         if (value == '') {
            //             this.dropdownSelectedIndex = -1;
            //         } else {
            //             if ((!this.allowAddingNewItem) && (this.filteredAvailableItems.length > 0)) {
            //                 this.dropdownSelectedIndex = 0;
            //             }
            //         }
            //     } else {
            //         let currentLabel = this.selectedItems.length > 0
            //             ? this.selectedItems[0]['label']
            //             : '';
            //         if (value != currentLabel) {
            //             if (this.filteredAvailableItems.length == 0) {
            //                 this.selectedItems = [];
            //                 this.$emit('input', null);
            //                 this.invalidItem = true;
            //             }
            //             this.openDropdown = true;
            //         }
            //     }
            // },
            shouldShowDropdown: function() {
                if (this.shouldShowDropdown) {
                    document.addEventListener('click', this.handleClickOutside, true);
                    Vue.nextTick(() => {
                        this.$refs['input'].focus();
                    })
                } else {
                    document.removeEventListener('click', this.handleClickOutside, true);
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
    .multi-select-main {
        position: relative;
    }
    .multi-select-container {
        display: flex;
        flex-direction:column;
        flex-wrap: wrap;
        /*border: 1px solid darkgrey;*/
        width: 100%;
        max-width: 100%;
    }
    .multi-select-selected-container {
        display: flex;
        flex-wrap: wrap;
        font-size: .7em;
        max-height: 12em;
        overflow-y: auto;
    }
    .multi-select-input {
        order: 9999;
        margin: 0px;
        /*margin-top: 20px;*/
        border-radius: 5px;
        width: 100%;
        padding-right:40px;
        max-height:20%;
        margin-bottom: 5px;
    }
    .multi-select-item-span {
        padding: 4px;
        padding-left: 5px;
        padding-right: 5px;
        /*border-radius: 2px;*/
        box-shadow: 3px 3px lightgrey;
        background-color: #3e9cb9;
        margin: 3px;
        color: white;
    }
    .multi-select-remove-button {
        margin-left: 10px;
        padding: 3px;
        cursor: pointer;
        font-weight: bold;
        color: darkgrey;
        user-select: none;
    }
    .multi-select-remove-button:hover {
        color: white;
    }
    .multi-select-dropdown {
        z-index: 1000;
        width: 100%;
        /*max-width: 400px;*/
        padding: 5px;
        height: 20em;
        border: 1px solid black;
        border-top: none;
        box-shadow: 5px 5px darkgrey;
        background-color: white;
        position:absolute;
        overflow: hidden;
    }

    .multi-select-dropdown-list {
        overflow-y: scroll;
        height: 80%;
        width: 100%;
    }
    .multi-select-dropdown-list > div {
        cursor:pointer;
    }
    .multi-select-dropdown-list > div.multi-select-selected {
        /*background-color: lightgoldenrodyellow;*/
        font-weight:bold;
    }
    .multi-select-dropdown-caret {
        cursor:pointer;
        position:absolute;
        z-index: 100;
        right: 0px;
        padding-right:10px;
        bottom: 10%;
        font-size: 200%;
        transition: transform 200ms ease-in-out;
    }
    .multi-select-clear-button {
        cursor:pointer;
        position:absolute;
        z-index: 100;
        right: 35px;
        bottom: -2px;
        font-size: 1.6em;
        color: red;
    }
    .multi-select-dropdown-caret-open {
        transform: rotate(-90deg);
        transform-origin: center;
    }
    .multi-select-item-disabled {
        cursor: not-allowed !important;
        color: darkgrey;
    }
    .multi-select-undefined-item {
        margin-bottom: 2px;
        padding-bottom: 2px;
        border-bottom: 1px solid darkgrey;
    }
    .multi-select-available-item {
        line-height: normal;
        padding-top: .7em;
        padding-bottom: .7em;
        border-bottom: 1px solid lightgray;
    }

</style>
