<template>
    <div :class="getCSSClass('model-manager-filters-inner-container')">
        <div v-bind:class="getCSSClass('model-manager-filters-heading')"
             v-if="filtersExist"
        >
            <div>
                <span v-html="icon('vuecrud-filter')"></span>
                {{ translate('Filters') }}
            </div>
            <button v-bind:class="getCSSClass('model-manager-resetFilters-button')"
                    v-on:click="resetFilters"
                    v-html="buttons['resetFilters']['html']"
            ></button>
        </div>
        <div class="portlet-body model-manager-filters-list-container"
             v-on:keyup.enter="emitValue"
             v-bind:class="getCSSClass('model-manager-filters-body', 'model-manager-filters-body')"
             v-if="filtersExist"
        >
            <tabgroup :tabs="filterTabs" v-on:tab-changed="resetFilters">
                <template v-for="tabFilters, index in filterTabContents"
                          v-slot:[index]
                >
                    <div :class="getCSSClass('model-manager-filters-list')">
                        <div v-for="filterData, filterName in tabFilters"
                             :class="getCSSClass('model-manager-filter-block') + ' ' + filterData['containerClass']"
                        >
                            <label v-html="filterData['label']"></label>
                            <datepicker v-if="filterData['type'] == 'datepicker'"
                                        :locale="locale"
                                        v-model="filterData['value']"
                            ></datepicker>
                            <input v-if="filterData['type'] == 'text'"
                                   type="text"
                                   v-model="filterData['value']"
                            >
                            <select v-if="filterData['type'] == 'select'"
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
                    <div v-if="!autoFilter" :class="getCSSClass('model-manager-filter-button-container')">
                        <button
                                :class="getCSSClass('model-manager-filter-button')"
                                v-html="buttons['search']['html']"
                                v-on:click="emitValue"
                        ></button>
                    </div>

                </template>
            </tabgroup>
        </div>
    </div>
</template>

<script>
    export default {
        inject: ['getCSSClass', 'loadingIndicator', 'icon', 'translate', 'locale'],
        props: {
            value: {type: Object, default: () => {return {}}},
            autoFilter: {type: Boolean, default: false},
            buttons: {type: Object, default: () => {return {}}},
        },
        data: function () {
            return {
                filters: {},
            }
        },
        mounted() {
            this.filters = {...this.value};
        },
        methods: {
            emitValue: function() {
                this.$emit('input', this.filters);
            },
            resetFilters: function() {
                Object.keys(this.filters).forEach((filter) => {
                    this.filters[filter].value = this.filters[filter].default;
                });
                this.emitValue();
            },

        },
        computed: {
            filtersExist: function() {
                if (['{}', '[]'].includes(JSON.stringify(this.filters))) {
                    return false;
                }
                if (typeof this.filters === 'object') {
                    return Object.keys(this.filters).filter((k) => {
                        return !this.filters[k].containerClass.includes('hidden');
                    }).length > 0;
                }
                return this.filters.filter((f) => {
                    return !f.containerClass.includes('hidden');
                }).length > 0;

            },
            filterTabs: function() {
                if (JSON.stringify(this.filters) == '{}') {
                    return [];
                }
                let result = [];
                Object.keys(this.filters).forEach((key) => {
                    if (!result.includes(this.filters[key].tab)) {
                        result.push(this.filters[key].tab);
                    }
                });
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
            },
        },
        watch: {
            value: function() {
                this.filters = {...this.value};
            }
        }

    }
</script>
