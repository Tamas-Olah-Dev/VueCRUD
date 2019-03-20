<template>
    <div style="height:100%; min-height:100%">
        <div class="trix-wrapper-container">
            <input v-bind:id="fieldname+'-richtext'"
                   type="hidden"
                   :ref="fieldname+'-content'"
            >
            <div v-if="!trixReady" v-html="spinnerSrc"></div>
            <template  v-if="trixReady">
                <div class="trix-wrapper-custom-buttons-container">
                    <span class="trix-wrapper-button-group" style="width: 4em;">
                        <button class="trix-button"
                                type="button"
                                v-on:click="toggleViewMode"
                                v-html="viewModeLabel"
                        ></button>
                    </span>
                    <span class="trix-wrapper-button-group" style="width: 8em" v-show="viewMode == 'normal' && allowTableOperations == 'true'">
                        <button class="trix-button"
                                type="button"
                                v-on:click="insertTable"
                        >Táblázat beszúrása</button>
                    </span>
                    <span class="trix-wrapper-button-group" style="width: 10em" v-show="viewMode == 'normal' && allowTableOperations == 'true'">
                        <button class="trix-button"
                                type="button"
                                v-on:click="editTable"
                        >Táblázat szerkesztése</button>
                    </span>
                    <span class="trix-wrapper-button-group" style="width: 10em" v-show="viewMode == 'normal' && allowPreview == 'true'">
                        <button class="trix-button"
                                type="button"
                                v-on:click="openPreview"
                        >Előnézet</button>
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
            </template>
        </div>
        <div class="trix-wrapper-modal-overlay" v-if="showPopup && allowTableOperations == 'true'">
            <div class="trix-wrapper-modal">
                <trix-table-editor v-on:input="saveAttachment($event);showPopup=false;"
                                   v-on:cancel="showPopup=false"
                                   v-bind:value="currentTable"
                                   v-if="popupMode == 'table'"
                ></trix-table-editor>
            </div>
        </div>
    </div>
</template>

