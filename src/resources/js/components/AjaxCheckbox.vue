<template>
    <label :for="'checkbox-'+uid">
        <input type="checkbox" v-model="state" :name="'checkbox-'+uid" :id="'checkbox-'+uid">
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
            labelContent: {type: String, default: ''},
            componentId: {}
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
            if (!this.initialized) {
                this.initialized = true;
                this.$watch('state', function() {
                    window.axios.post(this.url, {
                        action: this.action,
                        subject: this.subject,
                        state: this.state,
                        componentId: this.componentId
                    }).then((response) => {
                        this.$emit('input', this.state);
                    }).catch((error) => {
                        console.log(error.response);
                        alert(error.response.data);
                    });
                });
            }
        },
    }
</script>
