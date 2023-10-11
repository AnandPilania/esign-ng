var signatures = [];
class SignatureSample {
    constructor(id, description, page, type, width, height, xAxis, yAxis, pageWidth, pageHeight, isAutoSign, fullName, email, phone, nationalId, imageUrl, orderAssignee, isMyOrganization, isEditable, signingMethod, notiType) {
        this.id = id;
        this.description = description;
        this.Page = page;
        this.Type = type;
        this.Width = width;
        this.Height = height;
        this.XAxis = xAxis;
        this.YAxis = yAxis;
        this.pageWidth = pageWidth;
        this.pageHeight = pageHeight;
        this.is_auto_sign = isAutoSign;
        this.full_name = fullName;
        this.email = email;
        this.phone = phone;
        this.national_id = nationalId;
        this.image_signature = imageUrl;
        this.order_assignee = orderAssignee;
        this.is_my_organisation = isMyOrganization;
        this.is_editable = isEditable;
        this.signing_method = signingMethod;
        this.noti_type = notiType;
    }
}
class TextSample {
    constructor(id, content, description, page, type, size, font, noidung, width, height, xAxis, yAxis, pageWidth, pageHeight, isRequired, isEditable, formName, isCode, isTime, fieldCode) {
        this.id = id;
        this.content = content;
        this.description = description;
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
        this.is_required = isRequired;
        this.is_editable = isEditable;
        this.form_name = formName;
        this.is_code = isCode;
        this.is_time = isTime;
        this.field_code = fieldCode;
    }
}
class CheckboxSample {
    constructor(id, content, description, page, type, width, height, xAxis, yAxis, pageWidth, pageHeight, isRequired, isEditable, formName, formDescription, fieldCode) {
        this.id = id;
        this.content = content;
        this.description = description;
        this.Page = page;
        this.Type = type;
        this.Width = width;
        this.Height = height;
        this.XAxis = xAxis;
        this.YAxis = yAxis;
        this.pageWidth = pageWidth;
        this.pageHeight = pageHeight;
        this.is_required = isRequired;
        this.is_editable = isEditable;
        this.form_name = formName;
        this.form_description = formDescription;
        this.field_code = fieldCode
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
    init: function (data, scale, isView = false, signatures = [], image = "", texts = []) {
        signature.isView = isView;
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
        pdfLoad(data, scale, (canvas, fcanvas) => {
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
            // signatures.forEach(s => {
            //     signature.drawimg(fcanvas, {top: s.y, left: s.x, page: s.page_sign, width: s.width_size, height: s.height_size, url: image, isView: true}, "signature");
            // })
            texts.forEach(t => {
                let type = "text";

                if (t.Type == 3) {
                    type = 'checkbox';
                } else if (t.Type == 4) {
                    type = 'radio';
                } else if (t.Type == 2) {
                    type = 'signature';
                }
                if (type != 'signature') {
                    signature.drawimg(fcanvas, {
                        id: t.id,
                        top: t.YAxis,
                        left: t.XAxis,
                        page: t.Page,
                        width: t.Width,
                        height: t.Height,
                        matruong: t.matruong,
                        description: t.description,
                        size: t.size,
                        FontFamily: t.FontFamily,
                        isView: true,
                        noidung: t.content
                    }, type);
                } else {
                    signature.drawimg(fcanvas, {id: t.id, top: t.YAxis, left: t.XAxis, page: t.Page, width: t.Width, height: t.Height, url: t.image_signature, isView: true}, "signature");
                }
            })
            fcanvas.on({
                'object:modified': this.signatureEventHandler,
                'object:moving': this.positionSizedHandler,
                'object:scaling': this.positionSizedHandler,
                "mouse:up": this.mouseUpHandler
            });
            signature.isView = false;
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
        if (p.isView) {
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

