<template>
    <div class="trix-container" style="min-height:210px">
        <input v-bind:id="fieldname+'-richtext'"
               type="hidden"
               v-bind:value="value"
               :ref="fieldname+'-content'"
        >
        <trix-editor v-bind:input="fieldname+'-richtext'"
                     class="editform-richtext-editor"
                     :ref="fieldname+'-editor'"
                     style="min-height:200px;"
        ></trix-editor>
    </div>
</template>

<script>
    export default {
        props: {
            fieldname: {type: String},
            value: {type: String},
            trixCSSUrl: {type: String, default: '/js/plugins/trix/trix.css'},
            trixJSUrl: {type: String, default: '/js/plugins/trix/trix.hu.js'},
        },
        data: function() {
            return {
                latestValue: '',
                valueInitialized: false,
            }
        },
        mounted: function() {
            this.latestValue = this.value;
            var csstag = document.createElement('link');
            csstag.setAttribute('href', this.trixCSSUrl);
            csstag.setAttribute('rel', 'stylesheet');
            var scripttag = document.createElement('script');
            scripttag.setAttribute('src', this.trixJSUrl);
            document.head.appendChild(csstag);
            document.head.appendChild(scripttag);
            window.trixIntervals = []
            window.trixIntervals[this.fieldname] = window.setInterval(this.updateValue, 1000);
        },
        methods: {
            updateValue: function() {
                if (typeof(this.$refs[this.fieldname+'-content']) == 'undefined') {
                    window.clearInterval(window.trixIntervals[this.fieldname]);
                } else {
                    if (this.$refs[this.fieldname+'-content'].value != this.latestValue) {
                        this.$emit('input', this.$refs[this.fieldname+'-content'].value);
                        this.latestValue = this.$refs[this.fieldname+'-content'].value;
                    }
                }
            }
        },
        watch: {
            value: function() {
                if (!this.valueInitialized) {
                    this.$refs[this.fieldname+'-editor'].editor.loadHTML(this.value);
                    this.valueInitialized = true;
                }
            }
        }

    }
</script>
