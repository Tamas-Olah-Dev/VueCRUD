<template>
    <div style="height:100%; min-height:100%">
        <div class="g-recaptcha" :data-sitekey="gKey" id="recaptcha-div"></div>
    </div>
</template>

<script>
    export default {
        props: {
            gKey: {type: String},
            locale: {type: String},
            scriptUrl: {type: String, default: 'https://www.google.com/recaptcha/api.js?hl=hu'}
        },
        data: function() {
            return {
                response: '',
            }
        },
        computed: {},
        mounted: function() {
            if (typeof(window.recaptchaInitialized) == 'undefined') {
                window.recaptchaInitialized = true;
                var scripttag = document.createElement('script');
                scripttag.setAttribute('src', this.scriptUrl);
                document.head.appendChild(scripttag);
            } else {
                grecaptcha.render('recaptcha-div');
            }
            window.recaptchInterval = window.setInterval(() => {
                if (document.getElementById('g-recaptcha-response') != null) {
                    this.response = document.getElementById('g-recaptcha-response').value;
                }
            }, 500)
        },
        beforeDestroy: function() {
            window.clearInterval(window.recaptchInterval);
        },
        methods: {
        },
        watch: {
            response: function() {
                this.$emit('input', this.response);
            }
        }
    }
</script>
