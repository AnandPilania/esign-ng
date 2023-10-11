pdfjsLib.GlobalWorkerOptions.workerSrc = '/vcontract/assets/javascript/libs/pdfjs/pdf.worker.js';
window.fCanvas = [];
var pdfLoad = (data, _scale, callback) => {
    let loadingTask = pdfjsLib.getDocument(data),
        pdfDoc = null,
        stopScroll = false,
        numPage = 1;
    var scale = _scale
    if (scale != document.getElementById("scale").value) {
        document.getElementById("scale").value = scale;
    }
    const GeneratePDF = (numPage_, scale_) => {
        UpdatePageNumber();
        if (IsPageLoaded(numPage_))
            return new Promise(resolve => resolve("Page loaded"));
        return pdfDoc.getPage(numPage_).then(page => {
            let viewport = page.getViewport({ scale: scale_ });
            const canvas = document.createElement("canvas");

            canvas.id = `canvas-page-${numPage_}`;
            canvas.dataset.pageNo = numPage_;
            document.getElementById(`page-${numPage_}`).appendChild(canvas);
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            const ctx = canvas.getContext('2d');
            let renderContext = {
                canvasContext: ctx,
                viewport: viewport
            }
            var ps = page.render(renderContext).promise;
            ps.then(() => {
                const bg = canvas.toDataURL("image/png");
                var fcanvas = new fabric.Canvas(canvas);
                fcanvas.setBackgroundImage(bg, fcanvas.renderAll.bind(fcanvas),
                    {
                        originX: 'left',
                        originY: 'top'
                    });
                RemoveLoading(numPage_);
                fCanvas.push(fcanvas);
                callback(canvas, fcanvas);
            });
        });
    }
    const GotoPage = (numPage) => {
        stopScroll = true;
        const _toPage = document.getElementById(`page-${numPage}`);
        const topPos = _toPage.offsetTop;
        $("#doc-viewer").animate({
            scrollTop: topPos
        }, 1000, function () {
            stopScroll = false;
        });
    }
    const IsPageLoaded = (numPage) => {
        return document.querySelector(`#page-${numPage} canvas`) != null;
    }
    const RemoveLoading = (numPage) => {
        document.querySelector(`#page-${numPage} .spinner`).remove();
    }
    const UpdatePageNumber = () => {
        document.querySelector('#page-num').textContent = numPage;
    }
    const RenderPageContainer = () => {
        document.getElementById("viewer").innerHTML = "";
        const _totalPage = pdfDoc.numPages;
        document.getElementById("total-page").innerHTML = _totalPage;
        for (let i = 0; i < _totalPage; i++) {
            const page = document.createElement("div");
            page.id = `page-${i + 1
                }`;
            page.setAttribute("data-page", i + 1);
            page.className = "viewer-canvas-container page";
            page.innerHTML = `<div class="spinner"><div class="spinner-grow text-light" role="status">
                                <span class="sr-only">Loading...</span>
                            </div></div>`;
            document.getElementById("viewer").appendChild(page);
            pdfDoc.getPage(i + 1).then(p => {
                let _viewport = p.getViewport({ scale: scale });
                page.style.minHeight = _viewport.height + "px";
            });
        }
    }
    const SetPageSize = () => {
        const _pages = document.querySelectorAll("#viewer .page");
        for (let i = 0; i < _pages.length; i++) {
            const _page = _pages[i];
            const _numPage = Number(_page.getAttribute("data-page"));
            pdfDoc.getPage(_numPage).then(p => {
                let _viewport = p.getViewport({ scale: scale });
                _page.style.width = _viewport.width + "px";
                _page.style.minHeight = _viewport.height + "px";
            })
        }
    }
    const PrevPage = () => {
        if (numPage === 1) {
            return;
        }
        numPage--;
        if (!IsPageLoaded(numPage))
            GeneratePDF(numPage, scale).then(console.log("done1"));
        UpdatePageNumber();
        GotoPage(numPage);
    }

    const NextPage = () => {
        if (numPage < pdfDoc.numPages) {
            numPage++;
            if (!IsPageLoaded(numPage))
            GeneratePDF(numPage, scale).then(console.log("done1"));


            UpdatePageNumber();
            GotoPage(numPage);
        }
    }
    const GoToPageOnInput = (e) => {
        e.preventDefault();
        const _numPage = Number(document.querySelector('#page-num').textContent);
        if (_numPage > pdfDoc.numPages || _numPage < 1) {
            return;
        }
        GeneratePDF(_numPage);
        numPage = _numPage;
        GotoPage(numPage);
    }
    document.getElementById("doc-viewer").addEventListener("scroll", function (e) {
        if (stopScroll)
            return;
        const scrollPos = this.scrollTop;
        const _pages = this.querySelectorAll(".viewer-canvas-container");
        let _numPage = 0;
        let s = document.getElementById("scale").value;
        for (let i = 0; i < _pages.length; i++) {
            const _page = _pages[i];
            const _start = _page.offsetTop;
            const _end = _start + _page.scrollHeight;
            if (_start <= scrollPos && scrollPos < _end) {
                _numPage = _page.getAttribute("data-page");
                break;
            }
        }
        if (_numPage === 0) return;
        if (_numPage != numPage) {
            numPage = Number(_numPage);
            if (!IsPageLoaded(numPage)) {
                GeneratePDF(numPage, s);
            }
            if (numPage + 1 <= pdfDoc.numPages && !IsPageLoaded(numPage + 1))
                GeneratePDF(numPage + 1, s);

            UpdatePageNumber();
        }
    });
    document.querySelector('#prev').addEventListener('click', PrevPage);
    document.querySelector('#next').addEventListener('click', NextPage);
    loadingTask.promise.then(pdfDoc_ => {
        let s = document.getElementById("scale").value;
        pdfDoc = pdfDoc_;
        RenderPageContainer();
        if (numPage > 1) {
            GeneratePDF(numPage + 1, s);
        }
        else {
            GeneratePDF(numPage, s);
        }
    });
}
