<template>
    <div class="container-fluid select-or-add-field-container"
         v-on:keyup.down="selectNext"
         v-on:keyup.up="selectPrevious"
         v-on:keyup.enter.stop="selectPreselected"
         ref="container"
    >
        <div class="row">
            <div class="col-12 ">
                <div v-if="multiSelect">
                    <div class="select-or-add-field-multi-input"
                         v-on:click.self="toggleDropdown"
                         v-on:keydown="showDropdownFlag = true; $refs.searchterminput.focus()"
                    >
                        <div v-for="item in multiSelected"
                             class="badge badge-secondary select-or-add-field-badge"
                         >
                            <span v-html="valueset[findValuesetKeyItemById(item)][labelfield]"></span>
                            <span class="select-or-add-field-badge-remove-button" v-on:click="selectItem(findValuesetKeyItemById(item))">X</span>
                        </div>
                    </div>
                </div>
                <div class="input-group" v-if="!multiSelect">
                    <label v-if="formElementLabel != ''">{{ formElementLabel }}</label>
                    <input v-model="selectedLabel"
                           class="form-control select-or-add-field-single-input"
                           v-on:click="toggleDropdown"
                           v-on:keydown="showDropdownFlag = true; $refs.searchterminput.focus()"
                           readonly
                    >
                    <div class="input-group-append" v-if="enableAdding">
                        <button type="button" v-on:click="showNewForm"
                                class="btn btn-primary btn-block"
                        >
                            {{ $root.translate('New') }}...
                        </button>
                        <button type="button" v-on:click="selected = null"
                                class="btn btn-secondary"
                        >
                            X
                        </button>
                    </div>
                </div>
                <div class="select-or-add-field-chevron" v-html="dropdownChevron"></div>
                <div class="select-or-add-field-dropdown" v-show="showDropdownFlag && !showForm">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="ion-android-search"></i></span></div>
                        <input v-model="searchterm"
                               type="text"
                               class="form-control form-control-sm"
                               ref="searchterminput"
                        >
                    </div>
                    <div v-if="showDropdownFlag" class="select-or-add-list-container">
                        <div v-if="filteredValueset.length == 0" v-html="$root.translate('No results')"></div>
                        <ul v-if="filteredValueset.length > 0">
                            <li v-for="(valuesetvalue, key) in filteredValueset"
                                v-on:click="selectItem(key)"
                                v-bind:class="{'select-or-add-field-dropdown-preselected': preselect == key}"
                                v-bind:ref="'valueset_'+key"
                            >
                                <span v-if="multiSelected.indexOf(valuesetvalue.id) > -1">
                                    <i class="fa far fa-check"></i>&nbsp;
                                </span>
                                <span v-html="valuesetvalue[optionLabelField]"
                                ></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" v-if="showForm">
            <transition name="modalfade">
                <div class="select-or-add-field-overlay" v-on:click="hideForm"></div>
            </transition>
            <transition name="modalfade">
                <div class="col-12 select-or-add-form-container">
                    <form role="form" class="margin-b-20" v-on:submit.prevent="submitForm">
                        <div class="row">
                            <div v-for="data, fieldname in newElementData"
                                 v-bind:class="data.containerClass">
                                <label>
                                    {{ data.label }}
                                    <span v-if="data.mandatory"> *</span>
                                </label>
                                <input v-if="data.kind == 'input'"
                                       v-model="newElementData[fieldname].value"
                                       v-bind:class="data.class"
                                >
                                <select v-if="data.kind == 'select'"
                                        v-model="newElementData[fieldname].value"
                                        v-bind:class="data.class"
                                >
                                    <option v-for="valuesetitem, valuesetvalue in data.valueset"
                                            v-bind:value="valuesetvalue"
                                            v-html="valuesetitem">
                                    </option>
                                </select>
                                <datepicker v-if="data.kind == 'datepicker'"
                                            v-bind="JSON.parse(data.props)"
                                            v-model="subjectData[fieldname].value"
                                ></datepicker>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="alert alert-danger col col-12"
                             v-for="error in errors" v-html="error[0]"></div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <button type="button"
                                    class="btn btn-lg btn-primary btn-block"
                                    v-on:click="submitForm"
                            >{{ $root.translate("Save") }}</button>
                        </div>
                        <div class="col">
                            <button type="button"
                                    class="btn btn-lg btn-default btn-block"
                                    v-on:click="hideForm"
                            >{{ $root.translate("Cancel") }}</button>
                        </div>
                    </div>
                </div>
            </transition>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [
            'default',
            'formElementLabel',
            'labelfield',
            'optionLabelField',
            'lookupUrl',
            'formUrl',
            'storeUrl',
            'value',
            'enableAddingElement',
            'multiple',
            'closeOnSelect',
            'defaultJson'
        ],
        data: function() {
            return {
                valueset: {},
                selected: -1,
                formHtml: '',
                showForm: false,
                newElementData: {},
                errors: {},
                newElementId: null,
                showDropdownFlag: false,
                selectedLabel: '',
                showFilteredDropdownFlag: false,
                enableAdding: true,
                searchterm: '',
                preselect: null,
                multiSelected: [],
                defaultValue: null
            }
        },
        mounted() {
            if ((typeof(this.enableAddingElement) != 'undefined') && (this.enableAddingElement == 'false')) {
                this.enableAdding = false;
            }
            if ((typeof(this.defaultJson) != 'undefined') && (this.defaultJson != '')) {
                this.defaultValue = JSON.parse(this.defaultJson);
            }

            this.getValueset();

        },
        computed: {
            formdata: function() {
                var result = {};
                for (var key in this.newElementData) {
                    if (this.newElementData.hasOwnProperty(key)) {
                        result[key] = this.newElementData[key].value;
                    }
                }
                return result;
            },
            filteredValueset: function() {
                var result = [];
                for (var key in this.valueset) {
                    if (this.valueset.hasOwnProperty(key)) {
                        if (this.valueset[key][this.labelfield].toLowerCase().includes(this.searchterm.toLowerCase())) {
                            result.push(this.valueset[key]);
                        }
                    }
                }
                return result;
            },
            multiSelect: function() {
                return typeof(this.multiple) != 'undefined' && this.multiple == 'true';
            },
            multiSelectLabel: function() {
                var results = [];
                for (var key in this.valueset) {
                    if ((this.valueset.hasOwnProperty(key))
                        && (this.valueset[key].hasOwnProperty('id'))
                        && (this.multiSelected.indexOf(this.valueset[key]['id']) > -1)
                    ) {
                        results.push(this.valueset[key][this.labelfield]);
                    }
                }

                return results.join(', ');
            },
            dropdownChevron: function() {
                return this.showDropdownFlag ? '▼' : '◂';
            },
            shouldCloseOnSelect: function() {
                return typeof(this.closeOnSelect) != 'undefined' && this.closeOnSelect == 'true';
            }
        },
        methods: {
            selectPreselected: function (event) {
                event.stopImmediatePropagation();
                event.target.preventDefault();
                if (this.showDropdownFlag) {
                    if (this.preselect > -1) {
                        this.selectItem(this.preselect);
                    }
                }
            },
            selectPrevious: function() {
                if (this.showDropdownFlag) {
                    if (this.preselect > 0) {
                        this.preselect = this.preselect - 1;
                    }
                }
            },
            selectNext: function() {
                if (this.showDropdownFlag) {
                    if (this.preselect < this.filteredValueset.length - 1) {
                        this.preselect = this.preselect + 1;
                    }
                }
            },
            findValuesetKeyItemById: function(id) {
                var result = null;
                for (var key in this.valueset) {
                    if (this.valueset.hasOwnProperty(key)) {
                        if (this.valueset[key].id == id) {
                            result = key
                        }
                    }
                }
                return result;
            },
            toggleDropdown: function() {
                if (this.showDropdownFlag) {
                    document.removeEventListener('click', this.handleClickOutside, true);
                } else {
                    document.addEventListener('click', this.handleClickOutside, true);
                }
                this.showDropdownFlag = !this.showDropdownFlag;
            },
            selectItem: function(key) {
                if (this.multiSelect) {
                    if (this.multiSelected.indexOf(this.valueset[key]['id']) == -1) {
                        this.multiSelected.push(this.valueset[key]['id']);
                    } else {
                        this.multiSelected = this.multiSelected.filter((arraykey) => { return arraykey != this.valueset[key]['id']});
                    }
                    this.selectedLabel = this.multiSelectLabel;
                    this.$emit('input', this.multiSelected);
                    if (this.shouldCloseOnSelect) {
                        this.showDropdownFlag = false;
                        document.removeEventListener('click', this.handleClickOutside, true);
                        this.showFilteredDropdownFlag = false;
                    }
                } else {
                    this.selected = key;
                    this.selectedLabel = this.filteredValueset[key][this.labelfield];
                    this.showDropdownFlag = false;
                    document.removeEventListener('click', this.handleClickOutside, true);
                    this.showFilteredDropdownFlag = false;
                }
            },
            hideForm: function() {
                this.newElementData = {};
                this.errors = {};
                this.showForm = false;
            },
            showNewForm: function() {
                this.getFormData()
            },
            getValueset: function() {
                window.axios.get(this.lookupUrl, {params: {search: this.value}})
                    .then((response) => {
                        this.valueset = response.data;
                        if ((typeof(this.default) != 'undefined')
                            && (this.default != '')
                            && (this.default != -1)
                            && (this.default != null))
                        {
                            if (this.multiSelect) {
                                this.multiSelected = this.default;
                            } else {
                                this.selected = this.default;
                            }
                        }
                        if (this.value != null) {
                            if (this.multiSelect) {
                                this.multiSelected = this.value;
                            } else {
                                this.selected = this.findValuesetKeyItemById(this.value);
                            }
                        }
                        if (this.defaultValue != null) {
                            if (this.multiSelect) {
                                this.multiSelected = this.defaultValue;
                            } else {
                                this.selected = this.defaultValue;
                            }
                        }
                        if (this.newElementId != null) {
                            this.selected = this.findValuesetKeyItemById(this.newElementId);
                            this.newElementId = null;
                        }
                        if ((this.selected != -1) && (this.selected != null)) {
                            this.selectedLabel = this.valueset[this.selected][this.labelfield];
                        }
                    });
            },
            getFormData: function() {
                window.axios.get(this.formUrl)
                    .then((response) => {
                        this.newElementData = response.data;
                        this.showForm = true;
                    });
            },
            submitForm: function() {
                window.axios.post(this.storeUrl, this.formdata)
                    .then((response) => {
                        this.newElementId = response.data.id;
                        this.getValueset();
                        this.hideForm();
                        this.showDropdownFlag = false;
                        document.removeEventListener('click', this.handleClickOutside, true);

                    })
                    .catch((error) => {
                        if (error.response.status == 422) {
                            this.errors = error.response.data.errors;
                        }
                    });

            },
            handleClickOutside: function(e) {
                const el = this.$refs.container;
                if (!el.contains(e.target))
                    this.toggleDropdown();
            }
        },
        watch: {
            multiSelected: function() {
                this.$emit('input', this.multiSelected);
            },
            selected: function() {
                if (this.multiSelect) {
                    this.$emit('input', this.multiSelected);
                } else {
                    if (typeof(this.filteredValueset[this.selected]) != 'undefined') {
                        this.$emit('input', this.filteredValueset[this.selected].id);
                    } else {
                        this.$emit('input', -1);
                        this.selectedLabel = '';
                    }
                }
            },
            preselect: function() {
                if (this.preselect > 0) {
                    Vue.nextTick(() => this.$refs['valueset_' + this.preselect][0].scrollIntoView());
                }

            },
            searchterm: function() {
                this.preselect = -1;
            }
        }
    }
</script>
<style>

</style>