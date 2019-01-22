﻿CKEDITOR.dialog.add("btgrid", function (d) {
    function c(a) {
        return function () {
            var b = this.getValue();
            (b = !!(CKEDITOR.dialog.validate.integer()(b) && 0 < b)) || alert(a);
            return b
        }
    }

    var b = d.lang.btgrid;
    return {
        title: b.editBtGrid, minWidth: 600, minHeight: 300, onShow: function () {
            var a = d.getSelection();
            a.getRanges();
            var b = this.getName(), c = this.getContentElement("info", "rowCount"),
                e = this.getContentElement("info", "colCount");
            "btgrid" == b && (a = a.getSelectedElement()) && (this.setupContent(a), c && c.disable(), e && e.disable())
        }, contents: [{
            id: "info",
            label: b.infoTab,
            accessKey: "I",
            elements: [{
                id: "colCount",
                type: "select",
                required: !0,
                label: b.selNumCols,
                items: [["2", 2], ["3", 3], ["4", 4], ["6", 6], ["12", 12]],
                validate: c(b.numColsError),
                setup: function (a) {
                    this.setValue(a.data.colCount)
                },
                commit: function (a) {
                    a.setData("colCount", this.getValue())
                }
            }, {
                id: "rowCount",
                type: "text",
                width: "50px",
                required: !0,
                label: b.genNrRows,
                validate: c(b.numRowsError),
                setup: function (a) {
                    this.setValue(a.data.rowCount)
                },
                commit: function (a) {
                    a.setData("rowCount", this.getValue())
                }
            }]
        }]
    }
});