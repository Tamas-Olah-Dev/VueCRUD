<template>
    <div style="" class="checklist">
        <div style="width: 100%; margin-bottom: 2px; border-bottom: 1px solid lightgray; display: flex; justify-content: space-between">
            <label style="display: flex; align-items: center">
                <input type="checkbox" v-on:change="toggleAll" v-model="allSelected">&nbsp;{{ selectAllLabel }}
            </label>
            <slot></slot>
        </div>
        <div style="width: 100%; flex-grow: 1; overflow-y:auto" class="checklist-inner">
            <label v-for="item in valueset"
                   :key="item[idProperty]"
                   style="width: 100%; display:flex; align-items:start"
            >
                <input type="checkbox"
                       style="margin-right: 3px;"
                       :value="item[idProperty]"
                       v-bind:id="item[idProperty]"
                       v-model="selectedValues"
                       v-on:change="$emit('input', selectedValues)"
                       >&nbsp;{{ item[labelProperty] }}
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            value: {type: Array},
            idProperty: {type: String, default: 'id'},
            labelProperty: {type: String, default: 'name'},
            valueset: {type: Array},
            selectAllLabel: {type: String, default: 'Összes'}
        },
        data: function () {
            return {
                selectedValues: [],
            }
        },
        created: function() {
        },
        mounted() {
        },
        methods: {
            toggleAll: function() {
                if (this.allSelected) {
                    this.selectedValues = [];
                } else {
                    this.selectedValues = this.valueset.map((item) => {
                        return item[this.idProperty];
                    });
                }
                this.$emit('input', this.selectedValues);
            }
        },
        computed: {
            allSelected: {
                get: function() {
                    return this.selectedValues.length == this.valueset.length && this.selectedValues.length > 0;
                },
                set: function() {

                }
            }
        },
        watch: {
            value: function(value) {
                this.selectedValues = [...value];
            },
        }

    }
</script>
<style>
    .checklist {
        display:flex;
        flex-direction: column;
        max-height: 100%;
        padding: 3px;
        border: 1px solid darkgrey;
    }
    .checklist label {
        cursor: pointer;
    }
    .checklist-inner label {
        margin-bottom: .1rem
    }
    .checklist-inner label input {
        margin-top: .3em;
    }
    .checklist label:hover {
        background-color: darkgrey;
    }
</style>
