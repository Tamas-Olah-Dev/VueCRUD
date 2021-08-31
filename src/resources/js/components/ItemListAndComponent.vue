<template>
    <div :class="getCSSClass('item-list-container')">
        <div :class="getCSSClass('item-list-items-container')">
            <transition-group name="item-list"
                              tag="span"
                              :class="getCSSClass('item-list-items-container-inner')"
            >
                <span v-for="label, id in valueLabels"
                      :key="label+'-'+id"
                      :class="getCSSClass('item-list-item')"
                      >
                    <span :class="getCSSClass('item-list-item-label')"
                          v-html="label"></span>
                    <button :class="getCSSClass('item-list-item-remove-button')"
                            type="button"
                          @click="removeItem(id)"
                          v-html="icon('close')"></button>
                </span>
            </transition-group>
        </div>
        <div :class="getCSSClass('item-list-component-container')">
            <label :class="getCSSClass('item-list-component-label')"
                   v-html="label"
            ></label>
            <component :is="component"
                       v-on:input="addItem($event)"
                       v-on:addtovalueset="addItemToValueset($event)"
                       v-on:valueset="setValueset($event)"
                       v-bind="componentPropsFiltered"></component>
        </div>

    </div>
</template>

<script>
    export default {
        inject: ['loadingIndicator', 'getCSSClass', 'translate', 'icon'],
        props: {
            value: {type: Array},
            label: {type: String},
            mode: {type: String, default: 'object'}, //'object' or 'string'
            basename: {type: Boolean, default: false}, // for filepicker components, use only filename as labels while retaining the full path
            idProperty: {type: String, default: 'id'},
            labelProperty: {type: String, default: 'name'},
            component: {type: String},
            componentValuesetKey: {type: String, default: 'valueset'},
            componentProps: {type: Object},
        },
        data: function () {
            return {
                additionalValuesetItems: [],
            }
        },
        mounted() {
        },
        methods: {
            removeItem: function(item) {
                let newValue = this.value.filter((i) => {
                    return i != item;
                });
                this.$emit('input', newValue);
            },
            addItem: function(item) {
                let newValue = this.value;
                newValue.push(item);
                this.$emit('input', newValue);
            },
            addItemToValueset: function(item) {
                this.additionalValuesetItems.push(item);
            },
            setValueset: function(valueset) {
                this.additionalValuesetItems = valueset;
            },

            getBasename: function(string) {
                let pieces = string.split('/');
                return pieces.pop();
            }
        },
        computed: {
            valueLabels: function() {
                let result = {};
                this.value.forEach((i) => {
                    if (this.mode == 'string') {
                        result[i] = this.basename ? this.getBasename(i) : i;
                    } else {
                        if (this.keyedItems[i]) {
                            result[i] = this.basename ? this.getBasename(this.keyedItems[i]) : this.keyedItems[i];
                        }
                    }
                });
                return result;
            },
            componentPropsFiltered: function() {
                let result = {...this.componentProps};
                if (result[this.componentValuesetKey]) {
                    this.additionalValuesetItems.forEach((i) => {
                        result[this.componentValuesetKey].push(i);
                    })
                    result[this.componentValuesetKey] = this.componentProps[this.componentValuesetKey].filter((i) => {
                        return !this.value.includes(i[this.idProperty]);
                    });
                }
                return result;
            },
            keyedItems: function() {
                let result = {};
                if (this.componentProps[this.componentValuesetKey]) {
                    this.componentProps[this.componentValuesetKey].forEach((i) => {
                        result[i[this.idProperty]] = i[this.labelProperty];
                    });
                }
                this.additionalValuesetItems.forEach((i) => {
                    result[i[this.idProperty]] = i[this.labelProperty];
                })
                return result;
            }
        },
        watch: {
        }

    }
</script>
<style>
    .item-list-enter-active, .item-list-leave-active {
        transition: opacity 100ms, translate 100ms;
    }
    .item-list-enter, .item-list-leave-to {
        opacity: 0;
        translate: scaleX(0);
    }
</style>