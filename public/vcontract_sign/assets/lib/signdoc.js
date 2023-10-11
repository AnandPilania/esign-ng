var sigs;
var signDoc = {
    //endPoints: {
    //    getDoc: "/document/getdocument",
    //    getSignatures: "/document/GetDocumentInfo",
    //    updateText: "/document/updatetextanonymous"
    //},
    data: {},
    info: undefined,
    refs: "",
    currentCanvas: undefined,
    signDone: undefined,
    remoteData: undefined,
    init: function () {
        if (!this.signDone) this.signDone = this.updateDoc;
        signTool.connect(function (msg) {
            console.log(msg);
        });
        var refs = $("#refs").val();
        var masothueOnlyMe = $("#masothueOnlyMe").val();
        if (refs) {
            this.refs = encodeURIComponent(refs);
            this.loadDocFromRef(refs);
        }

        $("#upload-signature-img").on("change",
            function () {
                if (!commonService.isFileType(this.files[0], "image")) {
                    notificationService.error("Chữ ký chỉ hỗ trợ định dạng ảnh", "Thông báo");
                    return;
                }
                else {
                    commonService.fileToBase64(this).then(image => {
                        var sig = image[0].base64;
                        signDoc.updateSignature(sig);
                    });
                }
            });
        $('#btn-create-signature').on("click",
            function (e) {
                if (!signDoc.currentCanvas) {
                    signDoc.currentCanvas = signature.currentCanvas;
                }
                if (!signDoc.currentCanvas || !signDoc.currentCanvas.getActiveObject()) {
                    notificationService.error("Vui lòng chọn chữ ký!");
                    return;
                }
                var target = $(this).data("target");
                $(target).modal("show");
            });
        $('#signature-creator-modal').on('shown.bs.modal',
            function () {
                if (!signDoc.currentCanvas) {
                    signDoc.currentCanvas = signature.currentCanvas;
                }
                var canvas = $("#signature-creator-canvas")[0];
                var selected = signDoc.currentCanvas.getActiveObject();
                canvas.offsetWidth = selected.width;
                canvas.offsetHeight = selected.height;
                signatureCreator.init("signature-creator-canvas");
            });
        $("#btn-signature-save").on("click",
            () => {
                var img = signatureCreator.toPng();
                this.updateSignature(img);
            });
        $("#btn-text-save").on("click",
            () => {
                this.updateText();
            });
        $("#btn-sign-doc").on("click",
            () => {
                var isCheck = this.data.signatures.find(x => (x.Img == null || x.Img == undefined));
                if (isCheck) {
                    notificationService.error("Vui lòng thực hiện ký vào vùng ký đã xác định của bạn trên tài liệu!");
                    return;
                }
                else {
                    commonService.loadingOn();
                    signTool.connect(function (msg) {

                    });
                    setTimeout(function () {
                        if (signTool.isConnected === false) {
                            notificationService.error(`Không thể kết nối với Plugin hỗ trợ ký số tài liệu. Vui lòng kiểm tra và chắc chắn rằng Plugin này đã cài đặt trên máy tính và đang mở`);
                            return false;
                        }
                        else {
                            commonService.loadingOn();
                            var rq = { "action": "PLUGIN_SIGNING", "data": signDoc.data };
                            var data = BSON.serialize(rq);
                            signTool.sendBinary(data, function (rp) {
                                if (rp.data === 'NO_CERT' || rp.data === 'CERT_CANCEL' || rp.data === 'CERT_EXPIRED' || rp.data === 'CERT_NOTMATCH' || rp.data === 'CERT_NOPRIVATERKEY') {
                                    const msg = signDoc.getMsg(rp.data);
                                    notificationService.error(msg);
                                }
                                else {
                                    var buffer = rp.data;
                                    signDoc.data.outputDocument = buffer;
                                    signDoc.signDone(buffer);
                                }
                                commonService.loadingOff();
                            });
                        }
                    }, 1000);
                    commonService.loadingOff();
                }
            });
        $("#print-doc").on("click",
            e => {
                this.printDoc();
            });
        $("#download-doc").on("click",
            e => {
                this.downLoadDoc();
            });
    },
    loadDocFromRef: function (refs) {
        if (this.refs) {
            fabric.Object.prototype.set({
                borderColor: 'red',
                borderWidth: 20,
                cornerStyle: 'circle',
                stroke: 'red',
                strokeWidth: 10,
                transparentCorners: false
            });
            fabric.Object.prototype.transparentCorners = false;
            fabric.Object.prototype.lockScalingFlip = true;
            fabric.Object.prototype.lockMovementX = true;
            fabric.Object.prototype.lockMovementY = true;
            fabric.Object.prototype.setControlsVisibility({
                mt: false, // middle top disable
                mb: false, // midle bottom
                ml: false, // middle left
                mr: false, // middle right
                tl: false, // top left
                tr: false, //top right
                bl: false, // bottom left
                mtr: false
            });
            //this.getDocumentInfo(refs).then(rp => {
            //    console.log(rp);
            //    var info = rp.data;

            //    $("#doc-name").text(rp.data.fileName);
            //    this.info = rp.data;
            //    var nguoithamgias = rp.data.nguoithamgias;
            //    this.renderParticipantPanel(nguoithamgias);
            //    var person = nguoithamgias.filter(x => x.current)[0];
            //    if (info.canEdit) {
            //        $("#edit-text-block").removeClass("hidden");
            //    }
            //    if (info.isSign && !person.isSigned) {
            //        this.showSignPanel();
            //    }
            //    this.data.person = person;
            //    this.data.signatures = person.signatures;
            //    this.data.texts = info.texts;
            //    this.getDoc(refs).then(doc => {
            //        //var byteArray = new Uint8Array(rp);
            //        this.data.document = doc;
            //        pdfLoad(doc,
            //            (c, fc) => {
            //                if (info.isSign) {
            //                    var cPage = parseInt(c.dataset.pageNo);
            //                    console.log(cPage);
            //                    var sigs = person.signatures.filter(x => x.page === cPage);
            //                    if (!person.isSigned) {
            //                        this.drawSignatures(fc, sigs);
            //                    }

            //                    fc.on({
            //                        "mouse:up": this.selectSignatureHandler
            //                    });
            //                } else {
            //                    //                                pdfEditText.pages = window.fCanvas;
            //                    var pageNo = fc.lowerCanvasEl.dataset.pageNo;
            //                    var textForPage = info.texts.filter(x => x.page == pageNo);
            //                    pdfEditText.onTextChangeEvent = function () {
            //                        var current = signDoc.data.texts.find(x => x.id == this.id);
            //                        if (current) current.noidung = this.text;
            //                    };
            //                    pdfEditText.addAllTextBox(fc, textForPage);
            //                }

            //            });
            //    }).catch(err => {
            //        //
            //    });
            //});

        } else {
            //bad request
        }
    },
    drawSignatures: function (fc, sigs) {
        if (sigs && sigs.length > 0) {
            console.log(sigs);
            $.each(sigs,
                function (i, e) {
                    fabric.Image.fromURL("/images/signature/signature-icon.svg",
                        img => {
                            img.set({
                                participant: 0,
                                person: 0,
                                signature: i,
                                sigW: e.width,
                                sigH: e.height,
                                page: e.page,
                                top: e.yAxis,
                                left: e.xAxis,
                                angle: 0,
                                opacity: 1,
                                sign_type: 1,
                                hoverCursor: "pointer",
                                lockMovementX: true,
                                lockMovementY: true,
                                lockUniScaling: true,
                                lockScalingX: true,
                                lockScalingY: true,
                                hasControls: false
                            });
                            fc.add(img);
                        });

                });
        }
    },
    //getDoc: function (refs) {
    //    return callApiService.getBinary(this.endPoints.getDoc + "?refs=" + this.refs);
    //},
    //getDocumentInfo: function (refs) {
    //    return callApiService.get(callApiService.econtractApi +
    //        this.endPoints.getSignatures +
    //        "?refs=" +
    //        this.refs);
    //},
    renderParticipantPanel: function (nguoithamgias) {
        var el = "";
        $.each(nguoithamgias,
            function (i, e) {
                var sig = e.signatures;
                var hasChild = sig && sig.length > 0;
                var isSigned = e.isSigned;
                el +=
                    `<li>${hasChild && isSigned ? '<i class="fas fa-angle-right expand">' : ''
                    }</i><span><i class="${isSigned ? 'fas fa-user-check' : 'fas fa-user-clock'} text-success"></i> ${e.HoVaTen} <i>(${e.Email})</i></span>`;
                if (isSigned) {
                    el += '<ul class="nested">';
                    $.each(sig,
                        function (i, s) {
                            el += `<li><a href="#page-${s.page}"><i class="fas fa-signature"></i> Chữ ký số ${i + 1}</a></li>`;
                        });
                    el += "</ul>";
                    el += `</li>`;
                }

            });
        $("#participants > ul").html(el);
    },
    showSignPanel: function () {
        $("#sign-right-panel").removeClass("hidden");
    },
    selectSignatureHandler: function (e) {
        signDoc.currentCanvas = this;
    },
    updateSignature: function (data, all) {
        if (!signDoc.currentCanvas) {
            signDoc.currentCanvas = signature.currentCanvas;
        }
        if (all) {
            $.each(window.fCanvas,
                function (i, e) {
                    var p = [];
                    var sigs = e.getObjects();
                    if (sigs.length > 0) {
                        sigs.filter(s => s.sign_type == 1);
                        $.each(sigs,
                            function (si, s) {
                                p.push(new Promise((resolve, reject) => {
                                    s.setSrc(data, function () {
                                        resolve();
                                    });
                                }));
                            });
                    }
                    Promise.all(p).then(() => {
                        e.renderAll();
                    });
                });

        } else {
            var selected = signDoc.currentCanvas.getActiveObject();
            if (!selected) {
                notificationService.error("Vui lòng chọn chữ ký số trên tài liệu trước khi thực hiện chức năng này", "Thông báo");
                return;
            }
            var w = selected.sigW;
            var h = selected.height;
            var sigIdx = selected.signature;
            this.data.signatures[sigIdx].Img = data;
            var s = selected.setSrc(data, function (Img, err) {
                Img.scaleToWidth(w);
                Img.scaleToWidth(w);
                signDoc.currentCanvas.renderAll();
            });
        }

    },
    
    printDoc: function (data) {
        if (!data) data = this.data.outputDocument ? this.data.outputDocument : this.data.document;
        var blobUrl = commonService.blobUrlFromBinary(data);
        printJS(blobUrl);
    },
    downLoadDoc: function (data) {
        if (!data) data = this.data.outputDocument ? this.data.outputDocument : this.data.document;
        commonService.downloadFormBinary(data, this.info.fileName);
    },
    getMsg: function (code) {
        switch (code) {
            case "NO_CERT":
                return "Không tìm thấy chứng thư số";
            case "CERT_CANCEL":
                return "Vui lòng chọn chứng thư số";
            case "CERT_EXPIRED":
                return "Chứng thư số đã hết hạn";
            case "CERT_NOTMATCH":
                return "Mã số thuế trên chứng thư số không đúng";
            case "CERT_NOPRIVATERKEY":
                return "Chứng thư số bị lỗi";
            default:
                return code;
        }
    },
}