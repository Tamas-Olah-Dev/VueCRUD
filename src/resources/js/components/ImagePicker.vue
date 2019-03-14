<template>
    <div style="display: flex; justify-content: space-between; align-items: center">
        <div class="image-picker-input-container">
            <label for="selected-file" style="display:flex; max-width: 80%">{{ formElementLabel }}
                <input id="selected-file"
                       type="file"
                       accept="image/jpeg,image/png"
                       v-on:change="fileSelected"
                       style="height:0px; width:0px; opacity:0">
                <input class="form-control"
                       type="text"
                       readonly
                       v-model="selectedFileLabel"
                >
                <span class="input-group-text image-picker-browse-button"
                >...</span>
            </label>
            <span class="input-group-text image-picker-browse-button"
                  style="margin-bottom: .5rem; cursor: pointer"
                  v-if="value != '' && value != null"
                  v-on:click.self="$emit('input', ''); selectedFileLabel = ''"
            >X</span>

        </div>
        <div class="image-picker-preview-container">
            <img v-if="value != '' && value != null " :src="value">
        </div>
    </div>
</template>

<script>
    import {fileUploadMixin} from './mixins/fileUploadMixin.js'
    export default {
        mixins: [fileUploadMixin],
        props: {
            uploadUrl: {type: String},
            defaultFileLabel: {type: String},
            value: {type: String},
            formElementLabel: {type: String, default: ''}
        },
        data: function() {
            return {
                selectedFileLabel: '',
                selectedFile: '',
                allowedFileTypes: ['image/png', 'image/jpeg'],
                defaultValue: null
            }
        },
        mounted() {
            this.defaultValue = this.value;
            this.uploadedFileUrl = typeof(this.defaultUploadedFileUrl) != 'undefined'
                ? this.defaultUploadedFileUrl
                : '';
            this.selectedFileLabel = this.value;
        },
        methods: {
            fileSelected: function(event) {
                if (event.target.files.length == 0) {
                    this.selectedFileLabel = '';
                    this.$emit('input', '');
                    return false;
                }
                if (this.allowedFileTypes.indexOf(event.target.files[0].type) == -1) {
                    return false;
                }
                this.selectedFileLabel = event.target.files[0].name;
                this.uploadPublicFileToVueCRUDController(
                    this.uploadUrl,
                    event.target.files[0],
                    'storePublicPicture'
                ).then((response) => {
                    if ((response.data.url != this.defaultValue) && (this.value != this.defaultValue)) {
                        this.removeUploadedPublicFile(
                            this.uploadUrl,
                            this.value,
                            'removePublicPicture'
                        ).then((removeResponse) => {
                            this.$emit('input', response.data.url)
                        })
                    } else {
                        this.$emit('input', response.data.url)
                    }
                })
                    .catch((error) => {
                        console.log(error);
                    })
            }
        }
    }
</script>
<style>
    .image-picker-input-container {
        display:flex;
        justify-content: flex-start;
    }
    .image-picker-input-container > label > .input-group > .form-control {
        outline-width: 1px !important;
        outline-color: black !important;
    }
    .image-picker-preview-container > img {
        height:6em;
        max-height:6em;
    }
    .image-picker-browse-button:hover {
        font-weight: bold;
    }
</style>