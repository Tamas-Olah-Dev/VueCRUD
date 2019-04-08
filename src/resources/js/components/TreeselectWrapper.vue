<template>
    <treeselect v-bind="treeselectProps"
                :value="treeselectValue"
                v-on:input="$emit('input', $event)"
                :options="options"
    ></treeselect>
</template>

<script>
    export default {
        props: {
            treeselectProps: {type: String},
            value: {},
            originalOptions: {default: () => {return [];}},
            relationshipName: {type: String, default: null},
            labelName: {type: String, default: 'name'}
        },
        data: function() {
            return {
                treeselectValue: null,
            }
        },
        computed: {
            options: function() {
                if (typeof(this.originalOptions) != 'undefined') {
                    return this.convertNodeToTreeselectStandard(
                        this.originalOptions,
                        this.relationshipName,
                        this.labelName
                    );
                }
                return [];
            }
        },
        methods: {
            convertNodeToTreeselectStandard: function(node, relationshipName, labelName) {
                let result = [];
                for (let i = 0; i < node.length; i++) {
                    let newElement = {id: node[i].id, label: node[i][labelName]};
                    if (relationshipName != null) {
                        if ((typeof(node[i][relationshipName]) != 'undefined')
                            && (node[i][relationshipName].length > 0)) {
                            newElement.children = this.convertNodeToTreeselectStandard(
                                node[i][relationshipName],
                                relationshipName,
                                labelName
                            );
                        }
                    }
                    result.push(newElement);
                }
                return result;
            }
        },
        watch: {
            value: function() {
                this.treeselectValue = this.value;
            }
        }
    }
</script>
