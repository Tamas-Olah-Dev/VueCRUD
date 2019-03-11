export const translateMixin = {
    methods: {
        translate: function(string) {
            if (typeof(window.laravelLocales[window.laravelLocale][string]) != 'undefined') {
                return window.laravelLocales[window.laravelLocale][string];
            }
            if (typeof(this.$root.translate) != 'undefined') {
                return this.$root.translate(string);
            }

            return string;
        },
    }
}