<template>
    <div style="display: flex; flex-direction: column; width: 100%">
        <div style="display: flex; flex-direction: row; align-items: center; justify-content: space-between; height: 2.5rem; margin-bottom: .25rem">
            <input type="color" style="width: 50%; flex-grow: 0; margin-right: .25rem; cursor:pointer; height: 100%" v-model="internalValue" v-on:change="updatePreset">
            <span v-html="valueToEmit" v-bind:style="{'background-color': rgbaString}"></span>
        </div>
        <div class="display: flex; align-items: center;">
            <label v-html="translate('Transparency')+':'"></label>
            <input type="range" min="0" max="1" step="0.1" v-model="colorData.a">
        </div>
        <select v-model="preset" v-on:change="updateInternalvalue" v-if="presets.length > 0">
            <option value="-1" v-html="presetDefault"></option>
            <option v-for="preset in presets"
                    v-html="preset.label"
                    v-bind:value="preset.value"></option>
        </select>
    </div>
</template>

<script>
    import {translateMixin} from "./mixins/translateMixin";

    export default {
        mixins: [translateMixin],
        props: {
            presets: {type: Array, default: () => {return []}},
            value: {type: String, default: null},
            mode: {type: String, default: 'rgba'}, //can be rgba or hex
            presetDefault: {type: String, default: 'Select a preset'},
        },
        data: function () {
            return {
                preset: -1,
                colorData: () => {
                    return {
                        r: 0,
                        g: 0,
                        b: 0,
                        a: 0,
                        v: '',
                    }
                },
                internalValue: '',
            }
        },
        mounted() {
            this.colorData = this.parseValue(this.value);
            this.internalValue = this.hexString;
        },
        methods: {
            parseValue: function(value) {
                if ((value != 'auto') && (value != null) && (value != '')) {
                    if (new RegExp(/#.{6}/).test(value)) {
                        return {
                            r: parseInt(value.substr(1, 2), 16),
                            g: parseInt(value.substr(3, 2), 16),
                            b: parseInt(value.substr(5, 2), 16),
                            a: 1,
                            v: '',
                        }
                    }
                    if (value.substr(0, 4) == 'rgba') {
                        let pieces = value.replace('rgba(', '').replace(')', '').split(',');
                        return {
                            r: parseInt(pieces[0]),
                            g: parseInt(pieces[1]),
                            b: parseInt(pieces[2]),
                            a: parseFloat(pieces[3]),
                        }
                    }
                    if (value.substr(0, 4) == 'rgb(') {
                        let pieces = value.replace('rgb(', '').replace(')', '').split(',');
                        return {
                            r: parseInt(pieces[0]),
                            g: parseInt(pieces[1]),
                            b: parseInt(pieces[2]),
                            a: 1,
                        }
                    }
                }
                return {
                    r: 0,
                    g: 0,
                    b: 0,
                    a: 0,
                    v: 'auto'
                }
            },
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
            },
            rgbaString: function() {
                if (this.colorData.v == 'auto') {
                    return 'auto';
                }
                return 'rgba('
                    +this.colorData.r
                    +', '
                    +this.colorData.g
                    +', '
                    +this.colorData.b
                    +', '
                    +this.colorData.a
                    +')';
            },
            hexString: function() {
                if (this.colorData.v == 'auto') {
                    return 'auto';
                }
                return '#'
                    +this.colorData.r.toString(16)
                    +this.colorData.g.toString(16)
                    +this.colorData.b.toString(16);
            },
            valueToEmit: function() {
                if (this.mode == 'rgba') {
                    return this.rgbaString;
                } else {
                    return this.hexString;
                }
            }
        },
        watch: {
            internalValue: function() {
                this.colorData = this.parseValue(this.internalValue);
                this.$emit('input', this.valueToEmit);
            },
            rgbaString: function() {
                this.$emit('input', this.valueToEmit);
            }
        }

    }
</script>
