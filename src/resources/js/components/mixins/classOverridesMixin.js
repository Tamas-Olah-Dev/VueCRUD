export const classOverridesMixin = {
    props: {
        classOverrides: {type: Object, default: () => {return {}}}
    },
    methods: {
        getClassOverrideOrDefaultClass: function(classRole, defaultClass) {
            return typeof(this.classOverrides[classRole]) == 'undefined'
                ? defaultClass
                : this.classOverrides[classRole];
        }
    }
}