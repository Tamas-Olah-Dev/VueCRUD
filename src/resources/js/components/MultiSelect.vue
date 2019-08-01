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
                          v-on:click="removeItem(index)"
                    >X</span>
                </span>
            </div>
            <div style="position:relative">
                <input type="text" class="multi-select-input"
                       v-bind:class="inputClass"
                       ref="input"
                       v-model="filterText"
                       v-bind:style="{'color': invalidItem ? 'red' : 'black'}"
                       v-on:keyup.arrow-down="moveDropdownSelection(1)"
                       v-on:keyup.arrow-up="moveDropdownSelection(-1)"
                       v-on:keyup.escape="resetFilterTextIfNeeded"
                       v-on:keyup.enter="addSelectedFromDropdownOrInput"
                >
                <span ref="caret"
                      v-on:click="openDropdown = !openDropdown"
                      class="multi-select-dropdown-caret"
                      v-bind:class="caretClass"
                >&#9666;</span>

            </div>
        </div>

        <div class="multi-select-dropdown" v-show="shouldShowDropdown" ref="dropdown">
            <div v-for="subject, index in filteredAvailableItems"
                 :key="index"
                 ref="index"
                 v-html="subject.label"
                 v-on:mouseover="dropdownSelectedIndex = index"
                 v-bind:class="{'multi-select-selected': dropdownSelectedIndex == index}"
                 v-on:click="addItem(subject)"></div>
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
            inputClass: {type: String, default: 'form-control'}
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
                if (this.multiple) {
                    return this.filteredAvailableItems.length > 0
                        && (this.filterText != '' || this.openDropdown == true);
                } else {
                    let currentLabel = this.selectedItems.length > 0
                        ? this.selectedItems[0]['label']
                        : '';
                    return this.filteredAvailableItems.length > 0
                        && (this.filterText != currentLabel || this.openDropdown == true);
                }
            },
            filteredAvailableItems: function() {
                if (this.multiple) {
                    return this.items.filter((item) => {
                        return item.uppercaseLabel.includes(this.filterText.toUpperCase())
                            && this.selectedItemIds.indexOf(item['id']) == -1;
                    });
                } else {
                    return this.items.filter((item) => {
                        return item.uppercaseLabel.includes(this.filterText.toUpperCase());
                    });
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
            }
        },
        methods: {
            resetFilterTextIfNeeded: function() {
                if (this.multiple) {
                    this.filterText = '';
                } else {
                    if (this.selectedItems.length > 0) {
                        this.filterText = this.selectedItems[0]['label'];
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
                    this.filterText = item.label;
                }
                this.invalidItem = false;
            },
            transformItem: function(originalItem) {
                return {
                    id: originalItem[this.idProperty],
                    label: originalItem[this.labelProperty],
                    uppercaseLabel: originalItem[this.labelProperty].toUpperCase()
                };
            },
            handleInputFocus: function() {
                if (this.allowAddingNewItem != 'true') {
                    this.openDropdown = !this.openDropdown;
                }
            },
            moveDropdownSelection: function(direction) {
                if (direction < 0) {
                    if (this.dropdownSelectedIndex > 0) {
                        this.dropdownSelectedIndex--;
                        let element = this.$refs['index'][this.dropdownSelectedIndex];
                        if (element.offsetTop < element.parentElement.scrollTop) {
                            element.parentElement.scrollTop = element.offsetTop;
                        }
                    }
                } else {
                    if (this.dropdownSelectedIndex < this.filteredAvailableItems.length - 1) {
                        this.dropdownSelectedIndex++;
                        let element = this.$refs['index'][this.dropdownSelectedIndex];
                        if (element.offsetTop + element.clientHeight > (element.parentElement.scrollTop + element.parentElement.clientHeight)) {
                            element.parentElement.scrollTop = element.parentElement.scrollTop + element.clientHeight + 3;
                        }
                    }
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
                this.pushToSelectedItems(item);
                if (this.multiple) {
                    this.filterText = '';
                } else {
                    this.filterText = this.selectedItems[0]['label'];
                    this.invalidItem = false;
                }
                this.dropdownSelectedIndex = -1;
                this.openDropdown = false;
                this.$emit('input', this.emittedValue);
            },
            removeItem: function(index) {
                this.selectedItems.splice(index, 1);
                this.$emit('input', this.emittedValue);
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
                    return this.multiple
                        ? this.value.includes(item[this.idProperty])
                        : item[this.idProperty] == this.value;
                }).map(item => this.transformItem(item));
                if (!this.multiple) {
                    this.filterText = this.selectedItems.length > 0 ? this.selectedItems[0].label : '';
                }
            }
        },
        mounted: function() {
            this.parseValue();
        },
        watch: {
            filterText: function(value) {
                if (this.multiple) {
                    if (value == '') {
                        this.dropdownSelectedIndex = -1;
                    } else {
                        if ((!this.allowAddingNewItem) && (this.filteredAvailableItems.length > 0)) {
                            this.dropdownSelectedIndex = 0;
                        }
                    }
                } else {
                    let currentLabel = this.selectedItems.length > 0
                        ? this.selectedItems[0]['label']
                        : '';
                    if (value != currentLabel) {
                        if (this.filteredAvailableItems.length == 0) {
                            this.selectedItems = [];
                            this.$emit('input', null);
                            this.invalidItem = true;
                        }
                        this.openDropdown = true;
                    }
                }
            },
            shouldShowDropdown: function() {
                if (this.shouldShowDropdown) {
                    document.addEventListener('click', this.handleClickOutside, true);
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
        max-height: 5em;
        overflow-y: auto;
    }
    .multi-select-input {
        order: 9999;
        margin: 5px;
        /*margin-top: 20px;*/
        border-radius: 5px;
        width: 98%;
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
        width: 80%;
        padding: 5px;
        max-height: 15em;
        overflow-y: scroll;
        border: 1px solid black;
        border-top: none;
        box-shadow: 5px 5px darkgrey;
        background-color: white;
        position:fixed;
    }
    .multi-select-dropdown > div {
        cursor:pointer;
    }
    .multi-select-dropdown > div.multi-select-selected {
        /*background-color: lightgoldenrodyellow;*/
        font-weight:bold;
    }
    .multi-select-dropdown-caret {
        cursor:pointer;
        position:absolute;
        z-index: 100;
        right: 2.5%;
        bottom: .3em;
        font-size: 1.3em;
        transition: transform 200ms ease-in-out;
    }
    .multi-select-dropdown-caret-open {
        transform: rotate(-90deg);
    }

</style>