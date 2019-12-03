<template>
        <select v-model="state" :name="'select-'+uid" :id="'select-'+uid" class="form-control ajax-select">
            <option v-for="optionLabel, optionId in valueset"
                    v-html="optionLabel"
                    v-bind:value="optionId"></option>
        </select>
</template>

<script>
    export default {
        props: {
            subject: {type: Object},
            url: {type: String},
            action: {type: String},
            value: {type: Number, default: -1},
            labelContent: {type: String, default: ''},
            componentId: {},
            valueset: {type: Object}
        },
        data: function() {
            return {
                state: -1,
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
