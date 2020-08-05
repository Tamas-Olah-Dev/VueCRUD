<template>
    <div class="image-library-container">
        <template v-for="imageUrl in imageUrls">
            <div draggable="true"
                 :data-imageurl="imageUrl"
                 v-on:dragover="$event.preventDefault()"
                 v-on:dragstart="startMoving"
                 v-on:dragend="endMoving"
                 v-on:dragenter="showDragOverEffect($event, imageUrl)"
                 v-on:dragleave="hideDragOverEffect($event, imageUrl)"
                 v-on:drop="moveToBefore($event, imageUrl)"
                 :ref="'image-'+imageUrl"
                 class="image-library-thumbnail">
                <img style="width: 100%" :src="imageUrl" :label="imageUrl">
                <div class="image-library-thumbnail-button"
                     v-on:click="removeImage(imageUrl)"
                     v-show="moving === false"
                >-
                </div>
            </div>
        </template>
        <div v-show="moving !== false"
             class="image-library-drop-field"
             :ref="'image-end'"
             v-on:dragover="$event.preventDefault()"
             v-on:dragenter="showDragOverEffect($event, 'end')"
             v-on:dragleave="hideDragOverEffect($event, 'end')"
             v-on:drop="moveToEnd($event)"
        ><span></span></div>
        <div v-if="adding" v-html="spinnerSrc"></div>
        <div style="height: 5rem; margin: .2rem; padding-top: 1rem">
            <label :for="'selected-file-'+fieldname" style="display:flex; height: 100%; padding: 5%">
                <input :id="'selected-file-'+fieldname"
                       type="file"
                       :accept="this.accept === false ? '' : this.accept.join(',')"
                       v-on:change="fileSelected"
                       style="height:0px; width:0px; opacity:0">
                <span class="image-library-add-button">+</span>
            </label>
        </div>
    </div>
</template>

<script>
    import {fileUploadMixin} from './mixins/fileUploadMixin.js'
    import {spinner} from './mixins/spinner'

    export default {
        mixins: [fileUploadMixin, spinner],
        props: {
            uploadUrl: {type: String},
            value: {type: Array},
            fieldname: {type: String, default: 'fieldname'},
            formElementLabel: {type: String, default: ''},
            limit: {default: null},
            accept: {default: false},
        },
        data: function () {
            return {
                adding: false,
                imageUrls: [],
                selectedFileLabel: '',
                selectedFile: '',
                allowedFileTypes: [],
                defaultValue: null,
                moving: false,
            }
        },
        created() {
            if (this.accept !== false) {
                this.accept.forEach((allowString) => {
                    allowString.split(',').forEach((allow) => {
                        this.allowedFileTypes.push(allow);
                    })
                })
            }
        },
        mounted() {
            this.imageUrls = this.value;
        },
        methods: {
            emitValue: function() {
                this.$emit('input', this.imageUrls);
            },
            showDragOverEffect: function(event, url) {
                event.preventDefault();
                let target = this.$refs['image-'+url][0] || this.$refs['image-'+url];
                if (target.getAttribute('data-imageurl') !== this.moving) {
                    target.classList.add('image-library-thumbnail-dropping');
                }
            },
            hideDragOverEffect: function(event, url) {
                event.preventDefault();
                let target = this.$refs['image-'+url][0] || this.$refs['image-'+url];
                target.classList.remove('image-library-thumbnail-dropping');
            },
            removeImage: function (url) {
                this.imageUrls = this.imageUrls.filter(item => item != url);
                this.emitValue();
            },
            startMoving: function(event) {
                let target = event.target;
                while (target.getAttribute('data-imageurl') == null) {
                    target = target.parentNode;
                }
                event.dataTransfer.setData('url', target.getAttribute('data-imageurl'));
                window.setTimeout(() => {
                    Array.from(document.querySelectorAll('.image-library-thumbnail img')).forEach((t) => {
                        t.classList.add('pointer-events-none');
                    });
                    this.moving = target.getAttribute('data-imageurl');
                    target.classList.add('image-library-thumbnail-hidden');
                }, 10);

            },
            endMoving: function(event) {
                event.target.classList.remove('image-library-thumbnail-hidden');
                this.moving = false;
                Array.from(document.querySelectorAll('.image-library-thumbnail')).forEach((t) => {
                    t.querySelector('img').classList.remove('pointer-events-none');
                    t.classList.remove('image-library-thumbnail-dropping');
                    t.classList.remove('image-library-thumbnail-hidden');
                });
                document.querySelector('.image-library-drop-field').classList.remove('image-library-thumbnail-dropping');
            },
            moveToBefore: function(event, imageUrl) {
                event.stopPropagation();
                event.preventDefault();
                let newOrder = [];
                this.imageUrls.forEach((item) => {
                    if (item == decodeURI(imageUrl)) {
                        newOrder.push(decodeURI(event.dataTransfer.getData('url')));
                    }
                    if (item != decodeURI(event.dataTransfer.getData('url'))) {
                        newOrder.push(item);
                    }
                });
                this.imageUrls = newOrder;
                this.emitValue();
            },
            moveToEnd: function(event) {
                event.stopPropagation();
                event.preventDefault();
                this.removeImage(decodeURI(event.dataTransfer.getData('url')));
                this.imageUrls.push(decodeURI(event.dataTransfer.getData('url')));
                this.emitValue();
            },
            fileSelected: function (event) {
                if (event.target.files.length == 0) {
                    return false;
                }
                if (this.allowedFileTypes.indexOf(event.target.files[0].type) == -1) {
                    return false;
                }
                if (this.imageUrls.length == this.limit) {
                    alert('Maximum '+this.limit+' fájl tölthető fel!')
                    return false;
                }
                this.adding = true;
                this.uploadPublicFileToVueCRUDController(
                    this.uploadUrl,
                    event.target.files[0],
                    'storePublicPicture'
                ).then((response) => {
                    this.adding = false;
                    this.imageUrls.push(response.data.url);
                    this.emitValue();
                }).catch((error) => {
                    console.log(error);
                })
            }
        }
    }
