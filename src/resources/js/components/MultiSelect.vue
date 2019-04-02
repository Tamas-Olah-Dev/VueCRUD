<template>
    <div class="multi-select-main"  ref="container">
        <div class="multi-select-container">
            <div class="multi-select-selected-container">
                <span v-for="item, index in selectedItems"
                      class="multi-select-item-span"
                      :key="index"
                >
                    {{ valueset[item] }}
                    <span class="multi-select-remove-button"
                          v-on:click="removeItem(index)"
                    >X</span>
                </span>
            </div>
            <div style="position:relative">
                <input type="text" class="multi-select-input"
                       v-model="filterText"
                       v-on:keyup.arrow-down="moveDropdownSelection(1)"
                       v-on:keyup.arrow-up="moveDropdownSelection(-1)"
                       v-on:keyup.escape="filterText = ''"
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
                 :ref="index"
                 v-html="subject.label"
                 v-on:mouseover="dropdownSelectedIndex = index"
                 v-bind:class="{'multi-select-selected': dropdownSelectedIndex == index}"
                 v-on:click="addItem(subject.id)"></div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            valueset: {type: Object, default: () => {}},
            value: {type: Array, default: () => []},
            allowAddingNewItem: {type: String, default: 'true'}
        },
        data: function() {
            return {
                selectedItems: [],
                filterText: '',
                dropdownSelectedIndex: -1,
                openDropdown: false
            }
        },
        computed: {
            items: function() {
                let result = [];
                for (let index in this.valueset) {
                    if (this.valueset.hasOwnProperty(index)) {
                        result.push({id: index, label: this.valueset[index]});
                    }
                }

                return result;
            },
            caretClass: function() {
                return this.shouldShowDropdown ? 'multi-select-dropdown-caret-open' : ''
            },
            shouldShowDropdown: function() {
                return this.filteredAvailableItems.length > 0
                    && (this.filterText != '' || this.openDropdown == true);
            },
            filteredAvailableItems: function() {
                return this.items.filter((item) => {
                    return item.label.includes(this.filterText)
                        && this.selectedItemIds.indexOf(item.id) == -1;
                });
            },
            selectedItemIds: function() {
                return this.selectedItems.map(item => item.id);
            }
        },
        methods: {
            handleInputFocus: function() {
                if (this.allowAddingNewItem != 'true') {
                    this.openDropdown = !this.openDropdown;
                }
            },
            moveDropdownSelection: function(direction) {
                if (direction < 0) {
                    if (this.dropdownSelectedIndex > 0) {
                        this.dropdownSelectedIndex--;
                        let element = this.$refs[this.dropdownSelectedIndex][0];
                        if (element.offsetTop < element.parentElement.scrollTop) {
                            element.parentElement.scrollTop = element.offsetTop;
                        }
                    }
                } else {
                    if (this.dropdownSelectedIndex < this.filteredAvailableItems.length - 1) {
                        this.dropdownSelectedIndex++;
                        let element = this.$refs[this.dropdownSelectedIndex][0];
                        if (element.offsetTop + element.clientHeight > (element.parentElement.scrollTop + element.parentElement.clientHeight)) {
                            element.parentElement.scrollTop = element.parentElement.scrollTop + element.clientHeight;
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
                        this.$emit('input', this.selectedItems);
                        return;
                    }
                }
                if (typeof(this.filteredAvailableItems[this.dropdownSelectedIndex]) == 'undefined') {
                    return;
                }
                this.addItem(this.filteredAvailableItems[this.dropdownSelectedIndex].id);
            },
            addNewItem: function(item) {
                this.selectedItems.push({
                    id: -1,
                    label: item
                });
                this.openDropdown = false;
                this.$emit('input', this.selectedItems);
            },
            addItem: function(item) {
                this.selectedItems.push(item);
                this.filterText = '';
                this.dropdownSelectedIndex = -1;
                this.openDropdown = false;
                this.$emit('input', this.selectedItems);
            },
            removeItem: function(index) {
                this.selectedItems.splice(index, 1);
                this.$emit('input', this.selectedItems);
            },
            handleClickOutside: function(e) {
                const el = this.$refs.dropdown;
                const caretel = this.$refs.caret;
                if ((!el.contains(e.target)) && ((!caretel.contains(e.target)))) {
                    this.filterText = '';
                    this.dropdownSelectedIndex = -1;
                    this.openDropdown = false;
                }
            },

        },
        mounted: function() {
            this.selectedItems = [];
            for (var index = 0; index < this.value.length; index++) {
                this.selectedItems.push(this.value[index]);
            }

        },
        watch: {
            filterText: function() {
                this.dropdownSelectedIndex = -1;
            },
            shouldShowDropdown: function() {
                if (this.shouldShowDropdown) {
                    document.addEventListener('click', this.handleClickOutside, true);
                } else {
                    document.removeEventListener('click', this.handleClickOutside, true);
                }
            },
            value: function() {
                this.selectedItems = [];
                for (var index = 0; index < this.value.length; index++) {
                    this.selectedItems.push(this.value[index]);
                }
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
        border: 1px solid darkgrey;
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
        margin-top: 20px;
        border-radius: 5px;
        width: 99%;
    }
    .multi-select-item-span {
        padding: 2px;
        padding-left: 5px;
        padding-right: 5px;
        border-radius: 5px;
        box-shadow: 2px 3px lightgrey;
        /*background-color: lightcyan;*/
        margin: 3px;
        color: black;
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
        padding: 5px;
        max-height: 15em;
        overflow-y: scroll;
        border: 1px solid black;
        border-top: none;
        box-shadow: 5px 5px darkgrey;
        background-color: white;
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
        right: .7em;
        bottom: .3em;
        font-size: 1.3em;
        transition: transform 200ms ease-in-out;
    }
    .multi-select-dropdown-caret-open {
        transform: rotate(-90deg);
    }

</style>