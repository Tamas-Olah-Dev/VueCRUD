<template>
    <div class="trix-table-editor-container">
        <div class="trix-table-editor-buttons-row">
            <button v-on:click="addRow">Új sor</button>
            <button v-on:click="deleteRow">Sor törlése</button>
            <button v-on:click="addColumn">Új oszlop</button>
            <button v-on:click="deleteColumn">Oszlop törlése</button>
        </div>
        <table style="width: 100%; border: 1px solid darkgrey;" class="trix-table-editor-table">
            <tr v-for="row, rowIndex in table">
                <td v-for="column, columnIndex in row"
                    v-bind:class="cellClass(rowIndex, columnIndex)"
                    v-bind:style="{'width': 100 / row.length+'%'}"
                    v-on:click="currentRow = rowIndex; currentColumn = columnIndex"
                    v-bind:data-row-index="rowIndex"
                    v-bind:data-column-index="columnIndex"
                >
                    <div v-if="currentRow != rowIndex || currentColumn != columnIndex"
                         v-html="column"
                         class="trix-table-editor-inactive-cell"
                    ></div>
                    <trix-wrapper v-bind:fieldname="'table-'+currentRow+'-'+currentColumn"
                                  v-bind:value="table[rowIndex][columnIndex]"
                                  v-on:input="setCellValue($event, rowIndex, columnIndex)"
                                  v-show="currentRow == rowIndex && currentColumn == columnIndex"
                    ></trix-wrapper>
                </td>
            </tr>
        </table>
    </div>
</template>

<script>
    export default {
        props: {
            value: {type: Array, default: () => {return [['']]}},
        },
        data: function() {
            return {
                table: [['']],
                currentRow: 0,
                currentColumn: 0,
            }
        },
        mounted() {
            this.table = this.value;
        },
        methods: {
            cellClass: function(rowIndex, columnIndex) {
                if ((rowIndex == this.currentRow) && (columnIndex == this.currentColumn)) {
                    return 'trix-table-editor-td';
                }
                if (rowIndex == this.currentRow) {
                    return 'trix-table-editor-td trix-table-editor-horizontal-crosshair';
                }
                if (columnIndex == this.currentColumn) {
                    return 'trix-table-editor-td trix-table-editor-vertical-crosshair';
                }
                return 'trix-table-editor-td';
            },
            setCellValue: function(payload, rowIndex, columnIndex) {
                console.log({r: rowIndex, c: columnIndex, p: payload});
                Vue.set(this.table[rowIndex], columnIndex, payload);
            },
            addRow: function() {
                let newRow = [];
                for (var i = 0; i < this.table[0].length; i++) {
                    newRow.push('');
                }
                this.table.splice(this.currentRow + 1, 0, newRow);
                this.emitChange();
            },
            addColumn: function() {
                for (var i = 0; i < this.table.length; i++) {
                    this.table[i].splice(this.currentColumn + 1, 0, '');
                }
                this.emitChange();
            },
            deleteRow: function() {
                if (this.table.length > 1) {
                    this.table.splice(this.currentRow, 1);
                    this.emitChange();
                }
            },
            deleteColumn: function() {
                if (this.table[0].length > 1) {
                    for (var i = 0; i < this.table.length; i++) {
                        this.table[i].splice(this.currentColumn, 1);
                    }
                    this.emitChange();
                }
            },
            emitChange: function() {
                this.$emit('input', this.table)
            }
        }
    }
</script>
<style>
    .trix-table-editor-container {
        display: flex;
        flex-direction: column;
    }
    .trix-table-editor-buttons-row {
        display: flex;
        flex-direction: row;
    }
    .trix-table-editor-table td {
        height: 2em;
        //min-width: 10em;
        opacity: 1;
    }
    .trix-table-editor-inactive-cell {
        opacity: .6;
    }
    .trix-table-editor-td {
        border: 2px solid lightgrey;
    }
    .trix-table-editor-horizontal-crosshair {
        border-top: 2px solid black !important;
        border-bottom: 2px solid black !important;
    }
    .trix-table-editor-vertical-crosshair {
        border-left: 2px solid black !important;
        border-right: 2px solid black !important;
    }
</style>