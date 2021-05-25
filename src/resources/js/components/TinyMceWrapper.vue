<template>
    <editor v-model="internalValue" :init="tinyinit"></editor>
</template>

<script>
    //npm install --save "@tinymce/tinymce"
    //npm install --save "@tinymce/tinymce-vue@^3"
    // in main js entry point:
    // require('tinymce');
    // require('tinymce/plugins/link')
    // require('tinymce/plugins/lists')
    // require('tinymce/plugins/advlist')
    // require('tinymce/plugins/table')
    // require('tinymce/plugins/code')
    // require('tinymce/plugins/image')
    // require('tinymce/themes/silver')
    // require('tinymce/icons/default')

    import Editor from '@tinymce/tinymce-vue';
    export default {
        components: {
            'editor': Editor
        },
        props: {
            operationsUrl: {type: String},
            value: {type: String, default: ''},
            minHeight: {type: Number, default: 300},
            componentId: {type: Number, default: () => Math.ceil(Math.random() * 1000000)}
        },
        data: function () {
            return {
                internalValue: '',
                emitTimer: 0,
            }
        },
        created() {
            this.internalValue = this.value;
        },
        mounted() {},
        methods: {},
        computed: {
            tinyinit: function() {
                return {
                    height: this.minHeight,
                    plugins: [
                        'link image lists table code'
                    ],
                    toolbar: 'undo redo | styleselect | bold italic | ' +
                        'bullist numlist outdent indent | link image | print preview media fullpage | ' +
                        'forecolor backcolor code image',
                    menubar: 'edit view format',
                    content_css: '/css/app.css'
                }
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


