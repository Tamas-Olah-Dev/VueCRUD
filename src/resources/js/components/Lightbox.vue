<template>
    <div style="max-height: 100%">
        <img :src="thumbUrl"
             class="lightbox-thumbnail"
             :alt="imageAlt"
             :title="imageAlt"
             v-bind:style="{'max-height': maxThumbHeight}"
             style="max-width:100%"
             v-on:click="toggleZoom">
        <div class="lightbox-zoomed"
             v-if="zoom"
             v-on:click="toggleZoom"
        >
            <img :src="imageUrl"
                 :alt="imageAlt"
                 :title="imageAlt"
            >
            <div style="width: 100%; text-align: center; margin-top: 5px">
                <a :href="imageUrl" target="_blank" v-html="newWindowLabel" style="color:white"></a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            imageUrl: {},
            thumbnailUrl: {type: String, default: ''},
            imageAlt: {type: String, default: ''},
            newWindowLabel: {type: String, default: 'Open in new window'},
            maxThumbHeight: {type: String, default: '2em'}
        },
        data: function() {
            return {
                zoom: false
            }
        },
        computed: {
            thumbUrl: function() {
                return this.thumbnailUrl != ''
                    ? this.thumbnailUrl
                    :this.imageUrl;
            }
        },
        methods: {
            toggleZoom: function() {
                this.zoom = !this.zoom;
            }
        },
        mounted() {}
    }
</script>
<style>
    .lightbox-thumbnail {
        cursor: pointer;
        max-height: 100%
    }
    .lightbox-zoomed {
        position: fixed;
        z-index: 1000;
        width: 100%;
        height: 100vh;
        top: 0px;
        left: 0px;
        background-color: rgba(32, 32, 32, .6);
        display: flex;
        justify-content:center;
        align-items: center;
        flex-direction:column;
    }
    .lightbox-zoomed > img {
        max-height: 90%;
        border: 1px solid black;
        box-shadow: 2px 2px rgba(32, 32, 32, .6), 2px 2px rgba(32, 32, 32, .3), 2px 2px rgba(32, 32, 32, .1) ;
    }
</style>