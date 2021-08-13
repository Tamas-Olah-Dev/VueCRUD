<template>
    <div style="position: fixed; transition: opacity 200ms ease-in-out, visibility 200ms ease-in-out; z-index: 50"
         :ref="'container'"
         :style="{opacity: show ? 1 : 0, visibility: show ? 1 : 0}"
         :class="getCSSClass('model-manager-context-menu-container')">
        <ul>
            <li>Menü</li>
            <li>Menü</li>
            <li>Menü</li>
            <li>Menü</li>
        </ul>
    </div>
</template>

<script>
    export default {
        inject: ['loadingIndicator', 'getCSSClass', 'translate'],
        props: {
            operationsUrl: {type: String},
            subject: {type: Object},
            show: {type: Boolean, default: false},
            cursorPosition: {type: Object, default: () => {return {x: 0, y: 0}}}
        },
        data: function () {
            return {}
        },
        mounted() {
        },
        methods: {
            emitHide: function() {
                this.$emit('hide');
            },
            handleClickOutside: function(event) {
                let inPath = false;
                event.path.reverse().forEach((p) => {
                    if ((p.isSameNode) && (p.isSameNode(this.$refs['container']))) {
                        inPath = true;
                    }
                });
                if (!inPath) {
                    this.emitHide();
                }
            }
        },
        computed: {},
        watch: {
            show: function(value) {
                if (value === true) {
                    let openToX = this.cursorPosition.x + 250 > window.innerWidth ? 'right' : 'left';
                    let openToY = this.cursorPosition.y > window.innerWidth / 2 ? 'bottom' : 'top';
                    if (openToX == 'left') {
                        this.$refs['container'].style['right'] = 'unset';
                        this.$refs['container'].style[openToX] = this.cursorPosition.x.toString() + 'px';
                    } else {
                        this.$refs['container'].style['left'] = 'unset';
                        this.$refs['container'].style[openToX] = (window.innerWidth - this.cursorPosition.x).toString() + 'px';
                    }
                    if (openToY == 'top') {
                        this.$refs['container'].style['bottom'] = 'unset';
                        this.$refs['container'].style.top = this.cursorPosition.y.toString()+'px';
                    } else {
                        this.$refs['container'].style['top'] = 'unset';
                        this.$refs['container'].style.bottom = (window.innerHeight - this.cursorPosition.y).toString()+'px';
                    }
                    document.body.addEventListener('click', this.handleClickOutside);
                } else {
                    document.body.removeEventListener('click', this.handleClickOutside);
                }
            }
        }

    }
</script>
