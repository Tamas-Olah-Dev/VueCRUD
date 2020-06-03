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
    </div>
</template>

<script>
    export default {
        props: {
            value: {type: String, default: ''},
            customId: {type: String, default: Math.floor(Math.random() * 100000).toString()},
            colors: {type: Array, default: () => {return [];}}
        },
        data: function () {
            return {
                quill: null,
                editorRoot: null,
                innerValue: null,
                innerValueCopy: null,
                showCodePopup: false,
            }
        },
        mounted() {
            this.setValueAsRootHTML();
            this.resetQuill();
        },
        methods: {
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
            setValueAsRootHTML: function(value) {
                value = typeof(value) == 'undefined' ? this.value : value;
                this.$refs['quill-container'].innerHTML = value;
                this.innerValue = value;
            },
            resetQuill: function() {
                // this.$refs['quill-main-container'].querySelector('.quill-wrapper-quill-container').remove();
                // let node = document.createElement('DIV');
                // node.setAttribute('id', 'quill-container-'+this.customId);
                // node.setAttribute('ref', 'quill-container');
                // node.setAttribute('key', 'quill-container-'+this.customId);
                // node.classList.add('quill-wrapper-quill-container');
                // this.$refs['quill-main-container'].appendChild(node);
                // this.setValueAsRootHTML();
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

                                ['clean']                                         // remove formatting button
                            ],
                            handlers: {
                                'code-view': this.showCodeEditorPopup
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
</style>