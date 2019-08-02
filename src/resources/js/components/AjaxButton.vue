<template>
    <div v-if="showLoader"
         style="position: fixed; top:0px; left: 0px; z-index:500; width: 100%; height: 100vh; background-color: rgba(7,7,7,.3); display: flex; justify-content: center; align-items: center"
    ></div>
    <button v-else v-on:click="submitOperation"
            :title="title"
    >
        <slot></slot>
    </button>
</template>

<script>
    export default {
        props: {
            operationsUrl: {type: String},
            subject: {},
            action: {type: String},
            title: {type: String, default: ''},
            successEvent: {type: String, default: 'submit-success'},
            failureEvent: {type: String, default: 'submit-failed'},
        },
        data: function () {
            return {
                showLoader: false,
            }
        },
        mounted() {
        },
        methods: {
            submitOperation: function() {
                this.showLoader = true;
                window.axios.post(this.operationsUrl, {
                    action: this.action,
                    subject: this.subject,
                }).then((response) =>{
                    this.showLoader = false;
                    this.$emit(this.successEvent, response.data);
                }).catch((error) => {
                    this.showLoader = false;
                    this.$emit(this.failureEvent, error.response.data);
                })
            }
        },
        computed: {},
        watch: {}

    }
</script>
