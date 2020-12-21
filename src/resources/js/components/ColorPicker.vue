<template>
    <div style="display: flex; flex-direction: column; width: 100%">
        <div style="display: flex; flex-direction: row; align-items: center; justify-content: space-between; height: 2.5rem; margin-bottom: .25rem">
            <input type="color" style="width: 50%; flex-grow: 0; margin-right: .25rem; cursor:pointer; height: 100%" v-model="internalValue" v-on:change="updatePreset">
            <span v-html="internalValue" v-if="internalValue != -1"></span>
        </div>
        <select v-model="preset" v-on:change="updateInternalvalue" v-if="presets.length > 0">
            <option value="-1" v-html="presetDefault"></option>
            <option v-if="transparentOption" :value="transparentOption" v-html="transparentOption"></option>
            <option v-for="preset in presets"
                    v-html="preset.label"
                    v-bind:value="preset.value"></option>
        </select>
    </div>
</template>

<script>
    export default {
        props: {
            presets: {type: Array, default: () => {return []}},
            value: {type: String, default: '#ffffff'},
            presetDefault: {type: String, default: 'Select a preset'},
            transparentOption: {type: String, default: 'Transparent'},
        },
        data: function () {
            return {
                preset: -1,
                internalValue: '',
            }
        },
        mounted() {
            this.internalValue = this.value;
        },
        methods: {
            updatePreset: function() {
                this.preset = this.presetCodes.indexOf(this.internalValue);
            },
            updateInternalvalue: function() {
                this.internalValue = this.preset;
            }
        },
        computed: {
            presetCodes: function() {
                return this.presets.map((item) => {
                    return item.value;
                })
            }
        },
        watch: {
            internalValue: function() {
                this.$emit('input', this.internalValue);
            }
        }

    }
</script>
