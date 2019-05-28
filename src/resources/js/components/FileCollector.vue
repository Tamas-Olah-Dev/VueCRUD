<template>
    <div>
        <div v-bind:class="collectorClass"
             v-if="!uploading"
             :ref="'collector'"
             v-on:dragenter.prevent="addDropClass = true"
             v-on:dragover.prevent="addDropClass = true"
             v-on:dragexit.prevent="addDropClass = false"
             v-on:dragleave.prevent="addDropClass = false"
             v-on:drop.prevent="collect($event)"
        >
            {{ collectorLabel }}
            <div style="display:flex; flex-wrap:wrap">
        <span v-for="file, index in files"
              v-bind:class="getClassOverrideOrDefaultClass('file-collector-file-span', 'file-collector-file-span')"
        >
            {{ filenameLabel(file) }}
            <span v-on:click="removeFile(index)"
                  v-on:keydown.enter="removeFile(index)"
                  role="button"
                  class="file-collector-remove-button">X</span>
        </span>
                <span v-for="pendingFile in pendingFiles" v-html="spinnerSrc">
                </span>
            </div>
            <div class="file-collector-add-button-container" v-if="this.limit == null || this.limit > this.combinedLength">
                <label :for="'fileinput'+token">
                    <input :name="'fileinput'+token"
                           :ref="'fileinput'"
                           :id="'fileinput'+token"
                           v-on:change="addFileFromInput"
                           type="file"
                           v-bind:accept="accept"
                           style="position:absolute; opacity: 0; width: 0px">
                    <span :class="buttons.fileUpload.class"
                          v-html="buttons.fileUpload.html"></span>
                </label>
            </div>
        </div>
        <div v-if="uploading">
            Feltöltés... (hátravan {{ pendingFilesLength }} fájl)
        </div>
    </div>
</template>

