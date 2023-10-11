var signatures = [];
class Signature {
    constructor(id, page, type, width, height, xAxis, yAxis, pageWidth, pageHeight) {
        this.id = id;
        this.Page = page;
        this.Type = type;
        this.Width = width;
        this.Height = height;
        this.XAxis = xAxis;
        this.YAxis = yAxis;
        this.pageWidth = pageWidth;
        this.pageHeight = pageHeight;
    }
}
class Text {
    constructor(id, matruong, page, type, size, font, noidung, width, height, xAxis, yAxis, pageWidth, pageHeight) {
        this.id = id;
        this.matruong = matruong;
        this.Page = page;
        this.Type = type;
        this.size = size;
        this.FontFamily = font;
        this.noidung = noidung;
        this.Width = width;
        this.Height = height;
        this.XAxis = xAxis;
        this.YAxis = yAxis;
        this.pageWidth = pageWidth;
        this.pageHeight = pageHeight;
    }
}
var signature = {
    textCount: 0,
    isView: false,
    currentCanvas: undefined,
    activeSignature: {},
    onObjectChange: undefined,
    onObjectViewChange: undefined,
    signatureTracking: undefined,
    init: function (data, scale, isView = false, signatures = [], image = "") {
        signature.isView = isView;
        if (!isView) {
            this.customFabricAction();
            $(".signature-content").draggable({helper: "clone", revert: true, revertDuration: 0});
            $(".doc-viewer canvas").droppable({
                accept: '.signature-content',
                tolerance: 'pointer',
                collision: "fit",
                live: true,
                drop: function (event, ui) {
                    dropped = true;
                }
            });
        }
        pdfLoad(data, scale, (canvas, fcanvas) => {
            if (!isView) {
                $(canvas).droppable({
                    accept: '.signature-content',
                    tolerance: 'pointer',
                    collision: "fit",
                    live: true,
                    drop: function (event, ui) {
                        dropped = true;
                        var type = ui.draggable[0].dataset.type;
                        leftPosition = ui.offset.left - $(this).offset().left;
                        topPosition = ui.offset.top - $(this).offset().top;
                        signature.drawimg(fcanvas, {top: topPosition, left: leftPosition}, type);
                    }
                });
            } else {
                signatures.forEach(s => {
                    signature.drawimg(fcanvas, {top: s.y, left: s.x, page: s.page_sign, width: s.width_size, height: s.height_size, url: image}, "signature");
                })
            }
            fcanvas.on({
                'object:modified': this.signatureEventHandler,
                'object:moving': this.positionSizedHandler,
                'object:scaling': this.positionSizedHandler,
                "mouse:up": this.mouseUpHandler
            });
        });

    },
    mouseUpHandler: function (e) {
        signature.currentCanvas = this;
        signature.activeSignature = this.getActiveObject();
        if (signature.isView) {
            if (signature.onObjectViewChange) {
                signature.onObjectViewChange(signature.activeSignature);
            }
        } else {
            if (signature.onObjectChange) {
                signature.onObjectChange(signature.activeSignature)
            }
        }
    },
    drawimg: function (canvas, p, type) {
        if (signature.isView) {
            angular.element(document.getElementById('doc-viewer')).scope().onDrawingViewFiles(canvas, p, type);
        } else {
            angular.element(document.getElementById('doc-viewer')).scope().onDrawingFiles(canvas, p, type);
        }
    },
    remove: function (target) {
        canvas.remove(target);
    },
    signatureEventHandler: function (e) {
        if (signature.isView) {
            if (signature.onObjectViewChange) {
                signature.onObjectViewChange(signature.activeSignature);
            }
        } else {
            if (signature.onObjectChange) {
                signature.onObjectChange(signature.activeSignature)
            }
        }
    },
    positionSizedHandler: function (e) {
        var action = e.transform.action;
        var obj = e.target;
        var scaled = obj.scaleX;
        var { width } = obj;
        var { height } = obj;
        var currentWidth = width * scaled;
        var currentHeight = height * scaled;
        var { canvas } = obj;
        var cWidth = canvas.width;
        var cHeight = canvas.height;
        var overFlowX = obj.left > cWidth - currentWidth;
        var overFlowY = obj.top > cHeight - currentHeight;
        switch (action) {
            case "drag":
                if (obj.top < 0)
                    obj.top = 0;

                if (obj.left < 0)
                    obj.left = 0;

                if (overFlowX)
                    obj.left = cWidth - currentWidth;

                if (overFlowY)
                    obj.top = cHeight - currentHeight;

                break;
            case "scale":
                if (overFlowX || overFlowY) {
                    var _maxScaleX = (cWidth - obj.left) / width;
                    var _maxScaleY = (cHeight - obj.top) / height;
                    obj.scale(Math.min(_maxScaleX, _maxScaleY));
                }
                break;
            default:
                break;
        }
    },
    customFabricAction: function () {
        var deleteIcon = "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='utf-8'%3F%3E%3C!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'%3E%3Csvg version='1.1' id='Ebene_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='595.275px' height='595.275px' viewBox='200 215 230 470' xml:space='preserve'%3E%3Ccircle style='fill:%23F44336;' cx='299.76' cy='439.067' r='218.516'/%3E%3Cg%3E%3Crect x='267.162' y='307.978' transform='matrix(0.7071 -0.7071 0.7071 0.7071 -222.6202 340.6915)' style='fill:white;' width='65.545' height='262.18'/%3E%3Crect x='266.988' y='308.153' transform='matrix(0.7071 0.7071 -0.7071 0.7071 398.3889 -83.3116)' style='fill:white;' width='65.544' height='262.179'/%3E%3C/g%3E%3C/svg%3E";
        var img = document.createElement('img');
        img.src = deleteIcon;
        fabric.Object.prototype.transparentCorners = false;
        fabric.Object.prototype.lockScalingFlip = true;

        fabric.Object.prototype.cornerColor = '#b2ccff';
        fabric.Object.prototype.cornerStyle = 'circle';
        fabric.Object.caches = false;
        fabric.Textbox.prototype.cornerStyle = 'circle';
        fabric.Textbox.prototype.controls.deleteControl = new fabric.Control({
            x: 0.5,
            y: -0.5,
            offsetY: 0,
            cursorStyle: 'pointer',
            mouseUpHandler: this.deleteObject,
            render: renderIcon,
            cornerSize: 36,
            caches: false
        });

        fabric.Object.prototype.controls.deleteControl = new fabric.Control({
            x: 0.5,
            y: -0.5,
            offsetY: 0,
            cursorStyle: 'pointer',
            mouseUpHandler: this.deleteObject,
            render: renderIcon,
            cornerSize: 39,
            caches: false
        });

        function renderIcon(ctx, left, top, styleOverride, fabricObject) {
            var size = this.cornerSize / 2;
            ctx.save();
            ctx.translate(left, top);
            ctx.rotate(fabric.util.degreesToRadians(fabricObject.angle));
            ctx.drawImage(img, - size / 2, - size / 2, size, size);
            ctx.restore();
        }
    },
    deleteObject: function (eventData, tg) {
        angular.element(document.getElementById('doc-viewer')).scope().onDeleteObject(eventData, tg);

    },
}

