<template>
    <div ref="container">
        <div class="input-group vue-datepicker-inputgroup">
            <label v-if="formElementLabel != ''">{{ formElementLabel }}</label>
            <div class="input-group-append vue-datepicker-inputgroup-append">
                <input v-model="dateLabel"
                       class="form-control vuedatepicker-input"
                       v-bind:class="inputClass"
                       @click="toggleDatepickerDropdown"
                       readonly
                >
                <span v-show="dateValue == null" class="input-group-text" v-on:click="toggleDatepickerDropdown"><i class="fa fa-calendar"></i></span>
                <span v-if="showTimeInputs == 'true'" class="vue-datepicker-time-inputs-container">
                    <input type="text" v-model="hour" style="width: 2em">
                    <span>:</span>
                    <input type="text" v-model="minute" style="width: 2em">
                    <span>:</span>
                    <input type="text" v-model="second" style="width: 2em">
                </span>
                <span v-show="dateValue != null" class="input-group-text" v-on:click="resetDate"><span class="vuedatepicker-clear-button">X</span></span>
            </div>
        </div>
        <div>
            <div class="vuedatepicker-dropdown" v-if="showDropdownFlag">
                <div class="vuedatepicker-inputs-container">
                    <input v-model="year" type="number" class="form-control vuedatepicker-year-input">
                    <select v-model="month" class="form-control vuedatepicker-month-select">
                        <option v-for="monthname, monthindex in months"
                                v-bind:value="monthindex"
                                v-html="monthname"></option>
                    </select>
                    <button type="button" v-on:click="gotoToday" class="vuedatepicker-today-button">&#x2600;</button>
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
    </div>
</template>

<script>
    export default {
        props: {
            formElementLabel: {type: String, default: ''},
            value: {},
            locale: {type: String, default: 'hu'},
            inputClass: {type: String, default: ''},
            showTimeInputs: {type: String, default: 'false'}
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
            }
        },
        mounted() {
            this.todaysDate = new Date();
            if (typeof(this.value) != 'undefined') {
                if (this.dateRegex.test(this.value)) {
                    var datetimeparts = this.value.split(' ');
                    var dateparts = datetimeparts[0].split('-');
                    this.year = parseInt(dateparts[0]);
                    this.month = parseInt(dateparts[1]) - 1;
                    this.day = parseInt(dateparts[2]);
                    if (datetimeparts.length == 2) {
                        var timeparts = datetimeparts[1].split(':');
                        this.hour = parseInt(timeparts[0]);
                        this.minute = parseInt(timeparts[1]);
                        this.second = parseInt(timeparts[2]);
                    } else {
                        this.hour = 0;
                        this.minute = 0;
                        this.second = 0;
                    }
                    this.dateValue = new Date(this.year, this.month, this.day, this.hour, this.minute, this.second);
                } else {
                    if ((typeof(this.value) == 'object') && (this.value instanceof Date)) {
                        this.year = this.value.getFullYear();
                        this.month = this.value.getMonth();
                        this.day = this.value.getDate();
                        this.hour = this.value.getHours();
                        this.minute = this.value.getMinutes();
                        this.second = this.value.getSeconds();
                        this.dateValue = this.value;
                        //this.$emit('input', this.year+'-'+(this.month+1)+'-'+this.day)
                        this.valueIsObject = true;
                    } else {
                        this.gotoToday();
                    }
                }
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
            calculateDateValue: function() {
                this.dateValue = new Date(this.year, this.month, this.day, this.hour, this.minute, this.second);
                this.dateLabel = this.year + '-' + (this.month + 1).toString().padStart(2, 0) + '-' + this.day.toString().padStart(2, 0);
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
            setDayByWeekAndDayIndex: function(weekIndex, dayIndex) {
                var selectedDate = this.dateByWeekAndDayIndex(weekIndex, dayIndex);
                this.year = null;
                this.year = selectedDate.getFullYear();
                this.month = selectedDate.getMonth();
                this.day = selectedDate.getDate();
                this.hideDatepickerDropdown();
            },
            dateByWeekAndDayIndex: function(weekIndex, dayIndex) {
                var startingDate = this.tableStartingDay;
                var index = (weekIndex * 7) + dayIndex;
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
            resetDate: function() {
                this.hideDatepickerDropdown();
                this.dateLabel = '';
                this.dateValue = null;
                this.$emit('input', null);
            }
        },
        watch: {
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
            left: 15px;
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

    .vuedatepicker-inputs-container {
        display:flex;
        justify-content: space-between;
    }
    .vuedatepicker-year-input {
        width:30% !important;
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
        margin-left: 5px;
        margin-right: 5px;
    }
    .vue-datepicker-time-inputs-container > span {
        padding-left: 3px;
        padding-right: 3px;
    }
    .vue-datepicker-time-inputs-container > input {
        text-align: center;
    }
</style>