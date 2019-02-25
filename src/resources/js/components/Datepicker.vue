<template>
    <div ref="container">
        <div class="input-group vue-datepicker-inputgroup">
            <label v-if="formElementLabel != ''">{{ formElementLabel }}</label>
            <div class="input-group-append vue-datepicker-inputgroup-append">
                <input v-model="dateLabel"
                       class="form-control vuedatepicker-input"
                       v-bind:class="datepickerInputClass"
                       @click="toggleDatepickerDropdown"
                       readonly
                >
                <span v-show="dateValue == null" class="input-group-text" v-on:click="toggleDatepickerDropdown"><i class="fa fa-calendar"></i></span>
                <span v-show="dateValue != null" class="input-group-text" v-on:click="resetDate"><i class="fa fa-times vuedatepicker-clear-button"></i></span>
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
        props: [
            'formElementLabel',
            'value',
            'locale',
            'inputClass'
        ],
        data: function() {
            return {
                dateValue: null,
                dateLabel: null,
                year: null,
                month:null,
                day:null,
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
                dateRegex: new RegExp('^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$'),
                showDropdownFlag: false,
                todaysDate: null
            }
        },
        mounted() {
            this.todaysDate = new Date();
            if (typeof(this.value) != 'undefined') {
                if (this.dateRegex.test(this.value)) {
                    var dateparts = this.value.split('-');
                    this.year = parseInt(dateparts[0]);
                    this.month = parseInt(dateparts[1]) - 1;
                    this.day = parseInt(dateparts[2]);
                    this.dateValue = new Date(this.year, this.month, this.day);
                }
            } else {
                this.gotoToday();
            }
        },
        computed: {
            datepickerInputClass: function() {
                return typeof(this.inputClass) == 'undefined' ? '' : this.inputClass;
            },
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
                this.dateValue = new Date(this.year, this.month, this.day);
                this.dateLabel = this.year + '-' + (this.month + 1).toString().padStart(2, 0) + '-' + this.day.toString().padStart(2, 0);
                this.$emit('input', this.dateLabel)
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
        }
    }
</script>
<style>

</style>