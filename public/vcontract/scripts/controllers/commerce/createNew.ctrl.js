
(function () {
    'use strict';
    angular.module("app", ['CommerceSrvc', 'UtilitiesSrvc', 'ConfigService']).controller("CreateCommerceDocumentCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$stateParams', '$window', '$timeout', '$filter', 'DTOptionsBuilder', 'DTColumnBuilder', 'XuLyTaiLieuThuongMai', 'NhanVien', 'KhachHangDoiTac', CreateCommerceDocumentCtrl]);
    function CreateCommerceDocumentCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $stateParams, $window, $timeout, $filter, DTOptionsBuilder, DTColumnBuilder, XuLyTaiLieuThuongMai, NhanVien, KhachHangDoiTac) {

        var ctrl = this;

        ctrl.initNewPartner = function (order) {
            return {
                'order_assignee': order + 1,
                'organisation_type': "2",
                'company_name': "",
                'tax': "",
                'assignees': [
                    ctrl.initNewAssignee()
                ],
            }
        }

        ctrl.isAutoOrder = false;
        ctrl.selectedSigningAssignee = "-1";
        ctrl.texts = [];

        ctrl.initNewAssignee = function() {
            return {
                'full_name': "",
                "email": "",
                "phone": "",
                "national_id":"",
                "assign_type": "-1",
                "is_auto_sign": false,
                "signing_method": [],
                "message": "",
                "noti_type": "-1",
                "addNewOptions": false
            }
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
        ]
        ctrl.lstAutoSign =[
            { 'id': '5', 'description':  $filter('translate')('DOCUMENT.SIGN_HSM.DEFAULT')}
        ]
        ctrl.lstAddendumType = [
            { 'id': '0', 'description': $filter('translate')('DOCUMENT.ADDENDUM_TYPE.ADD') },
            { 'id': '1', 'description': $filter('translate')('DOCUMENT.ADDENDUM_TYPE.EXTEND') },
            { 'id': '2', 'description': $filter('translate')('DOCUMENT.ADDENDUM_TYPE.DENY') },
        ]


        ctrl.addPartner = function() {
            ctrl.newDoc.partners.push(ctrl.initNewPartner(ctrl.newDoc.partners.length));
        }

        ctrl.deletePartner = function(index) {
            ctrl.newDoc.partners.splice(index, 1);
        }

        ctrl.addAssignee = function(partner) {
            partner.assignees.push(ctrl.initNewAssignee());
        }

        ctrl.deleteAssignee = function(partner, index) {
            partner.assignees.splice(index, 1);
        }

        ctrl.exitCompose = function() {
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.EXIT_CREATE_CONFIRM'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                if($scope.isAddendum){
                    $state.go("index.commerce.viewDocument", {docId: $stateParams.parentId});
                } else {
                    $state.go("index.commerce.documentList");
                }
            }).catch(function (err) {
            });
        }

        ctrl.callInit = function (id) {
            XuLyTaiLieuThuongMai.init({ docId: id, isLoading: true }).then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.lstDocumentType = response.data.data.documentType;
                ctrl.lstDocumentSample = response.data.data.documentSample;
                ctrl.company = response.data.data.company;
                ctrl.config = response.data.data.config;
                ctrl.isAddendum = response.data.data.isAddendum;
                let document_expire_day = ctrl.config == null ? 60 : ctrl.config.document_expire_day;
                if($scope.fatherAssignees){
                    var order_assignee = 1;
                    var organisation = [];
                    var ur = [];
                    $scope.partners = [];
                    $scope.fatherAssignees.forEach (fatherAssignee => {
                        if(!ur.includes(fatherAssignee.organisation_type)){
                            let or = [];
                            ur.push(fatherAssignee.organisation_type);
                            or.organisation_type = fatherAssignee.organisation_type;
                            or.company_name = fatherAssignee.company_name;
                            or.tax = fatherAssignee.tax_number;
                            organisation.push(or);
                        }
                    });
                    ur.sort();
                    if(!ur.includes(1)){
                        let partner = new Object();
                        partner.order_assignee = order_assignee++;
                        partner.organisation_type ="" + 1;
                        partner.company_name =  ctrl.company.name;
                        partner.tax = ctrl.company.tax_number;
                        partner.assignees = [
                            ctrl.initNewAssignee()
                        ];
                        $scope.partners.push(partner);
                    }
                    organisation.forEach(ot => {
                        let partner = new Object();
                        partner.order_assignee = order_assignee++;
                        partner.organisation_type ="" + ot.organisation_type;
                        partner.company_name = ot.company_name;
                        partner.tax = ot.tax;
                        partner.assignees = [];
                        $scope.fatherAssignees.forEach (fatherAssignee => {
                            if(fatherAssignee.organisation_type == ot.organisation_type){
                                let assignee = ctrl.initNewAssignee();
                                assignee.full_name = fatherAssignee.name;
                                assignee.email = fatherAssignee.email;
                                assignee.phone = fatherAssignee.phone;
                                assignee.national_id = fatherAssignee.national_id;
                                assignee.assign_type = "" + fatherAssignee.assign_type;
                                partner.assignees.push(assignee);
                            }
                        });
                        $scope.partners.push(partner);
                    });
                } else {
                    $scope.partners = [
                        {
                            'order_assignee': 1,
                            'organisation_type': 1,
                            'company_name': ctrl.company.name,
                            'tax': ctrl.company.tax_number,
                            'assignees': [
                                ctrl.initNewAssignee()
                            ]
                        }
                    ];
                }
                if (!response.data.data.document) {
                    let today = new Date();
                    ctrl.currentStep = 1;
                    ctrl.newDoc = {
                        document_type_id: $scope.document_type_id,
                        is_use_template: false,
                        document_sample_id: "-1",
                        expired_type:"-1",
                        doc_expired_date: null,
                        expired_month:"",
                        code: "",
                        name: "",
                        sent_date: Utils.formatDateNoTime(today),
                        expired_date: Utils.formatDateNoTime(new Date(today.setDate(today.getDate() + document_expire_day))),
                        addendum_type:"-1",
                        isAddendum: $scope.isAddendum,
                        parent_id : $scope.parentId,
                        is_verify_content: false,
                        is_order_approval: false,
                        is_encrypt : false,
                        encrypt_password : "",
                        partners: $scope.partners,
                    }
                    ctrl.selectedFile = [];
                } else {
                    if(response.data.data.document.doc_expired_date == null){
                        var doc_expired_date = null;
                    }
                    else{
                        var doc_expired_date = Utils.formatDateNoTime(new Date(response.data.data.document.doc_expired_date));
                    }
                    ctrl.newDoc = {
                        id: response.data.data.document.id,
                        document_type_id: response.data.data.document.document_type_id + "",
                        is_use_template: response.data.data.document.document_sample_id ? true : false,
                        document_sample_id: response.data.data.document.document_sample_id + "" ?? "-1",
                        code: response.data.data.document.code,
                        name: response.data.data.document.name,
                        expired_type: "" + response.data.data.document.expired_type,
                        doc_expired_date:doc_expired_date,
                        expired_month:response.data.data.document.expired_month,
                        sent_date: Utils.formatDateNoTime(new Date(response.data.data.document.sent_date)),
                        expired_date: Utils.formatDateNoTime(new Date(response.data.data.document.expired_date)),
                        addendum_type: "" + response.data.data.document.addendum_type,
                        isAddendum: $scope.isAddendum,
                        parent_id : $scope.parentId,
                        is_verify_content: response.data.data.document.is_verify_content == 1,
                        is_order_approval: response.data.data.document.is_order_approval == 1,
                        is_encrypt : response.data.data.document.is_encrypt == 1,
                        encrypt_password: response.data.data.document.save_password ,
                    }
                    if (ctrl.newDoc.is_use_template) {
                        ctrl.newDoc.samplePartners = response.data.data.document.partners;
                        ctrl.newDoc.infos = response.data.data.document.infos;
                        ctrl.newDoc.samplePartners.forEach(partner => {
                            partner.organisation_type += "";
                            partner.assignees.forEach(assignee => {
                                assignee.assign_type += "";
                                assignee.noti_type += "";
                                if (assignee.assign_type == 2) {
                                    assignee.signing_method = [];
                                    let tmp = assignee.sign_method == null ? [] : assignee.sign_method.split(",");
                                    tmp.forEach(method => {
                                        if (method != "") {
                                            assignee.signing_method.push(method);
                                        }
                                    });
                                }
                            })
                        })
                        XuLyTaiLieuThuongMai.getDetailDocumentSampleById({ id: ctrl.newDoc.document_sample_id }).then(function (response) {
                            ctrl.sampleSignatures = response.data.data.signatures;
                            ctrl.documentSample = response.data.data.documentSample;
                            ctrl.sampleInfos = response.data.data.infos;
                        });
                        ctrl.currentStep = 1;
                    } else {
                        if (response.data.data.document.partners.length == 0) {
                            ctrl.newDoc.partners = [
                                {
                                    'order_assignee': 1,
                                    'organisation_type': 1,
                                    'company_name': ctrl.company.name,
                                    'tax': ctrl.company.tax_number,
                                    'assignees': [
                                        ctrl.initNewAssignee()
                                    ]
                                }
                            ]
                        } else {
                            ctrl.lstSigningAssignee = []
                            ctrl.newDoc.partners = response.data.data.document.partners;
                            ctrl.newDoc.partners.forEach(partner => {
                                let group = {
                                    id: partner.id,
                                    name: `[${partner.order_assignee}] ${partner.company_name ?? ""}`,
                                    assignee: []
                                }
                                partner.organisation_type += "";
                                partner.assignees.forEach(assignee => {
                                    assignee.assign_type += "";
                                    assignee.noti_type += "";
                                    if (assignee.assign_type == 2) {
                                        assignee.signing_method = [];
                                        let tmp = assignee.sign_method == null ? [] : assignee.sign_method.split(",");
                                        tmp.forEach(method => {
                                            if (method != "") {
                                                assignee.signing_method.push(method);
                                            }
                                        });
                                        group.assignee.push({
                                            assignee_id: assignee.id,
                                            assignee_name: `${assignee.full_name} - ${assignee.email}`,
                                            signatures: [],
                                            partner_id: partner.id
                                        });
                                    }
                                })
                                ctrl.lstSigningAssignee.push(group);
                            })

                        }
                        ctrl.selectedFile = [];
                        response.data.data.document.files.forEach(file => {
                            let f = {
                                name: file.file_name_raw,
                                size: Utils.bytesToSize(file.file_size_raw),
                                file_id: file.file_id
                            }
                            ctrl.selectedFile.push(f);
                        });

                        ctrl.currentStep = response.data.data.document.document_draft_state;
                        if (ctrl.currentStep == 3) {
                            ctrl.getSignDocument(id);
                        }
                    }
                }
            }, function (response) {
                $scope.initBadRequest(response);
                if($scope.isAddendum){
                    $state.go("index.commerce.viewDocument", {docId: $stateParams.parentId});
                } else {
                    $state.go("index.commerce.documentList");
                }
            })
        }

        ctrl.init = function () {
            $scope.isExistedDocumentCode = false;
            $scope.loginUser = $rootScope.loginUser;
            $scope.initData = $rootScope.initData;
            let id = $stateParams.docId;
            if($stateParams.parentId){
                $scope.isAddendum = true;
                XuLyTaiLieuThuongMai.getFatherDoc({ parent_id: $stateParams.parentId, isLoading: true }).then(function (response) {
                    $scope.parentId = parseInt($stateParams.parentId);
                    $scope.fatherName = response.data.data.fatherDocument.name;
                    $scope.document_type_id = response.data.data.fatherDocument.document_type_id;
                    if(response.data.data.fatherDocument.expired_type != 0){
                        ctrl.lstExpiredType = [
                            { 'id': '0', 'description': $filter('translate')('DOCUMENT.UNLIMITED') },
                            { 'id': '1', 'description': $filter('translate')('DOCUMENT.TO_EXPIRED_DAY') },
                            { 'id': '2', 'description': $filter('translate')('DOCUMENT.TO_EXPIRED_MONTH') },
                        ]
                    } else {
                        ctrl.lstExpiredType = [
                            { 'id': '1', 'description': $filter('translate')('DOCUMENT.TO_EXPIRED_DAY') },
                        ]
                    }
                    $scope.fatherAssignees = response.data.data.fatherAssignee;
                    ctrl.callInit(id);
                },function (response){
                    $scope.initBadRequest(response);
                })
            }else{
                ctrl.lstExpiredType = [
                    { 'id': '0', 'description': $filter('translate')('DOCUMENT.UNLIMITED') },
                    { 'id': '1', 'description': $filter('translate')('DOCUMENT.TO_EXPIRED_DAY') },
                    { 'id': '2', 'description': $filter('translate')('DOCUMENT.TO_EXPIRED_MONTH') },
                ]
                $scope.isAddendum = false;
                $scope.parentId = -1;
                $scope.document_type_id = "-1"
                ctrl.callInit(id);
            }

            NhanVien.init().then(function (response) {
                $scope.lstDepartment = response.data.data.lstDepartment;
                $scope.lstPosition = response.data.data.lstPosition;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.prevStep = function () {
            ctrl.currentStep--;
        }

        ctrl.nextStep = function () {
            if (ctrl.currentStep == 1) {
                ctrl.goStep2();
            } else if (ctrl.currentStep == 2) {
                ctrl.goStep3();
            } else if (ctrl.currentStep == 3) {
                ctrl.finishDrafting();
            }
        }

        ctrl.splitCode = function(code) {
            let splitCode = code.split('-');
            splitCode.forEach( function(v,k) {
                if(k == 0){
                    ctrl.newDoc.codeNum = v;
                } else if(k == 1){
                    ctrl.newDoc.codeText = v ;
                } else {
                    ctrl.newDoc.codeText += "-" + v ;
                }
            });
        }

        ctrl.onDocumentTypeChange = function () {
            if (ctrl.newDoc.document_type_id == "-1") {
                ctrl.newDoc.code = "";
                ctrl.newDoc.codeNum = "";
                ctrl.newDoc.codeText = "";
            } else {
                XuLyTaiLieuThuongMai.getCodeById({ id: ctrl.newDoc.document_type_id, isLoading: true }).then(function (response) {
                    ctrl.newDoc.code = response.data.data;
                    if(ctrl.newDoc.code){
                        ctrl.splitCode(ctrl.newDoc.code);
                    }
                    if(ctrl.newDoc.code){
                        ctrl.isAutoOrder = true;
                    }else{
                        ctrl.isAutoOrder = false;
                    }
                },function(response){
                    $scope.initBadRequest(response);
                });
            }
        }

        ctrl.onDocumentSampleChange = function() {
            if(ctrl.newDoc.name){
                ctrl.newDoc.name = "";
            }
            if (ctrl.newDoc.document_sample_id == "-1") {
                ctrl.newDoc.document_type_id = "-1";
                ctrl.newDoc.code = "";
                ctrl.newDoc.codeNum = "";
                ctrl.newDoc.codeText = "";
            } else {
                XuLyTaiLieuThuongMai.getDetailDocumentSampleById({ id: ctrl.newDoc.document_sample_id, isLoading: true }).then(function (response) {
                    ctrl.sampleSignatures = response.data.data.signatures;
                    ctrl.documentSample = response.data.data.documentSample;
                    ctrl.sampleInfos = response.data.data.infos;
                    ctrl.newDoc.is_verify_content = ctrl.documentSample.is_verify_content == 1;
                    ctrl.isAutoOrder = response.data.data.is_order_auto.is_order_auto;
                    ctrl.newDoc.document_type_id = "" + ctrl.documentSample.document_type_id;

                    XuLyTaiLieuThuongMai.getCodeById({ id: ctrl.documentSample.document_type_id}).then(function (response) {
                        ctrl.newDoc.code = response.data.data;
                        if(ctrl.newDoc.code){
                            ctrl.splitCode(ctrl.newDoc.code);
                        }
                    },function (response){
                        $scope.initBadRequest(response);
                    });
                });
            }
        }

        ctrl.checkExistEmail = function(partner, assignee, index){
            if (!assignee.email) {
                assignee.email_err = true;
                assignee.email_err_format = false;
            } else {
                assignee.email_err = false;
                if (!Utils.validateEmail(assignee.email)) {
                    assignee.email_err_format = true;
                } else {
                    assignee.email_err_format = false;
                    if (partner.organisation_type == 1) {
                        NhanVien.checkExist({'email': assignee.email}).then(function (response) {
                            if (response.data.data.status == '404') {
                                assignee.addNewOptions = true;
                                // assignee.full_name = "";
                                // assignee.phone = "";
                                // assignee.national_id = "";
                            } else {
                                assignee.addNewOptions = false;
                                assignee.full_name = response.data.data.employee.emp_name;
                                assignee.phone = response.data.data.employee.phone;
                                assignee.national_id = response.data.data.employee.national_id;
                            }
                        }, function (response) {
                            $scope.initBadRequest(response);
                            assignee.addNewOptions = true;
                        })
                    } else {
                        KhachHangDoiTac.checkExist({'email': assignee.email}).then(function (response) {
                            if (response.data.data.status == '404') {
                                assignee.addNewOptions = true;
                                assignee.full_name = "";
                                assignee.phone = "";
                                assignee.national_id = "";
                            } else {
                                assignee.addNewOptions = false;
                                assignee.full_name = response.data.data.customer.name;
                                assignee.phone = response.data.data.customer.phone;
                                assignee.email = response.data.data.customer.email;
                            }
                        }, function (response) {
                            $scope.initBadRequest(response);
                            assignee.addNewOptions = true;
                        })
                    }
                }

            }
        }

        ctrl.validateField = function(obj, fieldName) {
            if (!obj[fieldName]) {
                obj[fieldName + "_err"] = true;
                if (fieldName == 'email') {
                    obj.email_err_format = false;
                } else if (fieldName == 'phone') {
                    obj[fieldName + "_err"] = false;
                    obj.phone_err_format = false;
                    obj.phone_err_length = false;
                } else if (fieldName == 'national_id') {
                    obj[fieldName + "_err"] = false;
                    obj.national_id_err_format = false;
                } else if (fieldName == 'full_name') {
                    obj.full_name_err_format = false;
                } else if (fieldName == 'tax') {
                    obj.tax_err_format = false;
                    obj.tax_err_length = false;
                }
            } else {
                if (fieldName == 'phone') {
                    if(obj.phone.length > 15 || obj.phone.length < 8) {
                        obj.phone_err_length = true;
                    }else {
                        obj.phone_err_length = false;
                        if (!Utils.isValidPhoneNumber(obj.phone)) {
                            obj.phone_err_format = true;
                        } else {
                            obj.phone_err_format = false;
                        }
                    }
                }
                if( fieldName == 'full_name') {
                    if (!Utils.validateVietnameseCharacterWithoutNumber(obj.full_name)) {
                        obj.full_name_err_format = true;
                    } else {
                        obj.full_name_err_format = false;
                    }
                }
                if( fieldName == 'national_id' ) {
                    if (!Utils.isValidPhoneNumber(obj.national_id)) {
                        obj.national_id_err_format = true;
                    } else {
                        obj.national_id_err_format = false;
                    }
                }
                if( fieldName == 'tax') {
                    if(obj.tax.length > 15 || obj.tax.length < 10) {
                        obj.tax_err_length = true;
                    }else {
                        obj.tax_err_length = false;
                        if (!Utils.isValidPhoneNumber(obj.tax)) {
                            obj.tax_err_format = true;
                        } else {
                            obj.tax_err_format = false;
                        }
                    }
                }
                obj[fieldName + "_err"] = false;
            }
        }

        ctrl.changeAutoSign = function (assignee) {
            if (assignee.is_auto_sign){
                assignee.signing_method = [5];
                assignee.noti_type = '1';
            }else{
                assignee.signing_method = [];
                assignee.noti_type = '-1';
            }
        }

        ctrl.validateSelectbox = function(obj, fieldName) {
            if (fieldName == "signing_method") {
                if (obj.assign_type == 2 && obj[fieldName].length == 0) {
                    obj[fieldName + "_err"] = true;
                } else {
                    obj[fieldName + "_err"] = false;
                }
            } else {
                if (obj[fieldName] == "-1") {
                    obj[fieldName + "_err"] = true;
                } else {
                    obj[fieldName + "_err"] = false;
                }
            }
        }

        ctrl.goStep2 = function () {
            let newDoc = ctrl.newDoc;
            let errorMess = "";
            if(newDoc.expired_type == 0){
                newDoc.doc_expired_date = null;
                newDoc.expired_month = '';
            } else if(newDoc.expired_type == 1 ) {
                newDoc.expired_month = '';
            } else if(newDoc.expired_type == 2){
                newDoc.doc_expired_date = null;
            }
            newDoc.sentDate = Utils.parseDate(newDoc.sent_date);
            newDoc.expiredDate = Utils.parseDateEnd(newDoc.expired_date);
            if(newDoc.doc_expired_date != null){
                newDoc.docExpiredDate = Utils.parseDateEnd(newDoc.doc_expired_date);
                if((new Date(newDoc.docExpiredDate)).getTime() < (new Date(newDoc.expiredDate)).getTime()){
                    errorMess += $filter('translate')('DOCUMENT.ERR_INVALID_EXPIRED_DOC_DATE');
                }
            }else {
                newDoc.docExpiredDate = null;
            }
            if (newDoc.is_use_template) {
                if (newDoc.document_sample_id == "-1") {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_SAMPLE');
                }
                if (!newDoc.code) {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_CODE');
                }
                if (!newDoc.name) {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_NAME');
                }else if(!Utils.validateName(newDoc.name)){
                    errorMess += $filter('translate')('DOCUMENT.INVALID_DOCUMENT_NAME');
                }
                if (!newDoc.sent_date) {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_DATE');
                }
                if (newDoc.is_encrypt && newDoc.encrypt_password) {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_ENCRYPT_PASSWORD');
                }
                if (!newDoc.expired_date) {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_EXPIRED_DATE');
                } else {
                    if (!Utils.isAfterToday(new Date(newDoc.expiredDate))) {
                        errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_EXPIRED_DATE_TODAY');
                    }
                }
                if (!errorMess) {
                    ctrl.currentStep++;
                    ctrl.newDoc.samplePartners = [];
                    ctrl.sampleSignatures.forEach(signature => {
                        let signing_method = [];
                        let tmp = signature.sign_method == null ? [] : signature.sign_method.split(",");
                        tmp.forEach(method => {
                            if (method != "") {
                                signing_method.push(method);
                            }
                        });
                        let partner = {
                            'order_assignee': signature.order_assignee,
                            'organisation_type': signature.is_my_organisation == 1 ? "1" : "3",
                            'company_name': signature.is_my_organisation == 1 ? ctrl.company.name : "",
                            'tax': signature.is_my_organisation == 1 ? ctrl.company.tax_number : "",
                            'assignees': [
                                {
                                    'signature_id': signature.id,
                                    'full_name': signature.is_my_organisation == 1 ? signature.full_name : "",
                                    "email": signature.is_my_organisation == 1 ? signature.email : "",
                                    "phone": signature.is_my_organisation == 1 ? signature.phone : "",
                                    "national_id": signature.is_my_organisation == 1 ? signature.national_id : "",
                                    "assign_type": "2",
                                    "signing_method": signing_method,
                                    "message": "",
                                    "noti_type": "1",
                                }
                            ]
                        }
                        ctrl.newDoc.samplePartners.push(partner);
                    })

                } else {
                    NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                }
            } else {
                if (newDoc.document_type_id == "-1") {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_TYPE');
                }
                if (newDoc.addendum_type == "-1" && $scope.isAddendum) {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_ADDENDUM_TYPE');
                }
                if (!newDoc.code) {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_CODE');
                }
                if (!newDoc.name) {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_NAME');
                }else if(!Utils.validateVietnameseCharacterWithNumber(newDoc.name)){
                    errorMess += $filter('translate')('DOCUMENT.INVALID_DOCUMENT_NAME');
                }
                if(!$scope.isAddendum || newDoc.addendum_type == 1 ){
                    if(newDoc.expired_type == "-1"){
                        errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_EXPIRED_TYPE');
                    }
                    if(newDoc.expired_type == "1" && !newDoc.doc_expired_date){
                        errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOC_EXPIRED_DATE');
                    }
                    if(newDoc.expired_type == "2" && !newDoc.expired_month ){
                        errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_EXPIRED_MONTH');
                    }else if(newDoc.expired_month && !Utils.isPositiveNumber(newDoc.expired_month)){
                        errorMess += $filter('translate')('DOCUMENT.INVALID_EXPIRED_MONTH');
                    }
                }
                if (!newDoc.sent_date) {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_DATE');
                }
                if (!newDoc.expired_date) {
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_EXPIRED_DATE');
                } else {
                    if (!Utils.isAfterToday(new Date(newDoc.expiredDate))) {
                        errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_EXPIRED_DATE_TODAY');
                    }
                }
                if (ctrl.selectedFile.length == 0) {
                    errorMess += $filter('translate')('DOCUMENT.NOT_UPLOAD_FILE');
                }

                if (!errorMess) {

                    newDoc.isLoading = true;
                    XuLyTaiLieuThuongMai.goChooseAssignee(newDoc).then(function (response) {
                        if (response.data.success) {
                            ctrl.currentStep++;
                        } else {
                            NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        }
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                } else {
                    NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                }
            }
        }

        ctrl.finishDrafting = function() {
            let newDoc = ctrl.newDoc;
            if (newDoc.is_use_template) {
                let infos = [];
                let errorMess = "";
                ctrl.sampleInfos.forEach(info => {
                    if (info.type == 'text') {
                        if (info.is_required && !info.content) {
                            errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_SAMPLE_INFO', { 'descriptionInfo': info.description });
                        } else {
                            infos.push({
                                id: info.id,
                                content: info.content
                            })
                        }
                    } else if (info.type == 'checkbox') {
                        let isSelected = false;
                        info.elements.forEach(element => {
                            if (element.content) {
                                isSelected = true;
                            }
                            infos.push({
                                id: element.id,
                                content: element.content
                            })
                        })
                        if (info.is_required && !info.content) {
                            errorMess += $filter('translate')('DOCUMENT.ERR_NOT_SELECT_SAMPLE_INFO', { 'descriptionInfo': info.description });
                        }
                    } else if (info.type == 'radio') {
                        let isSelected = false;
                        info.elements.forEach(element => {
                            infos.push({
                                id: element.id,
                                content: element.id == info.content
                            })
                        })
                        if (info.is_required && !info.content) {
                            errorMess += $filter('translate')('DOCUMENT.ERR_NOT_SELECT_SAMPLE_INFO', { 'descriptionInfo': info.description });
                        }
                    }
                })
                if (!errorMess) {
                    if(ctrl.isAutoOrder){
                        newDoc.code = newDoc.codeNum + '-' + newDoc.codeText;
                    }
                    let createdDoc = {
                        is_use_template: newDoc.is_use_template,
                        document_sample_id: newDoc.document_sample_id,
                        code: newDoc.code,
                        name: newDoc.name,
                        expired_type: newDoc.expired_type,
                        doc_expired_date: newDoc.docExpiredDate,
                        expired_month: newDoc.expired_month,
                        sentDate: newDoc.sentDate,
                        expiredDate: newDoc.expiredDate,
                        addendum_type: newDoc.addendum_type,
                        parent_id : newDoc.parent_id,
                        is_verify_content: newDoc.is_verify_content,
                        partners: newDoc.samplePartners,
                        infos: infos,
                        isLoading: true
                    }
                    XuLyTaiLieuThuongMai.createDocumentFromTemplate(createdDoc).then(function (response) {
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        if($scope.isAddendum){
                            $state.go("index.commerce.viewDocument", {docId: $stateParams.parentId});
                        } else {
                            $state.go("index.commerce.documentList");
                        }
                    }, function (response) {
                        $scope.initBadRequest(response);
                        if(response.data.message == "SERVER.EXISTED_DOCUMENT_CODE" || response.data.message == "DOCUMENT.ERR_EMPTY_DOCUMENT_CODE_NUM"){
                            $scope.isExistedDocumentCode = true;
                        }
                    });

                } else {
                    NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                }
            } else {
                let signature = [];
                let isError = false;
                ctrl.lstSigningAssignee.forEach(partner => {
                    partner.assignee.forEach(signee => {
                        if (signee.signatures.length == 0) {
                            isError = true;
                        }
                        signature.push({
                            assign_id: signee.assignee_id,
                            signatures: signee.signatures
                        });
                    })
                })
                if (isError) {
                    NotificationService.error($filter('translate')("DOCUMENT.ERR_NOT_DRAG_SIGNATURE"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    return;
                }
                let data = {
                    document_id: ctrl.newDoc.id,
                    texts: ctrl.texts,
                    signature: signature,
                    isLoading: true
                }

                XuLyTaiLieuThuongMai.finishDrafting(data).then(function (response) {
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    if($scope.isAddendum){
                        $state.go("index.commerce.viewDocument", {docId: $stateParams.parentId});
                    } else {
                        $state.go("index.commerce.documentList");
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }
        }

        ctrl.goStep3 = function () {
            let newDoc = ctrl.newDoc;
            if (newDoc.is_use_template) {
                let partners = ctrl.newDoc.samplePartners;
                let isError = false;
                let isErrorSigning = false;
                let isErrorOrder = false;
                partners.forEach(partner => {
                    if (partner.organisation_type != 3) {
                        if (!partner.company_name) {
                            partner.company_name_err = true;
                            isError = true;
                        }
                        if (!partner.tax) {
                            partner.tax_err = true;
                            isError = true;
                        }
                    } else {
                        partner.company_name = "";
                        partner.tax = "";
                    }
                    let hasSigning = false;
                    partner.assignees.forEach(assignee => {
                        if (!assignee.full_name) {
                            assignee.full_name_err = true;
                            isError = true;
                        }
                        if (!assignee.email) {
                            assignee.email_err = true;
                            isError = true;
                        } else {
                            if (!Utils.validateEmail(assignee.email)) {
                                assignee.email_err_format = true;
                                isError = true;
                            } else {
                                assignee.email_err_format = false;
                            }
                        }
                        if (assignee.phone && !Utils.isValidPhoneNumber(assignee.phone)) {
                            assignee.phone_err_format = true;
                            isError = true;
                        } else {
                            assignee.phone_err_format = false;
                        }
                        if (assignee.assign_type == "-1") {
                            assignee.assign_type_err = true;
                            isError = true;
                        }
                        if (assignee.noti_type == "-1") {
                            assignee.noti_type_err = true;
                            isError = true;
                        } else {
                            if (assignee.noti_type == "2" || assignee.noti_type == "3") {
                                if (!assignee.phone) {
                                    assignee.phone_err = true;
                                    isError = true;
                                }
                            }
                        }

                        if (assignee.assign_type == 2) {
                            hasSigning = true;
                            if (partner.organisation_type == 3) {
                                if (!assignee.national_id) {
                                    assignee.national_id_err = true;
                                    isError = true;
                                }
                            }
                            if (assignee.signing_method.length == 0) {
                                assignee.signing_method_err = true;
                                isError = true;
                            } else {
                                assignee.sign_method = ",";
                                assignee.signing_method.forEach(method => {
                                    assignee.sign_method += method + ",";
                                })
                            }
                        }
                    })

                    if (!hasSigning) {
                        isErrorSigning = true;
                    }
                })
                if (isError) {
                    NotificationService.error($filter('translate')("SERVER.INVALID_INPUT"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    return;
                }
                if (isErrorOrder) {
                    NotificationService.error($filter('translate')("DOCUMENT.ERROR_ORDER"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    return;
                }
                if (isErrorSigning) {
                    NotificationService.error($filter('translate')("DOCUMENT.ERR_NOT_SIGNING_PARTICIPANT"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    return;
                }
                ctrl.currentStep++;
            } else {
                let partners = ctrl.newDoc.partners;
                let isError = false;
                let isErrorSigning = false;
                let isErrorOrder = false;
                partners.forEach(partner => {
                    if (partner.organisation_type != 3) {
                        if (!partner.company_name) {
                            partner.company_name_err = true;
                            isError = true;
                        }
                        if (!partner.tax) {
                            partner.tax_err = true;
                            isError = true;
                        }
                    } else {
                        partner.company_name = "";
                        partner.tax = "";
                    }
                    if (partner.organisation_type != 1) {
                        if (!Utils.isPositiveNumber(partner.order_assignee) || partner.order_assignee == 1) {
                            isErrorOrder = true;
                        }
                    }
                    let hasSigning = false;
                    partner.assignees.forEach(assignee => {
                        if (!assignee.full_name) {
                            assignee.full_name_err = true;
                            isError = true;
                        }
                        if (!assignee.email) {
                            assignee.email_err = true;
                            isError = true;
                        } else {
                            if (!Utils.validateEmail(assignee.email)) {
                                assignee.email_err_format = true;
                                isError = true;
                            } else {
                                assignee.email_err_format = false;
                            }
                        }
                        if (assignee.phone && !Utils.isValidPhoneNumber(assignee.phone)) {
                            assignee.phone_err_format = true;
                            isError = true;
                        } else {
                            assignee.phone_err_format = false;
                        }
                        if (assignee.assign_type == "-1") {
                            assignee.assign_type_err = true;
                            isError = true;
                        }
                        if (assignee.noti_type == "-1") {
                            assignee.noti_type_err = true;
                            isError = true;
                        } else {
                            if (assignee.noti_type == "2" || assignee.noti_type == "3") {
                                if (!assignee.phone) {
                                    assignee.phone_err = true;
                                    isError = true;
                                }
                            }
                        }

                        if (assignee.assign_type == 2) {
                            hasSigning = true;
                            if (partner.organisation_type == 3) {
                                if (!assignee.national_id) {
                                    assignee.national_id_err = true;
                                    isError = true;
                                }
                            }
                            if (assignee.signing_method.length == 0) {
                                assignee.signing_method_err = true;
                                isError = true;
                            } else {
                                assignee.sign_method = ",";
                                assignee.signing_method.forEach(method => {
                                    assignee.sign_method += method + ",";
                                })
                            }
                        }
                    })

                    if (!hasSigning) {
                        isErrorSigning = true;
                    }
                })
                if (isError) {
                    NotificationService.error($filter('translate')("SERVER.INVALID_INPUT"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    return;
                }
                if (isErrorOrder) {
                    NotificationService.error($filter('translate')("DOCUMENT.ERROR_ORDER"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    return;
                }
                if (isErrorSigning) {
                    NotificationService.error($filter('translate')("DOCUMENT.ERR_NOT_SIGNING_PARTICIPANT"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    return;
                }
                let data = {
                    document_id: ctrl.newDoc.id,
                    partners: partners,
                    isLoading: true,
                    is_order_approval: ctrl.newDoc.is_order_approval
                }
                XuLyTaiLieuThuongMai.goStep3(data).then(function (response) {
                    if (response.data.success) {
                        ctrl.getSignDocument(ctrl.newDoc.id);
                        ctrl.currentStep++;
                        ctrl.lstSigningAssignee = []
                        ctrl.newDoc.partners = response.data.data.partners;
                        ctrl.newDoc.partners.forEach(partner => {
                            let group = {
                                id: partner.id,
                                name: `[${partner.order_assignee}] ${partner.company_name ?? ""}`,
                                assignee: []
                            }
                            partner.organisation_type += "";
                            partner.assignees.forEach(assignee => {
                                assignee.assign_type += "";
                                assignee.noti_type += "";
                                if (assignee.assign_type == 2) {
                                    assignee.signing_method = [];
                                    let tmp = assignee.sign_method == null ? [] : assignee.sign_method.split(",");
                                    tmp.forEach(method => {
                                        if (method != "") {
                                            assignee.signing_method.push(method);
                                        }
                                    });
                                    group.assignee.push({
                                        assignee_id: assignee.id,
                                        assignee_name: `${assignee.full_name} - ${assignee.email}`,
                                        signatures: [],
                                        partner_id: partner.id
                                    });
                                }
                            })
                            ctrl.lstSigningAssignee.push(group);
                        })
                    } else {
                        NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }

        }

        ctrl.getSignDocument = function (docId) {
            if(!$scope.scale){
                $scope.scale = "" + 1.5
            }
            var formData = new FormData();
            formData.append("id", docId);
            XuLyTaiLieuThuongMai.getSignDocument(formData).then(function (response) {
                ctrl.docFile = response;
                signature.init(response, $scope.scale);
                signDoc.data.document = response;
            })
        }

        $scope.onChangeFontSize = function(e) {
            let val = $("#text-font-size").val();
            if (val && signature.activeSignature && signature.activeSignature.type === "text") {
                signature.activeSignature.fontSize = parseInt(val) * 1.5;
                signature.currentCanvas.requestRenderAll();
                var txt = ctrl.texts.find(x => x.id === signature.activeSignature.id);
                if (txt) txt.size = parseInt(val);
            }
        }

        $scope.onChangeFontStyle = function(e) {
            let val = $("#text-font-style").val();
            if (val && signature.activeSignature && signature.activeSignature.type === "text") {
                signature.activeSignature.fontFamily = val;
                signature.currentCanvas.requestRenderAll();
                var txt = ctrl.texts.find(x => x.id === signature.activeSignature.id);
                if (txt) txt.FontFamily = val;
            }
        }

        $scope.onDrawingFiles = function(canvas, p, type) {
            signature.currentCanvas = canvas;
            var page = parseInt(canvas.lowerCanvasEl.dataset.pageNo);
            var imgElement = document.querySelector(".signature");
            if (p.top < 0)
                p.top = 0;
            if (p.left < 0)
                p.left = 0;
            var id = Utils.uuidv4();
            var matruong = `text-${signature.textCount + 1}`;
            var fontFamily = $("#text-font-style").val();
            if (type === "text") {
                var textBox = new fabric.Textbox('Nội dung văn bản', {
                    id: id,
                    matruong: matruong,
                    page: page,
                    top: p.top,
                    left: p.left,
                    width: 212,
                    fontSize: 12 * 1.5,
                    fontFamily: fontFamily,
                    backgroundColor: "#eaeff6",
                    type: "text",
                    hoverCursor: "text",
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
                var text = new Text(
                    id,
                    matruong,
                    page,
                    1,
                    12,
                    fontFamily,
                    'Nội dung văn bản',
                    212,
                    imgElement.offsetHeight,
                    p.left,
                    p.top,
                    canvas.width,
                    canvas.height);
                if (!ctrl.texts) {
                    ctrl.texts = [];
                }
                ctrl.texts.push(text);
                signature.textCount++;
            } else {
                if (ctrl.selectedSigningAssignee == "-1") {
                    NotificationService.error(`Vui lòng chọn <strong>Người ký</strong> của các bên tham gia trước khi thực hiện.`);
                    return
                }
                else {
                    var sigs = ctrl.selectedSigningAssignee.signatures;
                    var sigNo = sigs ? sigs.length : 0;
                    var imgInstance = new fabric.Image(imgElement, {
                        partner_id: ctrl.selectedSigningAssignee.partner_id,
                        assignee_id: ctrl.selectedSigningAssignee.assignee_id,
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
                        signature: sigNo,
                        sign_type: 1,
                        type: "signature"
                    });
                    /*imgInstance.setControlsVisibility({ mbr: true });*/
                    canvas.add(imgInstance);
                    var sign = new Signature(id,
                        page,
                        2,
                        imgInstance.scaleX * imgInstance.width,
                        imgInstance.scaleY * imgInstance.height,
                        p.left,
                        p.top,
                        canvas.width,
                        canvas.height);

                    ctrl.lstSigningAssignee.forEach(partner => {
                        if (partner.id == ctrl.selectedSigningAssignee.partner_id) {
                            partner.assignee.forEach(assignee => {
                                if (assignee.assignee_id == ctrl.selectedSigningAssignee.assignee_id) {
                                    assignee.signatures.push(sign);
                                }
                            })
                        }
                    })
                    if (signature.signatureTracking) signature.signatureTracking("add", sign);
                }

            }
        }

        signature.onObjectChange = (target) => {
            if (!target) {
                $("#add-text-form").addClass("hidden");
                return;
            }
            if (target && target.type === "text") {
                var textForm = $("#add-text-form").removeClass("hidden");
                textForm.find("#text-id").val(target.id);
                textForm.find("#text-code").val(target.matruong);
                textForm.find("#text-font-size").val(target.fontSize / 1.5);
                textForm.find("#text-font-style").val(target.fontFamily);
                textForm.find("#text-content").val(target.text);
                target.noidung = target.text;
                var object = ctrl.texts.find(x => x.id === target.id);
                if (object) {
                    object.noidung = target.text;
                    object.isvalid = target.isvalid;
                    if (object.size) {
                        object.size = target.fontSize / 1.5;
                        object.XAxis = target.left;
                        object.YAxis = target.top;
                        object.Width = target.width;
                        object.Height = target.height;
                    }
                }
            } else if (target.type === "signature") {
                $("#add-text-form").addClass("hidden");
                ctrl.lstSigningAssignee.forEach(partner => {
                    if (partner.id == target.partner_id) {
                        partner.assignee.forEach(assignee => {
                            if (assignee.assignee_id == target.assignee_id) {
                                let sigs = assignee.signatures;
                                var sig = sigs.find(x => x.id === target.id);
                                if (sig) {
                                    sig.XAxis = target.left;
                                    sig.YAxis = target.top;
                                    sig.Width = target.scaleX !== null ? target.width * target.scaleX : target.width;
                                    sig.Height = target.scaleY !== null ? target.height * target.scaleY : target.height;
                                }
                            }
                        })
                    }
                })
            }
        }

        $scope.onDeleteObject = function(eventData, tg) {
            var target = tg.target;
            var canvas = target.canvas;
            canvas.remove(target);
            canvas.requestRenderAll();
            if (target.type === "text") {
                ctrl.texts = ctrl.texts.filter(it => it.id !== target.id);
            } else if (target.type === "signature") {
                ctrl.lstSigningAssignee.forEach(partner => {
                    if (partner.id == target.partner_id) {
                        partner.assignee.forEach(assignee => {
                            if (assignee.assignee_id == target.assignee_id) {
                                assignee.signatures = assignee.signatures.filter(it => it.id !== target.id);
                            }
                        })
                    }
                })
                if (signature.signatureTracking) signature.signatureTracking("remove", target);
            }
        }

        $scope.onUploadFile = function (files) {
            let newDoc = ctrl.newDoc;
            if(ctrl.isAutoOrder){
                newDoc.code = newDoc.codeNum + '-' + newDoc.codeText;
            }
            let errorMess = "";
            if(newDoc.expired_type == 0){
                newDoc.doc_expired_date = null;
                newDoc.expired_month = '';
            } else if(newDoc.expired_type == 1 ) {
                newDoc.expired_month = '';
            } else if(newDoc.expired_type == 2){
                newDoc.doc_expired_date = null;
            }
            if(newDoc.doc_expired_date != null){
                newDoc.expiredDate = Utils.parseDateEnd(newDoc.expired_date);
                newDoc.docExpiredDate = Utils.parseDateEnd(newDoc.doc_expired_date);
                if((new Date(newDoc.docExpiredDate)).getTime() < (new Date(newDoc.expiredDate)).getTime()){
                    errorMess += $filter('translate')('DOCUMENT.ERR_INVALID_EXPIRED_DOC_DATE');
                }
            }else {
                newDoc.docExpiredDate = null;
            }
            if (newDoc.document_type_id == "-1") {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_TYPE');
            }
            if (newDoc.addendum_type == "-1" && $scope.isAddendum) {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_ADDENDUM_TYPE');
            }
            if (!newDoc.code) {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_CODE');
            }
            if(ctrl.isAutoOrder && !newDoc.codeText){
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_CODE_TEXT');
            }
            if (!newDoc.name) {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_NAME');
            }
            if (!newDoc.sent_date) {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_DATE');
            }
            if (!newDoc.expired_date) {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOCUMENT_EXPIRED_DATE');
            }
            if(!$scope.isAddendum || newDoc.addendum_type == 1 ){
                if(newDoc.expired_type == "-1"){
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_EXPIRED_TYPE');
                }
                if(newDoc.expired_type == "1" && !newDoc.doc_expired_date){
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_DOC_EXPIRED_DATE');
                }
                if(newDoc.expired_type == "2" && !newDoc.expired_month ){
                    errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_EXPIRED_MONTH');
                }else if(newDoc.expired_month && !Utils.isPositiveNumber(newDoc.expired_month)){
                    errorMess += $filter('translate')('DOCUMENT.INVALID_EXPIRED_MONTH');
                }
            }

            newDoc.files = [];
            files = [...files].reverse();
            var formData = new FormData();
            formData.append("id", newDoc.id);
            formData.append("document_type_id", newDoc.document_type_id);
            formData.append("code", newDoc.code);
            formData.append("name", newDoc.name);
            formData.append("addendum_type", newDoc.addendum_type);
            formData.append("parent_id", newDoc.parent_id);
            formData.append("expired_type", newDoc.expired_type);
            if(newDoc.doc_expired_date != null){
                formData.append("doc_expired_date",Utils.parseDate(newDoc.doc_expired_date));
            }
            formData.append("expired_month", newDoc.expired_month);
            formData.append("sent_date", Utils.parseDate(newDoc.sent_date));
            formData.append("expired_date", Utils.parseDate(newDoc.expired_date));
            formData.append("is_verify_content", newDoc.is_verify_content);
            if (!errorMess) {
                $.each(files, function (i, e) {
                    let file = {
                        name: e.name,
                        size: Utils.bytesToSize(e.size)
                    }
                    newDoc.files.push(file);
                    formData.append(`files[${i}]`, e);
                });
                var fp = $("#uploadFile");
                var lg = fp[0].files.length;
                var items = fp[0].files;
                var fileSize = 0;
                if (lg > 0) {
                    for (var i = 0; i < lg; i++) {
                        fileSize = fileSize + items[i].size;
                    }
                    if (fileSize > ($scope.configData.file_size_upload * 1024 * 1024)) {
                        NotificationService.error($filter('translate')('DOCUMENT.ERR_OVERSIZE_UPLOAD'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        selectedEl.html('');
                        $('#uploadFile').val('');
                        return false;
                    }
                }

                $('.loadingapp').removeClass('hidden');
                $.ajax({
                    method: 'POST',
                    enctype: 'multipart/form-data',
                    url: "/api/v1/noi-bo/tao-moi/upload-file",
                    contentType: false,
                    processData: false,
                    headers: {
                        'Authorization': 'Bearer ' + sessionStorage.getItem('etoken')
                    },
                    data: formData,
                    success: function (response) {
                        console.log(arguments);
                        $('.loadingapp').addClass('hidden');
                        ctrl.newDoc.id = response.data.id;
                        var lstFileId = response.data.total_fileid;
                        for(let i=0; i < lstFileId.length; i++){
                            newDoc.files[i].file_id = lstFileId[i];
                        }
                        newDoc.files.forEach(e => ctrl.selectedFile.push(e));
                        // ctrl.selectedFile = angular.copy(newDoc.files);
                        $scope.isExistedDocumentCode = false;
                        $scope.$digest();
                    },
                    error: function (response) {
                        $('.loadingapp').addClass('hidden');
                        NotificationService.error($filter('translate')(response.responseJSON.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        if(response.responseJSON.message == "SERVER.EXISTED_DOCUMENT_CODE" || response.responseJSON.message == "DOCUMENT.ERR_EMPTY_DOCUMENT_CODE_NUM"){
                            $scope.isExistedDocumentCode = true;
                        }
                        $scope.$digest();
                    },
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                percentComplete = parseInt(percentComplete * 100);
                                $(`#progressBar`).css('width', percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    }
                });

            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.refreshDocumentCode = function(){
            XuLyTaiLieuThuongMai.getCodeById({ id: ctrl.newDoc.document_type_id }).then(function (response) {
                ctrl.newDoc.code = response.data.data;
                ctrl.splitCode(ctrl.newDoc.code);
                NotificationService.success($filter('translate')('DOCUMENT.REFRESH_DOCUMENT_CODE_SUCCESS'), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
            },function (response) {
                $scope.initBadRequest(response);
            });
        }

        $scope.removeFile = function(file, index){
            const confirm = NotificationService.confirm($filter('translate')('COMMERCE.COMMON.REMOVE_FILE_CONFIRM', {'fileName': file.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function(response){
                XuLyTaiLieuThuongMai.removeFile(file).then(function (response) {
                    if (response.data.success) {
                        ctrl.selectedFile.splice(index, 1);
                        if(ctrl.selectedFile.length == 0){
                            $(`#progressBar`).css('width', '0%');
                        }
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    } else {
                        NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.selectAssignee = function (partner, index) {
            $scope.searchAssignee = partner;
            $scope.searchAssignee.index = index;
            // $scope.searchAssignee.assignees[$scope.searchAssignee.index].addNewOptions = false;
            $uibModal.open({
                animation: true,
                templateUrl: "vcontract/views/commerce/selectAssignee.html",
                windowClass: "fade show modal-blur",
                size: "xl modal-dialog-centered",
                backdrop: "static",
                backdropClass: "show",
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope,
            });
        }

        $scope.onPartnerOrganisationTypeChange = function(partner){
            partner.assignees = [ctrl.initNewAssignee()];
            partner.company_name = '';
            partner.tax = '';
            partner.addNewOptions = false;
        }

        $scope.initNewEmployee = function(assignee) {
            return {
                emp_code: "",
                reference_code: "",
                emp_name: assignee.full_name,
                dob: "",
                sex: "-1",
                address1: "",
                address2: "",
                ethnic: "",
                nationality: "",
                national_id: assignee.national_id,
                national_date: "",
                national_address_provide: "",
                email: assignee.email,
                phone: assignee.phone,
                note: "",
                department_id: "-1",
                position_id: "-1",
                status: true
            }
        }

        $scope.initNewCustomer = function(assignee) {
            return {
                name: "",
                code: "",
                note: "",
                status: true,
                customer_type: true,
                phone: "",
                email: assignee.email,
                tax_number: "",
                address: "",
                bank_info: "",
                bank_number: "",
                bank_account: "",
                representative: "",
                representative_position: "",
                contact_phone: "",
                contact_name: "",
            }
        };

        $scope.openAddEmployeeModal = function (partner, index) {
            $scope.searchAssignee = partner;
            $scope.searchAssignee.index = index;
            $scope.curAssignee = $scope.searchAssignee.assignees[$scope.searchAssignee.index];
            $scope.editEmployee = $scope.initNewEmployee($scope.curAssignee);
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateEmployeeFromDocument.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered modal-dialog-scrollable',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        $scope.openAddCustomerModal = function (partner, index) {
            $scope.searchAssignee = partner;
            $scope.searchAssignee.index = index;
            $scope.curAssignee = $scope.searchAssignee.assignees[$scope.searchAssignee.index];
            $scope.editCustomer = $scope.initNewCustomer($scope.curAssignee);
            $scope.editCustomer.customer_type = partner.organisation_type == 2 ? true : false;
            if($scope.editCustomer.customer_type){
                if(index == 0){
                    $scope.editCustomer.customer_type = true;
                    $scope.editCustomer.name = partner.company_name;
                    $scope.editCustomer.tax_number = partner.tax;
                    $scope.editCustomer.representative = $scope.curAssignee.full_name;
                    $scope.editCustomer.email = $scope.curAssignee.email;
                    $scope.editCustomer.phone = $scope.curAssignee.phone;
                } else {
                    $scope.editCustomer.customer_type = false;
                    $scope.editCustomer.name = $scope.curAssignee.full_name;
                    $scope.editCustomer.email = $scope.curAssignee.email;
                    $scope.editCustomer.phone = $scope.curAssignee.phone;
                }
            } else {
                $scope.editCustomer.name = $scope.curAssignee.full_name;
                $scope.editCustomer.phone = $scope.curAssignee.phone;
                $scope.editCustomer.email = $scope.curAssignee.email;
            }
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateCustomerFromDocument.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered modal-dialog-scrollable',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $compile, $filter, NhanVien, KhachHangDoiTac, DTOptionsBuilder, DTColumnBuilder) {

        $scope.dtEmployeeInstance = {};
        $scope.dtCustomerInstance = {};

        $scope.searchEmployee = {
            position_id: "-1",
            department_id: "-1",
            keyword: "",
            status: "-1"
        };

        $scope.searchCustomer = {
            status: "-1",
            // customer_type: $scope.searchAssignee.organisation_type == 3 ? 0 : 1,
            keyword: ""
        }

        $scope.dtEmployeeIntanceCallback = function (instance) {
            $scope.dtEmployeeInstance = instance;
        }

        $scope.dtCustomerIntanceCallback = function (instance) {
            $scope.dtCustomerInstance = instance;
        }

        function getDataEmployee(sSource, aoData, fnCallback, oSettings) {
            var draw = aoData[0].value;
            var order = aoData[2].value[0];
            order.columnName = aoData[1].value[order.column].data;
            var start = aoData[3].value;
            var length = aoData[4].value;
            var params = {
                draw: draw,
                order: order,
                start: start,
                limit: length,
                searchData: $scope.searchEmployee,
            }
            if($scope.searchAssignee.organisation_type == 1){
                params.isLoading = true;
            }
            NhanVien.search(params).then(function (response) {
                $scope.lstEmployee = response.data.data.data;
                $scope.lstEmployee.forEach(employee => {
                    employee.department_id += "";
                    employee.position_id += "";
                });
                fnCallback(response.data.data);
            }, function (response) {
                var records = {
                    'draw': draw,
                    'recordsTotal': 0,
                    'recordsFiltered': 0,
                    'data': []
                };
                fnCallback(records);
                $scope.initBadRequest(response);
            })
        }

        $scope.dtEmployeeColumns = [
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn('emp_name').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('emp_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('email').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_EMAIL')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('email', meta.row, data);
            }),
            DTColumnBuilder.newColumn('phone').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_PHONE')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('phone', meta.row, data);
            }),
            DTColumnBuilder.newColumn('national_id').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_NATIONAL_ID')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('national_id', meta.row, data);
            }),
            DTColumnBuilder.newColumn('department_name').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_DEPARTMENT_NAME')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('department_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('position_name').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_POSITION_NAME')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('position_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return `<button class="btn btn-sm btn-primary" ng-click="onSelectSearchAssginee(${meta.row})">${$filter('translate')('DOCUMENT.ADD_ASSIGNEE')}</button>`;
            }),
        ];

        $scope.dtEmployeeOptions = DTOptionsBuilder.newOptions()
            .withPaginationType('full_numbers')
            .withDisplayLength(20)
            .withOption('order', [[1, 'asc']])
            .withOption('searching', false)
            .withLanguage(Utils.getDataTableLanguage($scope.loginUser.language))
            .withDOM(Utils.getDataTableDom())
            .withOption('lengthMenu', [20, 50, 100])
            .withOption('responsive', true)
            .withOption('processing', true)
            .withOption('serverSide', true)
            .withOption('paging', true)
            .withFnServerData(getDataEmployee)
            .withOption('createdRow', function (row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            })


        $scope.onSearchEmployee = function(){
            $scope.dtEmployeeInstance.rerender();
        }

        function getDataCustomer(sSource, aoData, fnCallback, oSettings) {
            var draw = aoData[0].value;
            var order = aoData[2].value[0];
            order.columnName = aoData[1].value[order.column].data;
            var start = aoData[3].value;
            var length = aoData[4].value;
            var params = {
                draw: draw,
                order: order,
                start: start,
                limit: length,
                searchData: $scope.searchCustomer,
            }
            if($scope.searchAssignee.organisation_type == 2 || $scope.searchAssignee.organisation_type == 3){
                params.isLoading = true;
            }
            KhachHangDoiTac.search(params).then(function (response) {
                $scope.lstCustomer = response.data.data.data;
                fnCallback(response.data.data);
            }, function (response) {
                var records = {
                    'draw': draw,
                    'recordsTotal': 0,
                    'recordsFiltered': 0,
                    'data': []
                };
                fnCallback(records);
                $scope.initBadRequest(response);
            })
        }

        $scope.dtCustomerOptions = DTOptionsBuilder.newOptions()
            .withPaginationType('full_numbers')
            .withDisplayLength(20)
            .withOption('order', [[1, 'asc']])
            .withOption('searching', false)
            .withLanguage(Utils.getDataTableLanguage($scope.loginUser.language))
            .withDOM(Utils.getDataTableDom())
            .withOption('lengthMenu', [20, 50, 100])
            .withOption('responsive', true)
            .withOption('processing', true)
            .withOption('serverSide', true)
            .withOption('paging', true)
            .withFnServerData(getDataCustomer)
            .withOption('createdRow', function (row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            })
        $scope.dtCustomerPersonalColumns = [
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('email').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_EMAIL')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('email', meta.row, data);
            }),
            DTColumnBuilder.newColumn('phone').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_PHONE')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('phone', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return `<button class="btn btn-sm btn-primary" ng-click="onSelectSearchAssginee(${meta.row})">${$filter('translate')('DOCUMENT.ADD_ASSIGNEE')}</button>`;
            }),
        ];
        $scope.dtCustomerOrganizationColumns = [
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('representative').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_REPRESENTATIVE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('representative', meta.row, data);
            }),
            DTColumnBuilder.newColumn('tax_number').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_TAX_NUMBER')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('tax_number', meta.row, data);
            }),
            DTColumnBuilder.newColumn('email').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_EMAIL')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('email', meta.row, data);
            }),
            DTColumnBuilder.newColumn('phone').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_PHONE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('phone', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return `<button class="btn btn-sm btn-primary" ng-click="onSelectSearchAssginee(${meta.row})">${$filter('translate')('DOCUMENT.ADD_ASSIGNEE')}</button>`;
            }),
        ];


        $scope.onSearchCustomer = function () {
            $scope.dtCustomerInstance.rerender();
        }

        $scope.onSelectSearchAssginee = function (row) {
            if($scope.searchAssignee.organisation_type == 1){
                $scope.searchAssignee.assignees[$scope.searchAssignee.index].full_name = $scope.lstEmployee[row].emp_name;
                $scope.searchAssignee.assignees[$scope.searchAssignee.index].email = $scope.lstEmployee[row].email;
                $scope.searchAssignee.assignees[$scope.searchAssignee.index].phone = $scope.lstEmployee[row].phone;
                $scope.searchAssignee.assignees[$scope.searchAssignee.index].national_id = $scope.lstEmployee[row].national_id;
            } else {
                $scope.searchAssignee.assignees[$scope.searchAssignee.index].full_name = $scope.lstCustomer[row].name;
                $scope.searchAssignee.assignees[$scope.searchAssignee.index].email = $scope.lstCustomer[row].email;
                $scope.searchAssignee.assignees[$scope.searchAssignee.index].phone = $scope.lstCustomer[row].phone;
            }
            $scope.cancel();
        };

        $scope.onCreateUpdateEmployee = function () {
            let employee = $scope.editEmployee;
            let errorMess = "";
            if (!employee.emp_code) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_CODE');
            }
            if (!employee.emp_name) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_NAME');
            }
            if (!employee.dob) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_DOB');
            }
            if (employee.sex == "-1" || !employee.sex) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_GENDER');
            }
            if (!employee.address1) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_ADDRESS1');
            }
            if (!employee.address2) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_ADDRESS2');
            }
            if (!employee.national_id) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_NATIONAL_ID');
            }
            if (!employee.national_date) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_NATIONAL_DATE');
            }
            if (!employee.national_address_provide) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_NATIONAL_ADDRESS_PROVIDE');
            }
            if (!employee.email) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_EMAIL');
            } else {
                if(!Utils.validateEmail(employee.email)){
                    errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_INVALID_EMPLOYEE_EMAIL');
                }

            }

            if (employee.department_id == "-1" || !employee.department_id) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_DEPARTMENT_NAME');
            }
            if (employee.position_id == "-1" || !employee.position_id) {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_POSITION_NAME');
            }

            employee.birthday = !employee.dob ? "" : Utils.parseDate(employee.dob);
            employee.nationalDate = !employee.national_date ? "" : Utils.parseDate(employee.national_date);

            if (!errorMess) {
                NhanVien.create(employee).then(function (response) {
                    handleUpdateNhanVienResponse(response);
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            } else {
                NotificationService.error(errorMess);
            }

        }

        function handleUpdateNhanVienResponse(response) {
            if (response.data.success) {
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                $scope.curAssignee.addNewOptions = false;
                $scope.curAssignee.full_name = $scope.editEmployee.emp_name;
                $scope.curAssignee.email = $scope.editEmployee.email;
                $scope.curAssignee.phone = $scope.editEmployee.phone;
                $scope.curAssignee.national_id = $scope.editEmployee.national_id;
                $uibModalInstance.close(false);
            } else {
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.onCreateUpdateCustomer = function () {
            let customer = $scope.editCustomer;
            let errorMess = "";
            if (!customer.code) {
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_CODE');
            }
            if (!customer.name) {
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_NAME');
            }
            if (!customer.address) {
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_ADDRESS');
            }
            if (!customer.email) {
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_EMAIL');
            } else {
                if(!Utils.validateEmail(customer.email)){
                    errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_INVALID_CUSTOMER_EMAIL');
                }
            }

            if (!errorMess) {
                    KhachHangDoiTac.create(customer).then(function (response) {
                        handleUpdateKhachHangDoiTacResponse(response);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });

            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdateKhachHangDoiTacResponse(response) {
            if (response.data.success) {
                $scope.curAssignee.addNewOptions = false;
                $scope.curAssignee.full_name = $scope.editCustomer.name;
                $scope.curAssignee.email = $scope.editCustomer.email;
                $scope.curAssignee.phone = $scope.editCustomer.phone;
                $uibModalInstance.close(false);
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
            } else {
                NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
