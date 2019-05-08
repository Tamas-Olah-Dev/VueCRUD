<template>
    <div ref="container">
        <button class="dropdown-button"
                ref="mainButton"
                v-bind:class="mainButtonClass"
                :disabled="disabled"
                v-on:click="openDropdown = !openDropdown"
        >
            <span v-html="mainButtonLabel"></span>
            <span ref="caret"
                  class="dropdown-button-caret"
                  v-bind:class="caretClass"
            >&#9666;</span>
        </button>
        <div class="dropdown-button-dropdown" v-show="openDropdown" ref="dropdown">
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
                openDropdown: false
            }
        },
        mounted() {},
        computed: {
            caretClass: function() {
                return this.openDropdown ? 'dropdown-button-caret-open' : ''
            },
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
        max-width: 50%;
        padding: 5px;
        border-top: none;
        box-shadow: 5px 5px rgba(64, 64, 64, .3);
        background-color: white;
        position:absolute;
    }

</style>