<script>
    import {fileUploadMixin} from './mixins/fileUploadMixin.js'
    import {spinner} from './mixins/spinner.js'
    import {classOverridesMixin} from './mixins/classOverridesMixin.js'
    import {translateMixin} from './mixins/translateMixin.js'
    export default {
        mixins: [fileUploadMixin, spinner, classOverridesMixin, translateMixin],
        props: {
            uploadUrl: {type: String, default: ''},
            value: {type: Array, default: () => {return [];}},
            buttons: {type: Object, default: () => {
                return {
                    fileUpload: {
                        class: 'btn btn-outline-primary',
                        html: '+'
                    }
                }
            }},
            limit: {default: null},
            mode: {default: 'url'},
            accept: {default: false},
            token: {type: String, default: () => Math.random().toString().substr(3, 8)}
        },
        data: function() {
            return {
                files: [],
                defaultUploadUrl: '',
                uploading: false,
                addDropClass: false,
                pendingFiles: [],
                acceptErrorMessage: this.translate('Invalid file type'),
            }
        },
        mounted() {
            this.files = this.value.slice();
        },
        computed: {
            pendingFilesLength: function() {
                return this.pendingFiles.length;
            },
            combinedLength: function() {
                return this.pendingFiles.length + this.files.length;
            },
            collectorLabel: function() {
                return this.files.length == 0
                    ? 'Húzzon ide fájlokat vagy kattintson a + gombra megnyitáshoz'
                    : '';
            },
            collectorClass: function() {
                let result = 'file-collector-container';
                if (this.addDropClass) {
                    result = result + ' file-collector-container-focused'
                }

                return result;
            }
        },
        methods: {
            filelistCompliesWithAccept: function(filelist) {
                if (this.accept === false) {
                    return true;
                }
                let result = true;
                let acceptArray = this.accept.join(',').split(',');
                for (var i = 0; i < filelist.length; i++) {
                    let fileparts = filelist[i].name.split('.');
                    if ((acceptArray.indexOf(filelist[i].type) == -1)
                        && (acceptArray.indexOf(fileparts[fileparts.length - 1]) == -1)) {
                        result = false;
                    }
                }

                return result;
            },
            uploadFiles: function() {
                this.uploading = true;
                this.uploadFirstFile();
            },
            uploadFirstFile: function() {
                this.uploadPublicFileToVueCRUDController(
                    this.uploadUrl,
                    this.pendingFiles[0],
                    'storePublicAttachment',
                    this.mode
                ).then((response) => {
                    if (this.mode == 'url') {
                        this.files.push(response.data.url);
                    } else {
                        this.files.push(response.data);
                    }

                    this.pendingFiles.splice(0, 1);
                    this.$emit('input', this.files);
                    if (this.pendingFiles.length == 0) {
                        this.uploading = false;
                    } else {
                        this.uploadFirstFile();
                    }
                });
            },
            filenameLabel: function(file) {
                let filename = '';
                if (this.mode == 'url') {
                    filename = file;
                } else {
                    filename = file.name;
                }
                if (filename.includes('/')) {
                    let parts = filename.split('/');
                    filename = parts[parts.length - 1];
                }
                if (filename.includes('___')) {
                    filename = filename.substr(filename.indexOf('___')+3);
                }
                if (filename.length > 30) {
                    return filename.substring(0, 12)
                        +'(...)'
                        +filename.substring(filename.length - 12, 12);
                } else {
                    return filename;
                }
            },
            collectFiles: function(filelist) {
                if ((this.limit != null) && (filelist.length + this.combinedLength > this.limit)) {
                    alert('Maximum '+this.limit+' fájl tölthető fel!')
                    return false;
                }
                if (!this.filelistCompliesWithAccept(filelist)) {
                    alert(this.acceptErrorMessage);
                    return false;
                }
                for (var i = 0; i < filelist.length; i++) {
                    this.pendingFiles.push(filelist[i]);
                }
                if (!this.uploading) {
                    this.uploadFiles();
                }
                //this.$emit('input', this.files);
            },
            collect: function(event) {
                this.addDropClass = false;
                this.collectFiles(event.dataTransfer.files);
            },
            addFileFromInput: function() {
                this.collectFiles(this.$refs.fileinput.files);
            },
            removeFile: function(index) {
                let target = this.mode == 'url'
                    ? this.files[index]
                    : this.files[index]['id'];
                this.removeUploadedPublicFile(
                    this.uploadUrl,
                    target,
                    'removePublicAttachment',
                    this.mode
                )
                    .then((response) => {
                        this.files.splice(index, 1);
                        this.$emit('input', this.files);
                    });
            }
        },
        watch: {
            uploadUrl: function(oldvalue, newvalue) {
                if ((this.uploadUrl != '') && (oldvalue != newvalue)) {
                    this.uploadFiles();
                }
            },
            value: function() {
                this.files = this.value.slice();
            }
        }
    }
</script>
<style>
    .file-collector-container {
        width: 100%;
        min-height: 3em;
        border: 1px solid lightblue;
        text-align:center;
        padding-right: 2em;
        position:relative;
    }
    .file-collector-add-button-container {
        position: absolute;
        top: 0px;
        right: 0px;
        /*width: 1.8em;*/
        font-weight: bold;
        color: lightgrey;
        height: 100%;
        border: 1px solid rgba(0,0,0,0);
    }
    .file-collector-add-button-container > label {
        max-width: 100%;
        text-align:center;
        height: 100%;
        padding-top: .4em;
    }
    /*.file-collector-add-button-container:hover {*/
    /*border: 1px dotted lightblue;*/
    /*}*/
    .file-collector-container-focused {
        background-color: lightgrey;
    }
    .file-collector-file-span {
        padding: 2px;
        padding-left: 5px;
        padding-right: 5px;
        border-radius: 5px;
        box-shadow: 2px 3px lightgrey;
        background-color: lightcyan;
        margin: 3px;
        color: black;
        font-size: .7em;
        white-space: nowrap;
    }
    .file-collector-remove-button {
        margin-left: 10px;
        padding: 3px;
        cursor: pointer;
        font-weight: bold;
        color: darkgrey;
        user-select: none;
    }
    .file-collector-remove-button:hover {
        color: white;
    }

</style>