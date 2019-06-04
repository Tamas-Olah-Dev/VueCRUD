<template>
    <div ref="container" style="position: relative">
        <button class="dropdown-button"
                ref="mainButton"
                role="dropdown"
                v-bind:class="mainButtonClass"
                v-bind:aria-expanded="openDropdown ? 'true' : 'false'"
                :disabled="disabled"
                v-on:click="openDropdown = !openDropdown"
                v-on:keyup.esc="openDropdown = false"
        >
            <slot></slot>
            <span ref="caret"
                  class="dropdown-button-caret"
                  v-bind:class="caretClass"
            >&#9666;</span>
        </button>
        <div class="dropdown-button-dropdown"
             :class="dropdownPositioningClass"
             v-show="openDropdown"
             ref="dropdown">
            <button v-for="label, event in items"
                    :key="event"
                    :ref="event"
                    v-html="label"
                    v-bind:class="dropdownButtonClass"
                    v-on:click="$emit('clicked', event); openDropdown = false"></button>
        </div>

    </div>
</template>

<script>
    export default {
        props: {
            mainButtonClass: {type: String, default: 'btn btn-primary'},
            dropdownButtonClass: {type: String, default: 'btn btn-primary btn-block'},
            mainButtonLabel: {type: String},
            items: {},
            disabled: {type: Boolean, default: false}
        },
        data: function() {
            return {
                openDropdown: false,
                dropdownPositioningClass: [],
            }
        },
        mounted() {
            if (this.$refs.container.getBoundingClientRect().left > document.documentElement.clientWidth / 2) {
                this.dropdownPositioningClass.push('dropdown-button-dropdown-toleft');
            }
            if (this.$refs.container.getBoundingClientRect().top > document.documentElement.clientHeight * 0.7) {
                this.dropdownPositioningClass.push('dropdown-button-dropdown-upwards');
            }
        },
        computed: {
            caretClass: function() {
                return this.openDropdown ? 'dropdown-button-caret-open' : ''
            },
        },
        beforeDestroy: function() {
            document.removeEventListener('click', this.handleClickOutside, true);
        },
        methods: {
            handleClickOutside: function(e) {
                const el = this.$refs.dropdown;
                const mainButtonEl = this.$refs.mainButton;
                if ((!el.contains(e.target)) && ((!mainButtonEl.contains(e.target)))) {
                    this.openDropdown = false;
                }
            },
        },
        watch: {
            openDropdown: function() {
                if (this.openDropdown) {
                    document.addEventListener('click', this.handleClickOutside, true);
                } else {
                    document.removeEventListener('click', this.handleClickOutside, true);
                }
            }
        }
    }
</script>
<style>
    .dropdown-button {
        display: flex;
        align-items: center;
    }
    .dropdown-button-caret {
        cursor:pointer;
        font-size: 1.3em;
        transition: transform 100ms ease-in-out;
        margin-left: 5px;
        display: inline-block
    }
    .dropdown-button-caret-open {
        transform: rotate(-90deg);
    }
    .dropdown-button-dropdown {
        z-index: 1000;
        padding: 5px;
        border-top: none;
        box-shadow: 5px 5px rgba(64, 64, 64, .3);
        background-color: white;
        position:absolute;
    }
    .dropdown-button-dropdown-toright {
        left: 10px;
    }
    .dropdown-button-dropdown-toleft {
        margin-left: -40%;
    }
    .dropdown-button-dropdown-upwards {
        bottom: 4em;
    }

</style>