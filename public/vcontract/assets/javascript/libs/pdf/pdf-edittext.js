var pdfEditText = {
    currentPage: undefined,
    pages: [],
    onTextChangeEvent: undefined,
    addAllTextBox: function (page, texts) {
        if (!page) return;
        for (var txt of texts) {
//            var page = this.pages.find(x => x.lowerCanvasEl.dataset.pageNo == txt.page);
//            
            var letterBreakingTextBox = new fabric.Textbox(txt.noidung, {
                id: txt.id,
                top: txt.YAxis,
                left: txt.YAxis,
                width: 200,
                fontSize: 30,
                textAlign: 'left', // you can use specify the text align
                splitByGrapheme: true,
            });
            letterBreakingTextBox.on("changed", function () { console.log("chagned") })
            page.add(letterBreakingTextBox);
            var textBox = new fabric.Textbox(txt.noidung, {
                backgroundColor: "#fffddf",
                id:txt.id,
                top: txt.yAxis,
                left: txt.xAxis,
                width: txt.width,
                fontSize: 12 * 1.5,
                textAlign: 'left',
                hoverCursor: "text",
                splitByGrapheme: true
            });
            textBox.setControlsVisibility({
                tl: false,
                mt: false,
                tr: false,
                bl: false,
                btm:false,
                br: false,
                mr: false,
                ml:false
            });
            if (this.onTextChangeEvent)
                textBox.on("changed", this.onTextChangeEvent);
            page.add(textBox);
        }
    }
}