<script>
    import {fileUploadMixin} from './mixins/fileUploadMixin.js'
    import {spinner} from './mixins/spinner.js'
    export default {
        mixins: [fileUploadMixin, spinner],
        props: {
            fieldname: {type: String},
            value: {type: String, default: ''},
            trixCSSUrl: {type: String, default: '/js/plugins/trix/trix.css'},
            trixJSUrl: {type: String, default: '/js/plugins/trix/trix.hu.js'},
            ajaxOperationsUrl: {type: String, default: ''},
            allowPreview: {type: String, default: 'false'},
            allowTableOperations: {type: String, default: 'true'}
        },
        data: function() {
            return {
                latestValue: '',
                valueInitialized: false,
                viewMode: 'normal',
                codeValue: '',
                updatingCodeValue: false,
                trixReady: false,
                showPopup: false,
                popupMode: '',
                currentTable: [['']],
                currentSelection: [],
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
            },
        },
        mounted: function() {
            this.$refs[this.fieldname+'-content'].value = this.value;
            this.latestValue = this.value;
            this.codeValue = this.value;
            if (typeof(window.trixInitialized) == 'undefined') {
                window.trixInitialized = true;
                var csstag = document.createElement('link');
                csstag.setAttribute('href', this.trixCSSUrl);
                csstag.setAttribute('rel', 'stylesheet');
                var scripttag = document.createElement('script');
                scripttag.setAttribute('src', this.trixJSUrl);
                document.head.appendChild(csstag);
                document.head.appendChild(scripttag);
                window.trixIntervals = []
            }
            window.trixIntervals[this.fieldname] = window.setInterval(this.updateValue, 1000);
            if (this.ajaxOperationsUrl != '') {
                window.addEventListener("trix-attachment-add", (event) => {
                    if (event.srcElement.id == this.fieldname+'-richtext-trixeditor') {
                    this.uploadAttachment(event);
                }
            });
                window.addEventListener("trix-attachment-remove", (event) => {
                    if (event.srcElement.id == this.fieldname+'-richtext-trixeditor') {
                    this.removeAttachment(event);
                }
            });
            };
            window.setTimeout(() => {this.trixReady = true}, 1000);
            if (this.value = '') {
                this.valueInitialized = true;
            }
        },
        methods: {
            openPreview: function() {
                window.axios.post(this.ajaxOperationsUrl, {
                    action: 'trixGeneratePreview',
                    fieldName: this.fieldname,
                    content: this.$refs[this.fieldname+'-content'].value
                }).then((response) => {
                    let features = "menubar=no,location=no,resizable=yes,scrollbars=yes,status=yes";
                let previewWindow = window.open('', '_blank', features);
                let doc = previewWindow.document.open();
                doc.write(response.data);
                doc.close;
            })
            },
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
            }, (error) => {})
            },
            removeAttachment: function(event) {
                this.removeUploadedPublicFile(
                    this.ajaxOperationsUrl,
                    event.attachment.getAttribute('url'),
                    "trixRemoveAttachment"
                ).then((response) => {}, (error) => {});
            },
            insertTable: function() {
                this.currentTable = [['']];
                this.popupMode = 'table';
                this.showPopup = true;
            },
            editTable: function() {
                if (this.$refs[this.fieldname+'-editor'].editor.composition.editingAttachment != null) {
                    this.currentTable = this.parseTableStringToTabledata(this.$refs[this.fieldname+'-editor'].editor.composition.editingAttachment.getAttribute('content'));
                    this.popupMode = 'table';
                    this.showPopup = true;
                }
            },
            saveAttachment: function(attachment) {
                let editor = this.$refs[this.fieldname+'-editor'].editor;
                if (editor.composition.editingAttachment != null) {
                    this.currentSelection = editor.getSelectedRange();
                    editor.deleteInDirection("backward")
                }
                editor.insertAttachment(attachment);
            },
            parseTableStringToTabledata: function(tableString) {
                let result = [];
                let tempString = tableString.replace('<table>', '').replace('</table>', '');
                let rows = tempString.split('</tr><tr>');
                for (var rowIndex = 0; rowIndex < rows.length; rowIndex++) {
                    let newRow = [];
                    let columns = rows[rowIndex].replace('<tr>', '').replace('</tr>', '').split('</td><td>');
                    for (var columnIndex = 0; columnIndex < columns.length; columnIndex++) {
                        newRow.push(columns[columnIndex].replace('<td>', '').replace('</td>', ''));
                    }
                    result.push(newRow);
                }

                return result;
            }

        },
        watch: {
            value: function() {
                if (typeof(this.$refs[this.fieldname+'-editor']) != 'undefined') {
                    if (!this.valueInitialized) {
                        this.$refs[this.fieldname+'-editor'].editor.loadHTML(this.value);
                        this.valueInitialized = true;
                    }
                }
            },
            fieldname: function() {
                if (typeof(this.$refs[this.fieldname+'-editor']) != 'undefined') {
                    this.$refs[this.fieldname+'-editor'].editor.loadHTML(this.value);
                }
            }
        }

    }
</script>
<style>
    .trix-wrapper-container {
        min-height:310px;
        height: 100%;
        max-width: 100%;
    }
    .trix-wrapper-custom-buttons-container {
        height: 2.2em;
        display: flex;
        justify-content: space-between;
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
    .trix-wrapper-modal-overlay {
        z-index:1000;
        width: 100%;
        height: 100%;
        position: fixed;
        left: 0px;
        top: 0px;
        background-color: rgba(192,192,192,.5);
    }
    .trix-wrapper-modal {
        width: 85%;
        max-width: 85%;
        min-width: 85%;
        height: 80vh;
        max-height: 80vh;
        min-height: 80vh;
        left: 7%;
        top: 10vh;
        padding: 1em;
        background-color: white;
        box-shadow: 5px 5px darkgrey;
        position:absolute;
        display: flex;
        flex-direction: column;
    }

</style>
