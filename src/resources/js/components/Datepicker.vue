<template>
    <div ref="container" style="position:relative">
        <div class="input-group vue-datepicker-inputgroup" :ref="'inputgroup'">
            <label v-if="formElementLabel != ''">{{ formElementLabel }}</label>
            <template v-if="disabled">
                <input v-model="dateLabel"
                       class="form-control vuedatepicker-input"
                       v-bind:class="inputClass"
                       readonly
                       disabled
                       style="background-color: #e9ecef"
                >
            </template>
            <template v-else>
                <div class="input-group-append vue-datepicker-inputgroup-append">
                    <input v-model="dateLabel"
                           class="form-control vuedatepicker-input"
                           v-bind:class="inputClass"
                           @click="toggleDatepickerDropdown"
                           readonly
                    >
                    <span v-on:click="resetDate"
                          v-show="dateValue != null"
                          class="vuedatepicker-clear-button"
                    >X</span>
                    <span class="input-group-text vuedatepicker-calendar-icon"
                          v-on:click="toggleDatepickerDropdown"
                          style="padding:5px; cursor:pointer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" style="max-height:1.5em; max-width: 1.5em">
                            <path
                                    style="fill:darkgrey;fill-opacity:1;stroke:none"
                                    d="M 6 3 C 6 3 5 2.99997 5 4 L 3 4 L 3 7 L 3 18 L 3 19 L 19 19 L 19 18 L 19 7 L 19 4 L 17 4 C 17 2.99997 16 3 16 3 L 13 3 C 13 3 12 2.99997 12 4 L 10 4 C 10 2.99997 9 3 9 3 L 6 3 z M 6 4 L 9 4 L 9 5 L 6 5 L 6 4 z M 13 4 L 16 4 L 16 5 L 13 5 L 13 4 z M 4 7 L 18 7 L 18 18 L 4 18 L 4 7 z M 6 8 L 6 10 L 8 10 L 8 8 L 6 8 z M 10 8 L 10 10 L 12 10 L 12 8 L 10 8 z M 14 8 L 14 10 L 16 10 L 16 8 L 14 8 z M 6 11 L 6 13 L 8 13 L 8 11 L 6 11 z M 10 11 L 10 13 L 12 13 L 12 11 L 10 11 z M 14 11 L 14 13 L 16 13 L 16 11 L 14 11 z M 14 14 L 14 16 L 16 16 L 16 14 L 14 14 z "
                                    class="ColorScheme-Text"
                            />
                        </svg>
                    </span>
                    <span v-if="showTimeInputs == 'true'" class="vue-datepicker-time-inputs-container">
                        <input type="text" v-model="hour" style="width: 2em">
                        <span>:</span>
                        <input type="text" v-model="minute" style="width: 2em">
                        <span>:</span>
                        <input type="text" v-model="second" style="width: 2em">
                    </span>
                </div>
            </template>
        </div>
        <div class="vuedatepicker-dropdown" v-if="showDropdownFlag" :ref="'dropdown'" v-bind:class="{'vuedatepicker-dropdown-upwards': upwards}">
            <div class="vuedatepicker-inputs-container">
                <input v-model="year" type="number" class="form-control vuedatepicker-year-input">
                <select v-model="month" class="form-control vuedatepicker-month-select">
                    <option v-for="monthname, monthindex in months"
                            v-bind:value="monthindex"
                            v-html="monthname"></option>
                </select>
                <button type="button"
                        v-on:click="gotoToday"
                        class="vuedatepicker-today-button"
                        v-if="showTodayButton"
                >&#x2600;</button>
            </div>
            <div class="vuedatepicker-inputs-container">
                <table class="vuedatepicker-days-table">
                    <thead>
                    <tr>
                        <th v-for="weekday in weekdayInitials" v-html="weekday"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="weekindex in [0,1,2,3,4]">
                        <td v-for="dayindex in [0,1,2,3,4,5,6]"
                            v-html="dateByWeekAndDayIndex(weekindex, dayindex).getDate()"
                            v-bind:class="getDayTableCellClass(dateByWeekAndDayIndex(weekindex, dayindex))"
                            v-on:click="setDayByWeekAndDayIndex(weekindex, dayindex)"
                        ></td>

                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import {classOverridesMixin} from './mixins/classOverridesMixin.js'
    export default {
        mixins: [classOverridesMixin],
        props: {
            upwards: {type: Boolean, default: false},
            formElementLabel: {type: String, default: ''},
            value: {},
            locale: {type: String, default: () => {return typeof(window.laravelLocale) != 'undefined' ? window.laravelLocale : 'hu'}},
            inputClass: {type: String, default: ''},
            showTimeInputs: {type: String, default: 'false'},
            showTodayButton: {type: Boolean, default: true},
            clearingSetsToday: {type: Boolean, default: true},
            disabled: {type: Boolean, default: false},
            default: {default: null}
        },
        data: function() {
            return {
                dateValue: null,
                dateLabel: null,
                year: null,
                month:null,
                day:null,
                hour: null,
                minute: null,
                second: null,
                allmonths: {
                    "hu": ['január', 'február', 'március', 'április', 'május', 'június', 'július', 'augusztus', 'szeptember', 'október', 'november', 'december'],
                    "en": ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
                },
                allweekdays: {
                    "hu": ['hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat', 'vasárnap'],
                    "en": ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
                },
                allweekdayInitials: {
                    "hu": ['H', 'K', 'Sz', 'Cs', 'P', 'Sz', 'V'],
                    "en": ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
                },
                dateRegex: new RegExp('^[0-9]{4}\-[0-9]{2}\-[0-9]{2}(.[0-9]{1,2}\:[0-9]{1,2}\:[0-9]{1,2}){0,1}$'),
                showDropdownFlag: false,
                todaysDate: null,
                valueIsObject: false,
                internalDefault: null,
            }
        },
        mounted() {
            this.internalDefault = this.default;
            this.todaysDate = new Date();
            if (typeof(this.value) != 'undefined') {
                this.parseValue(this.value);
            } else {
                this.gotoToday();
            }
        },
        computed: {
            daysInCurrentMonth: function() {
                return new Date(this.year, this.month + 1, 0).getDate();
            },
            startingWeekDayOfCurrentMonthAndYear: function() {
                return this.europeanWeekday(new Date(this.year, this.month, 1).getDay());
            },
            months: function() {
                return this.allmonths[this.locale];
            },
            weekdays: function() {
                return this.allweekdays[this.locale];
            },
            weekdayInitials: function() {
                return this.allweekdayInitials[this.locale];
            },
            tableStartingDay: function() {
                return new Date(this.year, this.month, -1 * (this.startingWeekDayOfCurrentMonthAndYear - 1));
            },
        },
        methods: {
            parseValue: function(value) {
                if (this.dateRegex.test(value)) {
                    let datetimeparts = value.split(' ');
                    let dateparts = datetimeparts[0].split('-');
                    this.year = parseInt(dateparts[0]);
                    this.month = parseInt(dateparts[1]) - 1;
                    this.day = parseInt(dateparts[2]);
                    if (datetimeparts.length == 2) {
                        let timeparts = datetimeparts[1].split(':');
                        this.hour = parseInt(timeparts[0]);
                        this.minute = parseInt(timeparts[1]);
                        this.second = parseInt(timeparts[2]);
                    } else {
                        this.hour = 0;
                        this.minute = 0;
                        this.second = 0;
                    }
                    this.dateValue = new Date(this.year, this.month, this.day, this.hour, this.minute, this.second);
                    this.calculateDateValue();
                } else {
                    if ((typeof(value) == 'object') && (value instanceof Date)) {
                        this.year = value.getFullYear();
                        this.month = value.getMonth();
                        this.day = value.getDate();
                        this.hour = value.getHours();
                        this.minute = value.getMinutes();
                        this.second = value.getSeconds();
                        this.dateValue = value;
                        //this.$emit('input', this.year+'-'+(this.month+1)+'-'+this.day)
                        this.valueIsObject = true;
                        this.calculateDateValue();
                    } else {
                        this.gotoToday();
                    }
                }

            },
            gotoToday: function() {
                this.dateValue = new Date();
                this.year = this.dateValue.getFullYear();
                this.month = this.dateValue.getMonth();
                this.day = this.dateValue.getDate();
                this.hour = 0;
                this.minute = 0;
                this.second = 0;
            },
            getCompactDatestringFromDate: function(date) {
                if (date != null) {
                    return date.getFullYear().toString() + (date.getMonth() + 1).toString() + date.getDate().toString();
                }
            },
            getCompactYearMonthStringFromDate: function(date) {
                if (date != null) {
                    return date.getFullYear().toString() + (date.getMonth() + 1).toString();
                }
            },
            getDayTableCellClass: function(date) {
                if (this.isDateTodaysDate(date)) {
                    return 'vuedatepicker-current-day vuedatepicker-current-month vuedatepicker-today';
                }
                if (this.getCompactDatestringFromDate(date) == this.getCompactDatestringFromDate(this.dateValue)) {
                    return 'vuedatepicker-current-day vuedatepicker-current-month';
                }
                if (this.getCompactYearMonthStringFromDate(date) == this.getCompactYearMonthStringFromDate(this.dateValue)) {
                    return 'vuedatepicker-current-month';
                }
                return 'vuedatepicker-other-month';
            },
            europeanWeekday: function(weekday) {
                return weekday == 0 ? 6 : weekday-1;
            },
            toggleDatepickerDropdown: function() {
                if (this.showDropdownFlag) {
                    this.hideDatepickerDropdown();
                } else {
                    this.showDatepickerDropdown();
                }
            },
            hideDatepickerDropdown: function() {
                document.removeEventListener('click', this.handleClickOutside, true);
                this.showDropdownFlag = false;
            },
            showDatepickerDropdown: function() {
                document.addEventListener('click', this.handleClickOutside, true);
                if (this.dateValue == null) {
                    this.gotoToday();
                }
                this.showDropdownFlag = true;
            },
            emitInput: function() {
                if (this.valueIsObject) {
                    this.$emit('input', this.dateValue)
                } else {
                    if (this.showTimeInputs == 'true') {
                        this.$emit('input', this.dateLabel+' '+this.hour+':'+this.minute+':'+this.second);
                    } else {
                        this.$emit('input', this.dateLabel)
                    }
                }
            },
            calculateDateValue: function() {
                this.dateValue = new Date(this.year, this.month, this.day, this.hour, this.minute, this.second);
                if (this.dateValue.getFullYear() != this.year) {
                    this.month = 0;
                    this.day = 1;
                    this.dateValue = new Date(this.year, this.month, this.day, this.hour, this.minute, this.second);
                } else {
                    if (this.dateValue.getMonth() != this.month) {
                        this.day = 1;
                        this.dateValue = new Date(this.year, this.month, this.day, this.hour, this.minute, this.second);
                    }
                }
                this.dateLabel = this.year + '-' + (this.month + 1).toString().padStart(2, 0) + '-' + this.day.toString().padStart(2, 0);
                this.emitInput();
            },
            setDayByWeekAndDayIndex: function(weekIndex, dayIndex) {
                let selectedDate = this.dateByWeekAndDayIndex(weekIndex, dayIndex);
                this.year = null;
                this.year = selectedDate.getFullYear();
                this.month = selectedDate.getMonth();
                this.day = selectedDate.getDate();
                this.hideDatepickerDropdown();
            },
            dateByWeekAndDayIndex: function(weekIndex, dayIndex) {
                let startingDate = this.tableStartingDay;
                let index = (weekIndex * 7) + dayIndex;
                return new Date(this.year, this.month, index - (this.startingWeekDayOfCurrentMonthAndYear - 1));
            },
            handleClickOutside: function(e) {
                const el = this.$refs.container;
                if (!el.contains(e.target))
                    this.hideDatepickerDropdown();
            },
            isDateTodaysDate: function(date) {
                return date.getFullYear() == this.todaysDate.getFullYear()
                    && date.getMonth() == this.todaysDate.getMonth()
                    && date.getDate() == this.todaysDate.getDate();
            },
            dateLabelFromDate: function(value) {
                return value.getFullYear() + '-'
                    + (value.getMonth() + 1).toString().padStart(2, 0)
                    + '-'
                    + value.getDate().toString().padStart(2, 0);
            },
            resetDate: function() {
                this.hideDatepickerDropdown();
                if (this.clearingSetsToday) {
                    this.gotoToday();
                } else {
                    if (this.default == null) {
                        this.dateLabel = '';
                        this.dateValue = null;
                    } else {
                        this.parseValue(this.internalDefault);
                    }
                }
                this.emitInput();
            }
        },
        watch: {
            value: function(value) {
                this.parseValue(value);
            },
            year: function() {
                this.calculateDateValue();
            },
            month: function() {
                this.calculateDateValue();
            },
            day: function() {
                this.calculateDateValue();
            },
            hour: function() {
                if ((this.hour < 0) || (this.hour > 23)) {
                    this.hour = 0;
                }
                this.calculateDateValue();
            },
            minute: function() {
                if ((this.minute < 0) || (this.minute > 59)) {
                    this.minute = 0;
                }
                this.calculateDateValue();
            },
            second: function() {
                if ((this.second < 0) || (this.second > 59)) {
                    this.second = 0;
                }
                this.calculateDateValue();
            },
        }
    }
