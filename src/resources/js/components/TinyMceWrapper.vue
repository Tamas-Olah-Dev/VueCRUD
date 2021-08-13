<template>
    <editor v-model="internalValue" :init="tinyinit"></editor>
</template>

<script>
    import Editor from '@tinymce/tinymce-vue';
    export default {
        components: {
            'editor': Editor
        },
        props: {
            ajaxOperationsUrl: {type: String},
            value: {type: String, default: ''},
            minHeight: {type: Number, default: 300},
            colors: {type: Object, default: {}},
            componentId: {type: Number, default: () => Math.ceil(Math.random() * 1000000)}
        },
        data: function () {
            return {
                internalValue: '',
                emitTimer: 0,
                tinyColorset: {},
            }
        },
        created() {
            this.internalValue = this.value;
            if (Object.keys(this.colors).length > 0) {
                let result = [];
                Object.keys(this.colors).forEach((key) => {
                    result.push(this.colors[key].value);
                    result.push(this.colors[key].label);
                })
                this.tinyColorset = result;
            }

        },
        mounted() {},
        methods: {},
        computed: {
            tinyinit: function() {
                let result = {
                    height: this.minHeight,
                    plugins: [
                        'link image lists table code'
                    ],
                    toolbar: [
                        'undo redo | styleselect | bold italic | bullist numlist outdent indent',
                        'link image  | forecolor backcolor code '
                    ],
                    menubar: 'edit view format',
                    content_css: '/css/app.css',
                }
                if (Object.keys(this.colors).length > 0) {
                    result.color_map = this.tinyColorset;
                }

                return result;
            }
        },
        watch: {
            internalValue: function () {
                window.clearTimeout(this.emitTimer);
                this.emitTimer = window.setTimeout(() => {
                    this.$emit('input', this.internalValue)
                })

            }
        }

    }
</script>
