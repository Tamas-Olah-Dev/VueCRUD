<template>
    <div class="trix-table-editor-container">
        <div class="trix-table-editor-buttons-row">
            <button type="button" v-on:click="addRow">Új sor</button>
            <button type="button" v-on:click="deleteRow">Sor törlése</button>
            <button type="button" v-on:click="addColumn">Új oszlop</button>
            <button type="button" v-on:click="deleteColumn">Oszlop törlése</button>
            <button type="button" v-on:click="saveTable" style="margin-left:auto">Mentés és bezárás</button>
            <button type="button" v-on:click="$emit('cancel')">Mégsem</button>
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
                    <div v-show="currentRow != rowIndex || currentColumn != columnIndex"
                         v-html="column"
                         class="trix-table-editor-inactive-cell"
                    ></div>
                    <trix-wrapper v-bind:fieldname="'table-'+rowIndex+'-'+columnIndex"
                                  v-model="table[rowIndex][columnIndex]"
                                  v-show="currentRow == rowIndex && currentColumn == columnIndex"
                                  allow-table-operations="false"
                                  allow-preview="false"
                    ></trix-wrapper>
                    <!--
                    <input type="text" v-model="table[rowIndex][columnIndex]" v-show="currentRow == rowIndex && currentColumn == columnIndex">
                    -->
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
            convertTableToTrixAttachment: function() {
                let content = '<table>';
                for (let rowIndex = 0; rowIndex < this.table.length; rowIndex++) {
                    content = content + '<tr>'
                    for (let columnIndex = 0; columnIndex < this.table[rowIndex].length; columnIndex++) {
                        content = content + '<td>'+this.table[rowIndex][columnIndex]+'</td>';
                    }
                    content = content + '</tr>';
                }
                content = content + '<table>';
                return new Trix.Attachment({content: content});
            },
            cellClass: function(rowIndex, columnIndex) {
//                if ((rowIndex == this.currentRow) && (columnIndex == this.currentColumn)) {
//                    return 'trix-table-editor-td';
//                }
//                if (rowIndex == this.currentRow) {
//                    return 'trix-table-editor-td trix-table-editor-horizontal-crosshair';
//                }
//                if (columnIndex == this.currentColumn) {
//                    return 'trix-table-editor-td trix-table-editor-vertical-crosshair';
//                }
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
            },
            addColumn: function() {
                for (var i = 0; i < this.table.length; i++) {
                    this.table[i].splice(this.currentColumn + 1, 0, '');
                }
            },
            deleteRow: function() {
                if (this.table.length > 1) {
                    this.table.splice(this.currentRow, 1);
                }
            },
            deleteColumn: function() {
                if (this.table[0].length > 1) {
                    for (var i = 0; i < this.table.length; i++) {
                        this.table[i].splice(this.currentColumn, 1);
                    }
                }
            },
            saveTable: function() {
                this.$emit('input', this.convertTableToTrixAttachment());
                this.$emit('editing-finished');
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
    .trix-table-editor-buttons-row > button {
        position: relative;
        float: left;
        color: rgba(0, 0, 0, 0.6);
        font-size: 0.75em;
        font-weight: 600;
        white-space: nowrap;
        padding: 0 0.5em;
        margin: 0;
        margin-bottom: 10px;
        outline: none;
        border: none;
        border-bottom: 1px solid #888;
        border-radius: 0;
        background: white;
        border: 1px solid #bbb;
        border-top-color: #ccc;
        border-radius: 3px;
        height: 1.6em;
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