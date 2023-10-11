
(function () {
    'use strict';
    angular.module("app", ['DocumentSampleService']).controller("DetailDocumentSampleCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$stateParams', '$window', '$timeout', '$filter', 'QuanLyTaiLieuMau', DetailDocumentSampleCtrl]);

    function DetailDocumentSampleCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $stateParams, $window, $timeout, $filter, QuanLyTaiLieuMau) {
        var ctrl = this;

        ctrl.getSampleDocumentFile = function (docId) {
            if(!$scope.scale){
                $scope.scale = "" + 1.5
            }
            var formData = new FormData();
            formData.append("id", docId);
            QuanLyTaiLieuMau.getSampleDocument(formData).then(function (response) {
                ctrl.docFile = response;
                signature.init(response, $scope.scale, true, [], "", ctrl.texts);
                signDoc.data.document = response;
            })
        }

        ctrl.getSampleDocument = function(docId) {
            QuanLyTaiLieuMau.getDetail({id: docId}).then(function(response) {
                if (response.data.success) {
                    ctrl.sampleDocument = response.data.data.sample;
                    ctrl.texts = [];
                    ctrl.forms = [];
                    response.data.data.lstTexts.forEach(text => {
                        if (text.data_type == 1) {
                            var text = new TextSample(
                                text.id,
                                text.content,
                                text.description,
                                text.page_sign,
                                1,
                                text.font_size + "",
                                text.font_style,
                                '',
                                text.width_size,
                                text.height_size,
                                text.x,
                                text.y,
                                text.page_width,
                                text.page_height,
                                text.is_required == 1,
                                text.is_editable == 1,
                                text.form_name,
                                text.field_code == 'its_doc_code',
                                text.field_code == 'its_doc_time',
                                text.field_code);
                            ctrl.texts.push(text);
                            signature.textCount++;
                        } else if (text.data_type == 2) {
                            let signing_method = [];
                            let tmp = text.sign_method == null ? [] : text.sign_method.split(",");
                            tmp.forEach(method => {
                                if (method != "") {
                                    signing_method.push(method);
                                }
                            });
                            let sign = new SignatureSample(text.id,
                                text.description,
                                Number.parseInt(text.page_sign),
                                2,
                                Number.parseFloat(text.width_size),
                                Number.parseFloat(text.height_size),
                                Number.parseFloat(text.x),
                                Number.parseFloat(text.y),
                                text.page_width,
                                text.page_height,
                                text.is_auto_sign == 1,
                                text.full_name,
                                text.email,
                                text.phone,
                                text.national_id,
                                text.image_signature ?? 'vcontract/assets/images/signature-icon.svg',
                                text.order_assignee,
                                text.is_my_organisation == 1,
                                text.is_editable == 1,
                                signing_method,
                                text.noti_type ? text.noti_type + "" : "1");
                            ctrl.texts.push(sign);
                        } else if (text.data_type == 3 || text.data_type == 4) {
                            if (text.form_name) {
                                let form = {
                                    form_name: text.form_name,
                                    form_description: text.form_description,
                                    is_required: text.is_required,
                                    is_editable: text.is_editable
                                }
                                let isExisted = false;
                                for (let i = 0; i < ctrl.forms.length; i++) {
                                    if (form.form_name == ctrl.forms[i].form_name) {
                                        isExisted = true;
                                        break;
                                    }
                                }
                                if (!isExisted) {
                                    ctrl.forms.push(form);
                                }
                            }
                            var checkbox = new CheckboxSample(
                                text.id,
                                text.content,
                                text.description,
                                text.page_sign,
                                text.data_type,
                                text.width_size,
                                text.height_size,
                                text.x,
                                text.y,
                                text.page_width,
                                text.page_height,
                                text.is_required == 1,
                                text.is_editable == 1,
                                text.form_name,
                                text.form_description,
                                text.field_code);
                            ctrl.texts.push(checkbox);
                            signature.textCount++;
                        }
                    })
                    ctrl.getSampleDocumentFile(docId);
                }
            });
        }
        ctrl.onCancelDetailDocumentSample = function (){
        $state.go("index.utilities.document_sample");
        }

        ctrl.init = function () {
            let id = $stateParams.id;
            ctrl.getSampleDocument(id);
        }();

        ctrl.onChangeFontSize = function() {
            let val = $scope.currentSignature.size;
            if (val && signature.activeSignature && signature.activeSignature.type === "text") {
                signature.activeSignature.fontSize = val * 1.5;
                signature.currentCanvas.requestRenderAll();
            }
        }

        ctrl.onChangeFontStyle = function() {
            let val = $scope.currentSignature.FontFamily;
            if (val && signature.activeSignature && signature.activeSignature.type === "text") {
                signature.activeSignature.fontFamily = val;
                signature.currentCanvas.requestRenderAll();
            }
        }

        ctrl.checkFormName = function() {
            for (let i = 0; i < ctrl.forms.length; i++) {
                let form = ctrl.forms[i];
                if (form.form_name == $scope.currentSignature.form_name) {
                    $scope.currentSignature.form_description = form.form_description;
                    $scope.currentSignature.is_editable = form.is_editable;
                    $scope.currentSignature.is_required = form.is_required;
                    break;
                }
            }
        }

        ctrl.changeCheckboxForm = function() {
            for (let i = 0; i < ctrl.forms.length; i++) {
                let form = ctrl.forms[i];
                if (form.form_name == $scope.currentSignature.form_name) {
                    form.is_editable = $scope.currentSignature.is_editable;
                    form.is_required = $scope.currentSignature.is_required ;
                    break;
                }
            }

            for (let i = 0; i < ctrl.texts.length; i++) {
                let text = ctrl.texts[i];
                if (text.form_name == $scope.currentSignature.form_name) {
                    text.is_editable = $scope.currentSignature.is_editable;
                    text.is_required = $scope.currentSignature.is_required;
                }
            }
        }

        ctrl.changeIsDocumentCode = function() {
            if ($scope.currentSignature.is_code) {
                $scope.currentSignature.field_code = "its_doc_code";
                $scope.currentSignature.is_editable = false;
                $scope.currentSignature.is_required = false;
                $scope.currentSignature.description = "Mã hợp đồng";
            } else {
                $scope.currentSignature.field_code = "";
                $scope.currentSignature.description = "";
            }
        }

        ctrl.changeIsTime = function() {
            if ($scope.currentSignature.is_time){
                $scope.currentSignature.field_code = "its_doc_time";
                $scope.currentSignature.is_editable = false;
                $scope.currentSignature.is_required = false;
                $scope.currentSignature.description = "Ngày {DD} tháng {MM} năm {YYYY}";
            } else {
                $scope.currentSignature.field_code = "";
                $scope.currentSignature.description = "";
            }
        }

        ctrl.saveFormName = function() {
            let form = {
                form_name: $scope.currentSignature.form_name,
                form_description: $scope.currentSignature.form_description,
                is_editable: $scope.currentSignature.is_editable,
                is_required: $scope.currentSignature.is_required,

            }
            for (let i = 0; i < ctrl.texts.length; i++) {
                let text = ctrl.texts[i];
                if (text.form_name == form.form_name) {
                    text.form_description = form.form_description;
                    text.is_editable = form.is_editable;
                    text.is_required = form.is_required;
                }
            }
            ctrl.forms.push(form);
        }

        ctrl.tagSelectConfig = {
            create: false,
            valueField: 'id',
            labelField: 'description',
            placeholder: $filter('translate')('DOCUMENT.SIGN_METHOD'),
        };

        ctrl.lstSignMethod = [
            { 'id': '0', 'description': $filter('translate')('DOCUMENT.SIGN_USB_TOKEN') },
            { 'id': '1', 'description': $filter('translate')('DOCUMENT.SIGN_OTP') },
            { 'id': '2', 'description': $filter('translate')('DOCUMENT.SIGN_EKYC') },
            { 'id': '3', 'description': $filter('translate')('DOCUMENT.REMOTE_SIGNING.DEFAULT') },
            { 'id': '5', 'description':  $filter('translate')('DOCUMENT.SIGN_HSM.DEFAULT') },
        ];

        ctrl.formatDate = [
            { 'id': '0', 'description': $filter('translate')('Ngày {DD} tháng {MM} năm {YYYY}') },
            { 'id': '1', 'description': $filter('translate')('Ngày {DD}') },
            { 'id': '2', 'description': $filter('translate')('Tháng {MM}') },
            { 'id': '3', 'description': $filter('translate')('Năm {YYYY}') },
            { 'id': '4', 'description': $filter('translate')('Tháng {MM} năm {YYYY}') },
            { 'id': '5', 'description': $filter('translate')('{DD} / {MM} / {YYYY}') },
            { 'id': '6', 'description': $filter('translate')('{DD} / {MM} / {YY}') },
            { 'id': '7', 'description': $filter('translate')('{MM} / {YY}') },
            { 'id': '8', 'description': $filter('translate')('{MM} / {YYYY}') },

        ]

        $scope.defaultSignature = Utils.defaultUploadImage();

        ctrl.createSignature = function(){
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addSampleSignature.html',
                windowClass: "fade show modal-blur",
                size: 'md modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        $scope.onDrawingFiles = function(canvas, p, type) {
            signature.currentCanvas = canvas;
            var page = parseInt(canvas.lowerCanvasEl.dataset.pageNo);
            var imgElement = document.querySelector(".signature");
            var originalRender = fabric.Textbox.prototype._render;
            fabric.Textbox.prototype._render = function(ctx) {
            originalRender.call(this, ctx);
            if(this.showTextBoxBorder ){
                var w = this.width,
                h = this.height,
                x = -this.width / 2,
                y = -this.height / 2;
                ctx.beginPath();
                ctx.moveTo(x, y);
                ctx.lineTo(x + w, y);
                ctx.lineTo(x + w, y + h);
                ctx.lineTo(x, y + h);
                ctx.lineTo(x, y);
                ctx.closePath();
                var stroke = ctx.strokeStyle;
                ctx.strokeStyle = this.textboxBorderColor;
                ctx.stroke();
                ctx.strokeStyle = stroke;
            }
            }
            if (p.top < 0)
                p.top = 0;
            if (p.left < 0)
                p.left = 0;
            var id = Utils.uuidv4();
            var matruong = `text-${signature.textCount + 1}`;
            var fontFamily = "Times New Roman";
            var description = "";
            if (type === "text") {
                var textBox = new fabric.Textbox('', {
                    id: id,
                    matruong: matruong,
                    description: description,
                    page: page,
                    top: p.top,
                    left: p.left,
                    width: 212,
                    fontSize: 12 * 1.5,
                    fontFamily: fontFamily,
                    backgroundColor: "rgba(238, 243, 250,0.7)",
                    type: "text",
                    hoverCursor: "text",
                    textboxBorderColor: 'rgb(29, 96, 176)',
                    showTextBoxBorder: true,
                    splitByGrapheme: true,
                    centeredRotation: true
                });
                textBox.setControlsVisibility({
                    br: false
                });
                canvas.add(textBox);
                canvas.setActiveObject(textBox);
                signature.activeSignature = textBox;
                if (signature.onObjectChange) {
                    signature.onObjectChange(textBox);
                }
                var text = new TextSample(
                    id,
                    "",
                    description,
                    page,
                    1,
                    "12",
                    fontFamily,
                    '',
                    212,
                    imgElement.offsetHeight,
                    p.left,
                    p.top,
                    canvas.width,
                    canvas.height,
                    false,
                    true,
                    "",
                    false,
                    false,
                    "");
                if (!ctrl.texts) {
                    ctrl.texts = [];
                }
                ctrl.texts.push(text);
                signature.textCount++;
            } else if (type === "signature") {
                let imgInstance = new fabric.Image(imgElement, {
                    page: page,
                    sigW: imgElement.width,
                    sigH: imgElement.height,
                    scaleX: 1.5545081967213115,
                    scaleY: 1.5545081967213115,
                    top: p.top,
                    left: p.left,
                    angle: 0,
                    opacity: 1,
                    id: id,
                    sign_type: 1,
                    type: "signature"
                });
                /*imgInstance.setControlsVisibility({ mbr: true });*/
                canvas.add(imgInstance);

                let sign = new SignatureSample(id,
                    "",
                    page,
                    2,
                    imgInstance.scaleX * imgInstance.width,
                    imgInstance.scaleY * imgInstance.height,
                    p.left,
                    p.top,
                    canvas.width,
                    canvas.height,
                    false,
                    "",
                    "",
                    "",
                    "",
                    "",
                    1,
                    false,
                    true,
                    [],
                    "1");
                if (!ctrl.texts) {
                    ctrl.texts = [];
                }
                ctrl.texts.push(sign);
                canvas.setActiveObject(imgInstance);
                signature.activeSignature = imgInstance;
                if (signature.onObjectChange) {
                    signature.onObjectChange(imgInstance);
                }
                signature.textCount++;
            } else if (type === "checkbox") {
                var rect = new fabric.Rect({
                    id: id,
                    matruong: matruong,
                    description: description,
                    page: page,
                    top: p.top,
                    left: p.left,
                    width: 24,
                    height: 24,
                    strokeWidth: 1,
                    stroke: "rgb(29, 96, 176)",
                    fill: "rgba(238, 243, 250,0.7)",
                    type:"checkbox"
                });
                canvas.add(rect);
                canvas.setActiveObject(rect);
                signature.activeSignature = rect;
                if (signature.onObjectChange) {
                    signature.onObjectChange(rect);
                }
                var checkbox = new CheckboxSample(
                    id,
                    false,
                    description,
                    page,
                    3,
                    24,
                    24,
                    p.left,
                    p.top,
                    canvas.width,
                    canvas.height,
                    false,
                    true,
                    "",
                    "");
                if (!ctrl.texts) {
                    ctrl.texts = [];
                }
                ctrl.texts.push(checkbox);
                signature.textCount++;
            } else if (type === "radio") {
                var rect = new fabric.Rect({
                    id: id,
                    matruong: matruong,
                    description: description,
                    page: page,
                    top: p.top,
                    left: p.left,
                    width: 24,
                    height: 24,
                    strokeWidth: 1,
                    stroke: "rgb(29, 96, 176)",
                    fill: "rgba(238, 243, 250,0.7)",
                    type:"radio"
                });
                canvas.add(rect);
                canvas.setActiveObject(rect);
                signature.activeSignature = rect;
                if (signature.onObjectChange) {
                    signature.onObjectChange(rect);
                }
                var checkbox = new CheckboxSample(
                    id,
                    false,
                    description,
                    page,
                    4,
                    24,
                    24,
                    p.left,
                    p.top,
                    canvas.width,
                    canvas.height,
                    false,
                    true,
                    "",
                    "");
                if (!ctrl.texts) {
                    ctrl.texts = [];
                }
                ctrl.texts.push(checkbox);
                signature.textCount++;
            }

        }

        $scope.onDrawingViewFiles = function (canvas, p, type) {
            signature.currentCanvas = canvas;
            let pageNo = canvas.lowerCanvasEl.dataset.pageNo;
            let id = p.id;
            let matruong = p.matruong;
            var originalRender = fabric.Textbox.prototype._render;
            fabric.Textbox.prototype._render = function(ctx) {
            originalRender.call(this, ctx);
            if(this.showTextBoxBorder ){
                var w = this.width,
                h = this.height,
                x = -this.width / 2,
                y = -this.height / 2;
                ctx.beginPath();
                ctx.moveTo(x, y);
                ctx.lineTo(x + w, y);
                ctx.lineTo(x + w, y + h);
                ctx.lineTo(x, y + h);
                ctx.lineTo(x, y);
                ctx.closePath();
                var stroke = ctx.strokeStyle;
                ctx.strokeStyle = this.textboxBorderColor;
                ctx.stroke();
                ctx.strokeStyle = stroke;
            }
            }
            if (type == "text") {
                if (pageNo == p.page) {
                    var textBox = new fabric.Textbox(p.noidung ?? "", {
                        id: id,
                        matruong: p.matruong,
                        description: p.description,
                        page: Number.parseInt(p.page),
                        top: Number.parseFloat(p.top),
                        left: Number.parseFloat(p.left),
                        width: Number.parseFloat(p.width),
                        fontSize: p.size * 1.5,
                        fontFamily: p.FontFamily,
                        backgroundColor: "rgba(238, 243, 250,0.7)",
                        type: "text",
                        hoverCursor: "text",
                        textboxBorderColor: 'rgb(29, 96, 176)',
                        textboxBorder : 2,
                        showTextBoxBorder: true,
                        splitByGrapheme: true,
                        centeredRotation: true
                    });
                    textBox.setControlsVisibility({
                        br: false
                    });
                    canvas.add(textBox);
                }
            } else if (type === "signature") {
                if (pageNo == p.page) {
                    let imgInstance = new fabric.Image.fromURL(p.url, function (img) {
                        let scaleX = Number.parseFloat(p.width) / img.width;
                        let scaleY = Number.parseFloat(p.height) / img.height;
                        img.set({
                            page: Number.parseInt(p.page),
                            sigW: img.width,
                            sigH: img.height,
                            scaleX: scaleX,
                            scaleY: scaleY,
                            top: Number.parseFloat(p.top),
                            left: Number.parseFloat(p.left),
                            angle: 0,
                            opacity: 1,
                            id: id,
                            signature: signature.textCount,
                            sign_type: 1,
                            type: "signature"
                        })
                        canvas.add(img);
                    });
                }
            } else if (type === "checkbox") {
                if (pageNo == p.page) {
                    var rect = new fabric.Rect({
                        id: id,
                        matruong: p.matruong,
                        description: p.description,
                        page: Number.parseInt(p.page),
                        top: Number.parseFloat(p.top),
                        left: Number.parseFloat(p.left),
                        width: Number.parseFloat(p.width),
                        height: Number.parseFloat(p.height),
                        strokeWidth: 1,
                        stroke: "rgb(29, 96, 176)",
                        type: "checkbox",
                        fill: "rgba(238, 243, 250,0.7)",
                    });
                    canvas.add(rect);
                }
            } else if (type === "radio") {
                if (pageNo == p.page) {
                    var rect = new fabric.Rect({
                        id: id,
                        matruong: p.matruong,
                        description: p.description,
                        page: Number.parseInt(p.page),
                        top: Number.parseFloat(p.top),
                        left: Number.parseFloat(p.left),
                        width: Number.parseFloat(p.width),
                        height: Number.parseFloat(p.height),
                        strokeWidth: 1,
                        stroke: "rgb(29, 96, 176)",
                        type: "radio",
                        fill: "rgba(238, 243, 250,0.7)",
                    });
                    canvas.add(rect);
                }
            }
        }

        signature.onObjectChange = (target) => {
            if (!target) {
                $scope.currentSignature = null;
                $scope.$digest();
                return;
            }
            if (target && target.type === "text") {
                target.noidung = target.text;
                var object = ctrl.texts.find(x => x.id === target.id);
                if (object) {
                    $scope.currentSignature = object;
                    object.noidung = target.text;
                    object.isvalid = target.isvalid;
                    if (object.size) {
                        object.XAxis = target.left;
                        object.YAxis = target.top;
                        object.Width = target.width;
                        object.Height = target.height;
                    }
                }
            } else if (target.type === "signature") {
                var object = ctrl.texts.find(x => x.id === target.id);
                if (object) {
                    $scope.currentSignature = object;
                    object.XAxis = target.left;
                    object.YAxis = target.top;
                    object.Width = target.scaleX !== null ? target.width * target.scaleX : target.width;
                    object.Height = target.scaleY !== null ? target.height * target.scaleY : target.height;
                }
            } else if (target.type === "checkbox") {
                var object = ctrl.texts.find(x => x.id === target.id);
                if (object) {
                    $scope.currentSignature = object;
                    object.XAxis = target.left;
                    object.YAxis = target.top;
                    object.Width = target.scaleX !== null ? target.width * target.scaleX : target.width;
                    object.Height = target.scaleY !== null ? target.height * target.scaleY : target.height;
                }
            } else if (target.type === "radio") {
                var object = ctrl.texts.find(x => x.id === target.id);
                if (object) {
                    $scope.currentSignature = object;
                    object.XAxis = target.left;
                    object.YAxis = target.top;
                    object.Width = target.scaleX !== null ? target.width * target.scaleX : target.width;
                    object.Height = target.scaleY !== null ? target.height * target.scaleY : target.height;
                }
            }

            $scope.$digest();
        }

        $scope.onDeleteObject = function(eventData, tg) {
            var target = tg.target;
            var canvas = target.canvas;
            canvas.remove(target);
            canvas.requestRenderAll();
            if (target.type === "text") {
                ctrl.texts = ctrl.texts.filter(it => it.id !== target.id);
            } else if (target.type === "signature") {
                ctrl.texts = ctrl.texts.filter(it => it.id !== target.id);
            } else if (target.type === "checkbox" || target.type === "radio") {
                ctrl.texts = ctrl.texts.filter(it => it.id !== target.id);
            }
        }

        ctrl.onSaveSampleDocument = function() {
            let errorMess = "", errText = 0, errCheckbox = 0, errRadio = 0 ;
            let type = [];
            ctrl.texts.forEach(function checkErr(text){
                type.push(text.Type);
                if (!text.description && !text.field_code && text.Type == 1) {
                    errText++;
                }
                else{
                    if (text.Type == 1) {
                        if (!text.field_code) {
                            errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_FIELD_CODE',{ 'description': text.description });

                        }
                        if (!text.description) {
                            errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_DESCRIPTION',{ 'field_code': text.field_code });

                        }
                    } else if (text.Type == 2) {
                        if(!text.description) {
                            errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMTY_SIGN_DESCRIPTION');
                        }else{
                            if (!text.order_assignee) {
                                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_INVALID_ORDER_ASSIGNEE');

                            }
                            if (text.signing_method.length == 0) {
                                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_SIGNING_METHOD', {'description': text.description});

                            } else {
                                text.sign_method = ",";
                                text.signing_method.forEach(method => {
                                    text.sign_method += method + ",";
                                })
                            }
                            if (text.is_my_organisation) {
                                if (text.full_name == "") {
                                    errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_FULL_NAME', {'description': text.description});


                                }else if(text.full_name != "" && !Utils.validateVietnameseCharacterWithNumber(text.full_name)){
                                    errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_INVALID_FULL_NAME', {'description': text.description});

                                }
                                if (text.email == ""){
                                    errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_EMAIL', {'description': text.description});


                                }else if(text.email != "" && !Utils.validateEmail(text.email)){
                                    errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_INVALID_EMAIL', {'description': text.description});

                                }
                                if (text.national_id == ""){
                                    errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_CCCD', {'description': text.description});


                                }else if(text.national_id != "" && !Utils.validateCCCD(text.national_id)){
                                    errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_INVALID_CCCD', {'description': text.description});

                                }
                                if (text.image_signature == 'vcontract/assets/images/signature-icon.svg') {
                                    errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_IMAGE_SIGNATURE ', {'description': text.description});


                                }
                            }
                        }
                    } else if(text.Type == 3 || text.Type == 4) {
                        if(!text.description){
                            if(text.Type ==3){
                                errCheckbox++;
                            }
                            if(text.Type == 4){
                                errRadio++;
                            }
                        }else{

                            if (!text.form_name) {
                                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_FORM_NAME ',{ 'description': text.description });


                            }
                            if (!text.form_description) {
                                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_FORM_DESCRIPTION ',{ 'description': text.description });


                            }
                            if (!text.field_code) {
                                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_FIELD_CODE ',{ 'description': text.description });


                        }
                        }
                    }
                }
            })
            if(!type.includes(2)){
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_SIGNATURE');
            }
            if(errText > 0) {
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_TEXT',{ 'errText': errText });
            }
            if(errCheckbox > 0) {
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_CHECKBOX',{ 'errCheckbox': errCheckbox });
            }
            if(errRadio > 0) {
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_RADIO',{ 'errRadio': errRadio });
            }
            if(errorMess == ""){
                let data = {
                    document_sample_id: $stateParams.id,
                    texts: ctrl.texts,
                    isLoading: true
                }

                QuanLyTaiLieuMau.saveDetailSampleDocument(data).then(function (response) {
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    $state.go("index.utilities.document_sample");
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }else{
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, QuanLyTaiLieuMau) {
        $scope.updateSampleSignatureManual = function(data){
            if(data.isEmpty()){
                NotificationService.error($filter('translate')("CONFIG.ACCOUNT.ERR_DRAW_SIGNATURE"));
            } else {
                $scope.currentSignature.image_signature = data.toDataURL();
            }
            $uibModalInstance.close(false);
        }

        $scope.updateSampleSignatureUpload = function(data){
            let file = angular.element(document.querySelector('input[type=file]')['files'][0]);
            if(file && file[0]){
                var reader = new window.FileReader();
                reader.onload = function(){
                    var b64 = reader.result;
                    if($scope.currentSignature.image_signature != b64){
                        $scope.currentSignature.image_signature = b64;
                    }
                    $uibModalInstance.close(false);
                }
                reader.readAsDataURL(file[0]);
            } else {
                $uibModalInstance.close(false);
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }


})();
