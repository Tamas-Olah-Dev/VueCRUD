<template>
    <div class="trix-wrapper-container">
        <input v-bind:id="fieldname+'-richtext'"
               type="hidden"
               :ref="fieldname+'-content'"
        >
        <div class="trix-wrapper-custom-buttons-container">
            <span class="trix-wrapper-button-group" style="width: 4em;">
                <button class="trix-button"
                        type="button"
                        v-on:click="toggleViewMode"
                        v-html="viewModeLabel"
                ></button>
            </span>
        </div>
        <div v-show="viewMode == 'normal'" style="height: 85%" v-bind:id="fieldname+'-richtext-trixeditor-container'">
            <trix-editor v-bind:input="fieldname+'-richtext'"
                         class="editform-richtext-editor"
                         v-bind:id="fieldname+'-richtext-trixeditor'"
                         v-bind:trix-id="fieldname+'-richtext-trixeditor'"
                         :ref="fieldname+'-editor'"
                         style="min-height:300px; height: 100%"
            ></trix-editor>
        </div>
        <div v-show="viewMode == 'code'"  style="height: 85%">
            <textarea style="width: 100%; height: 100%; min-height: 210px"
                      v-model="codeValue"
            >
            </textarea>
        </div>
    </div>
</template>

<script>
    import {fileUploadMixin} from './mixins/fileUploadMixin.js'
    export default {
        mixins: [fileUploadMixin],
        props: {
            fieldname: {type: String},
            value: {type: String, default: ''},
            trixCSSUrl: {type: String, default: '/js/plugins/trix/trix.css'},
            trixJSUrl: {type: String, default: '/js/plugins/trix/trix.hu.js'},
            ajaxOperationsUrl: {type: String, default: ''}
        },
        data: function() {
            return {
                latestValue: '',
                valueInitialized: false,
                viewMode: 'normal',
                codeValue: '',
                updatingCodeValue: false
            }
        },
        computed: {
            viewModeLabel: function() {
                if (this.viewMode == 'code') {
                    return 'Szöveg';
                }
                if (this.viewMode == 'normal') {
                    return 'Kód';
                }
            }
        },
        mounted: function() {
            this.latestValue = this.value;
            this.codeValue = this.value;
            var csstag = document.createElement('link');
            csstag.setAttribute('href', this.trixCSSUrl);
            csstag.setAttribute('rel', 'stylesheet');
            var scripttag = document.createElement('script');
            scripttag.setAttribute('src', this.trixJSUrl);
            document.head.appendChild(csstag);
            document.head.appendChild(scripttag);
            window.trixIntervals = []
            window.trixIntervals[this.fieldname] = window.setInterval(this.updateValue, 1000);
            if (this.ajaxOperationsUrl != '') {
                window.addEventListener("trix-attachment-add", (event) => {
                    this.uploadAttachment(event);
                });
                window.addEventListener("trix-attachment-remove", (event) => {
                    this.removeAttachment(event);
                });
            };
            this.$refs[this.fieldname+'-content'].value = this.value;
            if (this.value == '') {
                this.valueInitialized = true;
            }
        },
        methods: {
            updateValue: function() {

                if (typeof(this.$refs[this.fieldname+'-content']) == 'undefined') {
                    window.clearInterval(window.trixIntervals[this.fieldname]);
                } else {
                    if (this.$refs[this.fieldname+'-content'].value != this.latestValue) {
                        let value = this.$refs[this.fieldname+'-content'].value;
                        this.$emit('input', value);
                        this.latestValue = value;
                    }
                }
            },
            toggleViewMode: function() {
                if (this.viewMode == 'normal') {
                    this.codeValue = this.$refs[this.fieldname+'-content'].value;
                    this.viewMode = 'code';
                    return;
                }
                if (this.viewMode == 'code') {
                    this.viewMode = 'normal';
                    this.$refs[this.fieldname+'-editor'].editor.loadHTML(this.codeValue);
                    return;
                }
            },
            uploadAttachment: function(event) {
                this.uploadPublicFileToVueCRUDController(
                    this.ajaxOperationsUrl,
                    event.attachment.getFile(),
                    "trixStoreAttachment"
                ).then((response) => {
                    event.attachment.setAttributes({url: response.data.url});
                })
            },
            removeAttachment: function(event) {
                this.removeUploadedPublicFile(
                    this.ajaxOperationsUrl,
                    event.attachment.getAttribute('url'),
                    "trixRemoveAttachment"
                );
            }
        },
        watch: {
            value: function() {
                if (!this.valueInitialized) {
                    this.$refs[this.fieldname+'-editor'].editor.loadHTML(this.value);
                }
                this.valueInitialized = true;
            },
            fieldname: function() {
                this.$refs[this.fieldname+'-editor'].editor.loadHTML(this.value);
            }
        }

    }
</script>
<style>
    .trix-wrapper-container {
        min-height:310px;
        height: 100%
    }
    .trix-wrapper-custom-buttons-container {
        height: 2.2em;
    }
    .trix-wrapper-button-group {
        display: flex;
        margin-bottom: 10px;
        border: 1px solid #bbb;
        border-top-color: #ccc;
        border-bottom-color: #888;
        border-radius: 3px;
        height: 1.6em;
    }
    .trix-wrapper-custom-buttons-container > .trix-wrapper-button-group > button {
        position: relative;
        float: left;
        color: rgba(0, 0, 0, 0.6);
        font-size: 0.75em;
        font-weight: 600;
        white-space: nowrap;
        padding: 0 0.5em;
        margin: 0;
        outline: none;
        border: none;
        border-bottom: 1px solid #ddd;
        border-radius: 0;
        background: transparent;
    }

</style>
