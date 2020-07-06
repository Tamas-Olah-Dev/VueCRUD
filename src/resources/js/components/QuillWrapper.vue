<template>
    <div :ref="'quill-main-container'">
        <div :ref="'quill-container'"
             :id="'quill-container-'+customId"
             :key="'quill-container-'+customId"
             class="quill-wrapper-quill-container"
             style="width: 100%; height: 100%;"
        ></div>
        <div v-if="showCodePopup" style="width: 100vw; height: 100vh; position: fixed; z-index: 50; display: flex; align-items: center; justify-content: center; background-color: rgba(7,7,7,.6); top: 0px; left: 0px;">
            <div style="width: 80%; height: 80%; display: flex; flex-direction: column; background-color: white; padding: 1em">
                <textarea v-model="innerValueCopy" style="width: 100%; height: 80%;"></textarea>
                <div style="width: 100%; display: flex; align-items:center; justify-content:space-between; padding: 1em;">
                    <button v-on:click="storeCodeChanges">Mentés</button>
                    <button v-on:click="cancelCodeChanges">Mégse</button>
                </div>
            </div>
        </div>
        <input style="opacity:0; width: 0px; height: 0px;"
               type="file"
               :ref="'quill-attachment-'+customId"
               multiple="false"
               v-on:change="storeAttachment">
    </div>
</template>

<script>
    import {fileUploadMixin} from './mixins/fileUploadMixin.js'
    import {translateMixin} from './mixins/translateMixin.js'
    import {spinner} from './mixins/spinner.js'
    export default {
        mixins: [fileUploadMixin, spinner, translateMixin],
        props: {
            value: {type: String, default: ''},
            customId: {type: String, default: Math.floor(Math.random() * 100000).toString()},
            colors: {type: Array, default: () => {return [];}},
            ajaxOperationsUrl: {type: String, default: ''}
        },
        data: function () {
            return {
                quill: null,
                editorRoot: null,
                innerValue: null,
                innerValueCopy: null,
                showCodePopup: false,
                attachment: null,
            }
        },
        mounted() {
            this.setValueAsRootHTML();
            this.resetQuill();
        },
        methods: {
            storeAttachment: function(event) {
                if (event.target.files.length > 0) {
                    this.uploadPublicFileToVueCRUDController(
                        this.ajaxOperationsUrl,
                        event.target.files[0],
                        "trixStoreAttachment"
                    ).then((response) => {
                        var range = this.quill.getSelection();
                        if (range) {
                            this.quill.insertText(range.index, this.translate('Letöltés'), 'link', response.data.url);
                        }
                    }, (error) => {
                        console.log(error);
                    })
                }
            },
            storeCodeChanges: function() {
                //this.setValueAsRootHTML(this.innerValueCopy);
                this.quill.clipboard.dangerouslyPasteHTML(this.innerValueCopy);
                this.innerValue = this.editorRoot.innerHTML
                this.showCodePopup = false;
                this.quill.enable();
            },
            cancelCodeChanges: function() {
                this.innerValue = this.value;
                this.showCodePopup = false;
                this.quill.enable();
            },
            showCodeEditorPopup: function() {
                this.quill.disable();
                this.innerValueCopy = this.innerValue;
                this.showCodePopup = true;
            },
            showAttachmentPopup: function() {
                this.$refs['quill-attachment-'+this.customId].click();
            },
            setValueAsRootHTML: function(value) {
                value = typeof(value) == 'undefined' ? this.value : value;
                this.$refs['quill-container'].innerHTML = value;
                this.innerValue = value;
            },
            resetQuill: function() {
                this.quill = new Quill('#quill-container-'+this.customId, {
                    theme: 'snow',
                    modules: {
                        toolbar: {
                            'container': [
                                ['bold', 'italic', 'underline', 'strike'],
                                ['blockquote', 'code-block'],

                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                [{ 'script': 'sub'}, { 'script': 'super' }],
                                [{ 'indent': '-1'}, { 'indent': '+1' }],
                                [{ 'size': ['small', false, 'large', 'huge'] }],
                                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                                [{ 'color': this.colors }, { 'background': this.colors }],          // dropdown with defaults from theme
                                [{ 'align': [] }],
                                ['code-view'],
                                ['image'],
                                ['attachment'],

                                ['clean']                                         // remove formatting button
                            ],
                            handlers: {
                                'code-view': this.showCodeEditorPopup,
                                'attachment': this.showAttachmentPopup
                            }
                        }
                    }
                });
                this.quill.on('text-change', (event) => {
                    this.emitValue();
                });
                this.editorRoot = this.$refs['quill-container'].querySelector('.ql-editor');
            },
            emitValue: function() {
                this.innerValue = this.editorRoot.innerHTML;
                this.$emit('input', this.innerValue);
            }
        },
        computed: {},
        watch: {
            value: function() {
                if (this.value != this.innerValue) {
                    this.setValueAsRootHTML();
                    this.resetQuill();
                }
            }
        }

    }
</script>
<style>
    button.ql-code-view::after {
        content: '@'
    }
    button.ql-attachment::after {
        content: '🗎'
    }
    .ql-editor {
        min-height: 300px;
        max-height: 500px;
        overflow-y: auto;
        padding-bottom: 2rem;
    }

</style>
