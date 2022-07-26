<template>
    <div ref="container" style="position:relative">
        <div :class="getCSSClass('vuedatepicker-inputgroup')" :ref="'inputgroup'">
            <label v-if="formElementLabel != ''">{{ formElementLabel }}</label>
            <template v-if="disabled">
                <input v-model="dateLabel"
                       :class="getCSSClass('vuedatepicker-input') + ' ' + inputClass"
                       readonly
                       disabled
                >
            </template>
            <template v-else>
                <div style="position: relative; width: 100%;">
                    <div style="position:relative">
                        <input v-model="dateLabel"
                               :class="getCSSClass('vuedatepicker-input') + ' ' +inputClass"
                               @click="toggleDatepickerDropdown"
                               readonly
                               style="width: 100%"
                        >
                        <div style="position: absolute; right: 0; top: 0; height: 100%; display: flex;">
                            <span v-on:click="resetDate"
                                  v-show="dateValue != null"
                                  :class="getCSSClass('vuedatepicker-clear-button')"
                                  style="margin-right: .5rem"
                                  v-html="icon('close')"
                            ></span>
                                <span :class="getCSSClass('vuedatepicker-calendar-icon')"
                                      v-on:click="toggleDatepickerDropdown"
                                      style="cursor:pointer; margin-right: .5rem"
                                      v-html="icon('calendar')"
                                ></span>
                        </div>
                    </div>
                    <span v-if="showTimeInputs == 'true'" :class="getCSSClass('vuedatepicker-time-inputs-container')">
                        <input type="text" v-model="hour" style="width: 2em">
                        <span>:</span>
                        <input type="text" v-model="minute" style="width: 2em">
                        <span>:</span>
                        <input type="text" v-model="second" style="width: 2em">
                    </span>
                </div>
            </template>
        </div>
        <div :class="dropdownClass" v-if="showDropdownFlag" :ref="'dropdown'">
            <div :class="getCSSClass('vuedatepicker-inputs-container')" style="width: 100%; display: flex; align-items: center; justify-content: space-between">
                <input v-model="year" type="number" :class="getCSSClass('vuedatepicker-year-input')" style="max-width: 6rem">
                <select v-model="month" :class="getCSSClass('vuedatepicker-month-select')">
                    <option v-for="monthname, monthindex in months"
                            v-bind:value="monthindex"
                            v-html="monthname"></option>
                </select>
                <button type="button"
                        v-on:click="gotoToday"
                        :class="getCSSClass('vuedatepicker-today-button')"
                        v-if="showTodayButton"
                        v-html="icon('today')"
                ></button>
            </div>
            <div :class="getCSSClass('vuedatepicker-inputs-container')">
                <table :class="getCSSClass('vuedatepicker-days-table')">
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
    export default {
        inject: ['loadingIndicator', 'getCSSClass', 'translate', 'icon'],
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
                openUpwards: false,
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
            dropdownClass: function() {
                let result = this.getCSSClass('vuedatepicker-dropdown');
                if (this.openUpwards) {
                    result = result + ' vuedatepicker-dropdown-upwards';
                }

                return result;
            }
        },
        methods: {
            checkUpwardsOpening: function() {
                this.openUpwards = this.$refs['inputgroup'].getBoundingClientRect().y > window.innerHeight * .75;
            },
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
                this.checkUpwardsOpening();
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
        padding:1px;
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
        transition: border 200ms ease-in-out;
    }
    .vuedatepicker-days-table td:hover{
        border: 1px solid black
    }
    .vuedatepicker-current-month {
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
    .vuedatepicker-time-inputs-container {
        display: flex;
        align-items: center;
        margin-left: 5px;
        margin-right: 5px;
    }
    .vuedatepicker-time-inputs-container > span {
        padding-left: 3px;
        padding-right: 3px;
    }
    .vuedatepicker-time-inputs-container > input {
        text-align: center;
        padding-left: 1px;
        padding-right: 1px;
        border-radius: 2px;
    }
</style>
