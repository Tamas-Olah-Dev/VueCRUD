<template>
    <label :for="'checkbox-'+uid">
        <input type="checkbox" v-model="state" :name="'checkbox-'+uid">
        <span style="margin-left:.5em">
            <slot></slot>
            <span v-html="labelContent"></span>
        </span>

    </label>
</template>

<script>
    export default {
        props: {
            subject: {type: Object},
            url: {type: String},
            action: {type: String},
            value: {type: Boolean, default: false},
            labelContent: {type: String, default: ''}
        },
        data: function() {
            return {
                state: false,
                initialized: false,
                uid: Math.ceil(Math.random() * 10000000000000)
            }
        },
        mounted() {
            this.state = this.value;
            this.$watch('state', function() {
                window.axios.post(this.url, {
                    action: this.action,
                    subject: this.subject,
                    state: this.state
                }).then((response) => {
                    this.$emit('input', this.state);
                }).catch((error) => {
                    console.log(error.response);
                    alert(error.response.data);
                });
            });
        },
    }
</script>
