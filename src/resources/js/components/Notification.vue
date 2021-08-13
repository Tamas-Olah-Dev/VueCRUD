<template>
    <div style="transform-origin: top center; transition: transform 200ms ease-in-out"
         :style="{'transform': show ? 'scaleY(1)' : 'scaleY(0)'}"
         :class="positionClass+' '+colorClass"
    >
        <div v-html="message"></div>
        <button style="margin-top: 1rem"
                type="button"
                :class="buttonClass"
                @click="hideMessage"
                v-html="closeButtonLabel"></button>
    </div>
</template>

<script>
    export default {
        props: {
            positionClass: {type: String, default: 'z-50 fixed top-0 left-0 w-full p-8 flex flex-col items-center justify-center border shadow-lg'},
            colorClass: {type: String, default: 'bg-white'},
            buttonClass: {type: String, default: ''},
            message: {type: String},
            closeButtonLabel: {type: String},
            timeout: {type: Number, default: 3000},
            display: {type: Number, default: 0}
        },
        data: function () {
            return {
                show: false,
                timer: 0,
            }
        },
        mounted() {
        },
        methods: {
            displayMessage: function() {
                this.show = true;
                if (this.timeout > 0) {
                    this.timer = window.setTimeout(() => {
                        this.hideMessage();
                    }, this.timeout);
                }
            },
            hideMessage: function() {
                window.clearTimeout(this.timer);
                this.show = false;
            }
        },
        computed: {},
        watch: {
            display: function() {
                this.displayMessage();
            }
        }

    }
</script>
