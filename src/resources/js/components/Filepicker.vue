<template>
    <div :class="getCSSClass('filepicker-container')" style="position: relative">
        <div :class="getCSSClass('filepicker-input-container')"
             :style="{cursor: loading ? 'not-allowed' : 'initial'}"
        >
            <input type="text" v-bind:value="fileLabel" readonly>
            <label :class="getCSSClass('filepicker-input-label')"
                   :style="{opacity: loading ? '.5' : '1'}"
                   :ref="'fileinput'">
                <input type="file"
                       v-on:change="handleFileChange"
                       style="width: 0; height: 0; opacity: 0; padding: 0; margin: 0; border: none">
                <button type="button"
                        v-on:click="forwardClick"
                        :disabled="loading"
                        v-html="translate('Select')"></button>
            </label>
        </div>
        <div :class="getCSSClass('filepicker-loader')"
             style="position: absolute; bottom: 0; left: 0; width: 100%; height: 2px"
        >
            <div :class="getCSSClass('filepicker-loader-inner')"
                 style="height: 100%;transition: width 50ms linear"
                 :style="{width: progressPercentage}"
                 :ref="'progressbar'"
            ></div>
        </div>
    </div>
</template>

<script>
    export default {
        inject: ['loadingIndicator', 'getCSSClass', 'translate', 'icon'],
        props: {
            value: {type: String},
            operationsUrl: {type: String},
            actions: {type: Object, default: () => {return {
                    upload: 'uploadFile',
                    delete: 'deleteFile'
                }}
            },
            mode: {type: String, default: 'url'},
        },
        data: function () {
            return {
                file: '',
                progress: 0,
                loading: false,
            }
        },
        mounted() {
        },
        methods: {
            uploadFile: function(file) {
                try {
                    let fileReader = new FileReader();
                    fileReader.readAsDataURL(file);
                    fileReader.onloadend = (readerEvent) => {
                        let uploadData = {
                            "fileName": file.name,
                            "fileData": readerEvent.target.result,
                            "fileType": file.type,
                            "action": this.actions.upload,
                            "mode": this.mode,
                        }
                        var self = this;
                        console.log(uploadData);
                        window.axios.post(this.operationsUrl, uploadData, {onUploadProgress: function(progressEvent) {
                                self.progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                            }}).then((response) => {
                                this.file = response.data;
                                this.$emit('input', this.file);
                                window.setTimeout(() => {
                                    this.progress = 0;
                                    this.loading = false;
                                }, 500);
                            })
                            .catch((error) => {
                                alert(error.response.data);
                                this.progress = 0;
                                this.file = '';
                                this.loading = false;
                            })
                    }
                } catch (error) {
                    alert(error.message);
                }
            },
            handleFileChange: function(event) {
                this.loading = true;
                this.file = '';
                if (event.target.files[0]) {
                    this.uploadFile(event.target.files[0]);
                }
            },
            forwardClick: function(event) {
                event.preventDefault();
                this.$refs['fileinput'].click();
            }
        },
        computed: {
            fileLabel: function() {
                if (this.mode == 'url') {
                    return this.file;
                }
                if (this.mode == 'object') {
                    return this.file['name'];
                }
            },
            progressPercentage: function() {
                return this.progress.toString()+'%';
            }
        },
        watch: {
        }

    }
</script>
