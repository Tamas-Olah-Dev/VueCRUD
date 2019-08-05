<template>
    <div style="display: flex; flex-direction: column">
        <div v-show="!uploading" style="display: flex; justify-content: center">
            <div v-show="internalValue != null"  style="display: flex; justify-content: center; flex-direction: column">
                <div style="width: 256px; position: relative">
                    <img style="width: 100%" :src="internalValue">
                    <button class="profile-picture-manager-remove-button"
                            v-on:click="removeFile">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                <template v-for="processingOptionLabel, processingOption in processingOptions">
                    <label class="profile-picture-manager-processing-option">
                        <input type="radio" :value="processingOption" v-model="selectedProcessingOption">
                        <span v-html="processingOptionLabel"></span>
                    </label>
                </template>
            </div>
            <div v-show="internalValue == null">
                <label :for="'filepicker'+token" style="width: 50px">
                    <input :name="'filepicker'+token" :id="'filepicker'+token"
                           :ref="'filepicker'"
                           v-on:change="uploadFile"
                           type="file" accept="image/jpeg"
                           style="height: 0px; width: 0px; opacity: 0"
                    >
                    <button style="height: 48px; width: 48px; border-radius: 24px; background-color: transparent"
                            v-on:click="$refs.filepicker.click()"
                    >+</button>
                </label>
            </div>
        </div>
        <div v-show="uploading">
            <div style="width: 100%; height: 2em; display: flex; justify-content: center">
                <div style="width: 256px; height: 32px !important" v-html="spinnerSrc"></div>
            </div>
        </div>
    </div>
</template>

<script>
    import {fileUploadMixin} from './mixins/fileUploadMixin.js'
    import {spinner} from './mixins/spinner.js'
    import {classOverridesMixin} from './mixins/classOverridesMixin.js'
    import {translateMixin} from './mixins/translateMixin.js'
    export default {
        mixins: [fileUploadMixin, spinner, classOverridesMixin],
        props: {
            operationsUrl: {type: String, default: ''},
            value: {type: String, default: ''},
            token: {type: String, default: () => Math.random().toString().substr(3, 8)},
            processingOptions: {type: Object, default: () => {
                return {
                    resize: 'Átméretezés',
                    crop: 'Körbevágás'
                }
            }}
        },
        data: function() {
            return {
                uploading: false,
                selectedProcessingOption: 'resize',
                internalValue: null,
            }
        },
        mounted() {
            this.internalValue = this.value == '' ? null : this.value;
        },
        computed: {
        },
        methods: {
            uploadFile: function() {
                if (this.$refs.filepicker.files.length > 0) {
                    this.uploading = true;
                    this.uploadPublicFileToVueCRUDController(
                        this.operationsUrl,
                        this.$refs.filepicker.files[0],
                        'storeProfilePicture',
                        this.selectedProcessingOption
                    ).then((response) => {
                        this.uploading = false;
                        this.internalValue = response.data;
                    });
                }
            },
            changeResizeMethod: function() {
                if (this.internalValue != '') {
                    this.uploading = true;
                    window.axios.post(this.operationsUrl, {
                        action: 'changeResizeMethod',
                        mode: this.selectedProcessingOption
                    }).then((response) => {
                        this.uploading = false;
                        this.internalValue = response.data;
                    });
                }
            },
            removeFile: function() {
                this.removeUploadedPublicFile(
                    this.operationsUrl,
                    this.internalValue,
                    'removeProfilePicture',
                    null
                ).then((response) => {
                    this.internalValue = null;
                });
            }
        },
        watch: {
            selectedProcessingOption: function() {
                this.changeResizeMethod();
            }
        }
    }
</script>
<style>

</style>