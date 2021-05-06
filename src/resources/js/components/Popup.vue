<template>
    <div v-show="visible"
         style="width: 100%; height: 100%; background-color: rgba(7,7,7,.3); display: flex; align-items: center; justify-content: center; position: fixed; top: 0px; left: 0px;"
         class="popup-inner"
         ref="popup-inner"
         v-bind:style="{'z-index': zIndex}"
         v-on:click.self="closePopup"
    >
        <div style="max-height: 90%; width: 80%; background-color: white; padding: 2em; box-shadow: 5px 5px rgba(7,7,7,.7);"
        >
            <slot></slot>
            <div style="width: 100%; display: flex; justify-content: center; padding-top: 1em; padding-bottom: 1em" v-if="showCloseButton">
                <button v-html="closeButtonLabel" :class="closeButtonClass"
                        v-on:click.self="closePopup"></button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            visible: {type: Boolean},
            showCloseButton: {type: Boolean, default: false},
            closeButtonLabel: {type: String, default: ''},
            closeButtonClass: {type: String, default: 'btn btn-primary'},
        },
        data: function() {
            return {
                zIndex: 0,
            }
        },
        methods: {
            closePopup: function() {
                this.$refs['popup-inner'].classList.remove('popup-inner-active');
                this.$emit('close')
            },
            maxParentZIndex: function() {
                if (!this.$refs['popup-inner']) {
                    return 0;
                }
                let result = 0;
                let maxZ = 0;
                let node = this.$refs['popup-inner'].parentNode;
                while ((node) && (node.nodeName != 'HTML')) {
                    maxZ = window.getComputedStyle(node).zIndex;
                    if ((maxZ != 'auto') && (parseInt(maxZ) > result)) {
                        result = maxZ;
                    }
                    node = node.parentNode;
                }

                return result;
            }
        },
        watch: {
            visible: function(value) {
                if (value) {
                    this.zIndex = parseInt(this.maxParentZIndex()) + 10;
                    window.setTimeout(() => {
                        this.$refs['popup-inner'].classList.add('popup-inner-active');
                    }, 100);
                } else {
                    this.zIndex = 0;
                }
            }
        },
        computed: {
        }
    }
</script>
<style>
    .popup-inner {
        opacity: 0;
        transition: opacity 300ms ease-in-out;
    }
    .popup-inner-active {
        opacity: 1;
    }
</style>
