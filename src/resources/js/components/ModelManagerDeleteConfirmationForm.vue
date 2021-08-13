<template>
    <div :class="getCSSClass('model-manager-delete-confirmation-form')">
        <span v-html="label"></span>
        <div :class="getCSSClass('model-manager-delete-confirmation-form-buttons-container')">
            <button type="button"
                    v-if="buttons['confirmDeletion']"
                    :class="getCSSClass('model-manager-confirmDeletion-button')"
                    v-html="buttons['confirmDeletion']['html']"
                    @click="confirmDeletion"
                    :disabled="loading"
            ></button>
            <button type="button"
                    v-if="buttons['cancelDeletion']"
                    :class="getCSSClass('model-manager-cancelDeletion-button')"
                    v-html="buttons['cancelDeletion']['html']"
                    @click="cancelDeletion"
            ></button>

        </div>
    </div>
</template>

<script>
    export default {
        inject: ['loadingIndicator', 'getCSSClass', 'translate'],
        props: {
            label: {type: String},
            buttons: {type: Object},
        },
        data: function () {
            return {
                loading: false,
            }
        },
        mounted() {
        },
        methods: {
            confirmDeletion: function() {
                this.loading = true;
                this.$emit('confirmed');
            },
            cancelDeletion: function() {
                this.loading = true;
                this.$emit('canceled');
            }
        },
        computed: {},
        watch: {}

    }
</script>