</script>
<style>
    .image-library-container {
        min-height: 10rem;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: flex-start;
        border: 1px solid darkgrey
    }

    .image-library-add-button {
        height: 3rem;
        width: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        border-radius: 50%;
        border: 1px solid darkgrey;
        transition: box-shadow 200ms ease-in-out;
    }

    .image-library-add-button:hover {
        box-shadow: .2rem .2rem rgba(7, 7, 7, .5);
    }

    .image-library-thumbnail {
        object-fit: contain;
        box-shadow: .2rem .2rem rgba(7,7,7,.4);
        height: 5rem;
        width: 5rem;
        max-height: 5rem;
        max-width: 5rem;
        margin: .2rem;
        position: relative;
        cursor: move;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        transition: width 200ms ease-in-out, max-width 200ms ease-in-out, padding-left 200ms ease-in-out;
    }
    .pointer-events-none {
        pointer-events: none;
    }
    .image-library-thumbnail-dropping {
        padding-left: 2rem;
        width: 7rem;
        max-width: 7rem;
    }
    .image-library-thumbnail-hidden {
        width: 0px !important;
    }

    .image-library-thumbnail-button {
        visibility: hidden;
        cursor: pointer;
        position: absolute;
        z-index:100;
        top: 0px;
        right: 0px;
        width: 40%;
        height: 40%;
        background-color: rgba(7, 7, 7, .5);
        color: white;
        font-size: 2rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .image-library-thumbnail:hover .image-library-thumbnail-button {
        visibility: visible;
    }
    .image-library-drop-field.image-library-thumbnail-dropping {
        background-color: rgba(7,7,7,.2);
    }
    .image-library-drop-field {
        height: 5rem;
        margin: .2rem;
        width: 2rem;
        max-height: 5rem;
        flex-shrink: 0;
        background-color: transparent;
        box-shadow: .2rem .2rem rgba(7,7,7,.4);
        transition: transform 200ms ease-in-out, background-color 200ms ease-in-out;
    }

</style>