</script>
<style>
    .vuedatepicker-dropdown {
        z-index:1500;
        border: 1px solid lightgrey;
        padding:1px;
        background-color:white;
        box-shadow: 10px 5px rgba(64,64,64,0.2);
    }

    @media only screen and (max-width: 600px) {
        .vuedatepicker-dropdown {
            position:fixed;
            width:90%;
            max-width:90%;
            left:5%;
            top:30%;
        }
    }

    @media only screen and (min-width: 601px) {
        .vuedatepicker-dropdown {
            position:absolute;
            width:300px;
            max-width:300px;
            left: 0px;
        }
        .vuedatepicker-dropdown-upwards {
            margin-top: -310px;
        }

    }

    .vuedatepicker-days-table {
        width:100%;
    }
    .vuedatepicker-days-table td {
        cursor:pointer;
        border:1px dotted lightgrey;
        padding:2px;
        height:2.6em;
    }
    .vuedatepicker-days-table td:hover{
        border: 1px solid black
    }
    .vuedatepicker-current-month {
        background-color:white;
    }
    .vuedatepicker-other-month {
        background-color:#E6E6E6;
        color:darkgray;
    }
    .vuedatepicker-current-day {
        font-weight:bold;
    }
    .vuedatepicker-today {
        color: blue;
    }
    .vuedatepicker-input {
        background-color:white !important;
        flex-grow: 1;
    }

    .vuedatepicker-inputs-container {
        display:flex;
        justify-content: space-between;
    }
    .vuedatepicker-year-input {
        flex-grow:0;
    }

    .vuedatepicker-month-select {
        flex-grow:1
    }
    .vuedatepicker-today-button {
        flex-grow:0;
        flex-shrink:1;
        max-width:2em;
        padding:4px;
        opacity:.8;
    }
    .vuedatepicker-today-button:hover {
        opacity:1;
    }
    .vue-datepicker-time-inputs-container {
        display: flex;
        align-items: center;
        margin-left: 5px;
        margin-right: 5px;
    }
    .vue-datepicker-time-inputs-container > span {
        padding-left: 3px;
        padding-right: 3px;
    }
    .vue-datepicker-time-inputs-container > input {
        text-align: center;
        padding-left: 1px;
        padding-right: 1px;
        border-radius: 2px;
    }
    .vue-datepicker-inputgroup-append {
        width: 100%;
        display:flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
    }
    .vuedatepicker-clear-button {
        cursor:pointer;
        position:absolute;
        right:2.5rem;
        padding-right: .5rem;
    }
    .vuedatepicker-calendar-icon {
        width: 2em;
        border-left: 1px solid darkgrey;
        display: flex;
        align-items: center;
        justify-content: center;
        right: 1px;
        position: absolute;
    }
</style>
