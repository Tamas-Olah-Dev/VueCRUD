<template>
    <div :class="getCSSClass('model-manager-header-buttons-container')">
        <button type="button"
                v-if="buttons['add']"
                :class="getCSSClass('model-manager-new-button')"
                @click="emitNew"
                v-html="buttons['add']['html']"></button>
    </div>
</template>

<script>
    export default {
        inject: ['getCSSClass', 'loadingIndicator', 'icon', 'locale'],
        props: {
            buttons: {type: Object},
        },
        data: function () {
            return {}
        },
        mounted() {
        },
        methods: {
            emitNew: function() {
                this.$emit('new');
            }
        },
        computed: {},
        watch: {}

    }
</script>
