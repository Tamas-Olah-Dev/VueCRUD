<template>
    <div v-show="visible"
         style="width: 100%; height: 100%; background-color: rgba(7,7,7,.3); display: flex; align-items: center; justify-content: center; position: fixed; z-index: 100; top: 0px; left: 0px;"
         class="popup-inner"
         ref="popup-inner"
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
            closeButtonClass: {type: String, default: 'btn btn-primary'}
        },
        methods: {
            closePopup: function() {
                this.$refs['popup-inner'].classList.remove('popup-inner-active');
                this.$emit('close')
            }
        },
        watch: {
            visible: function(value) {
                if (value) {
                    window.setTimeout(() => {
                        this.$refs['popup-inner'].classList.add('popup-inner-active');
                    }, 100);
                }
            }
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
