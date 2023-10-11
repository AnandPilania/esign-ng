var userSignature = null;
(function () {
    'use strict';
    angular.module("app", ['webcam']).controller("MainCtrl", ['$scope', '$state', '$uibModal', '$http', '$window', '$timeout', '$filter', 'XuLyTaiLieu', 'WebcamService', MainCtrl]);
    function MainCtrl($scope, $state, $uibModal, $http, $window, $timeout, $filter, XuLyTaiLieu, WebcamService) {

        var ctrl = this;


        ctrl.registerICA = 0;
        $scope.initDocument = function() {
            XuLyTaiLieu.initViewDoc({ isLoading: true }).then(function (response) {
                ctrl.viewDoc = response.data.data.document;
                $scope.loginUser = response.data.data.user;
                ctrl.viewDoc.sent_date = Utils.formatDateNoTime(new Date(ctrl.viewDoc.sent_date));
                ctrl.viewDoc.expired_date = Utils.formatDateNoTime(new Date(ctrl.viewDoc.expired_date));
                ctrl.viewDoc.created_at = Utils.formatDate(new Date(ctrl.viewDoc.created_at));
                let state = $scope.lstDocumentState.find(x => x.id == ctrl.viewDoc.document_state);
                ctrl.viewDoc.state_name = state.description;
                if(!$scope.scale){
                    $scope.scale = "" + 1.5
                }
                $scope.loginUser.assignType = ($scope.lstAssigneeRole.find(x => x.id == $scope.loginUser.assign_type)).description;
                $scope.loginUser.assign_date = $scope.loginUser.submit_time == null ? ".../.../..." : Utils.formatDate(new Date($scope.loginUser.submit_time));
                ctrl.viewDoc.send_doc_date = ctrl.viewDoc.send_doc_date == null ? "N/A" : Utils.formatDate(new Date(ctrl.viewDoc.send_doc_date));
                ctrl.viewDoc.finished_date = ctrl.viewDoc.finished_date == null ? $filter('translate')("DOCUMENT.NOT_COMPLETE") : Utils.formatDate(new Date(ctrl.viewDoc.finished_date));
                // for (let i = 0; i < ctrl.viewDoc.partners.length; i++) {
                //     let partner = ctrl.viewDoc.partners[i];
                //     if (partner.organisation_type == 3) {
                //         partner.displayName = `[${i+1}] ` + $filter('translate')("DOCUMENT.PERSONAL");
                //     } else {
                //         partner.displayName = `[${i+1}] ${partner.company_name} - ${partner.tax}`;
                //     }
                //     partner.assignees.forEach(assignee => {
                //         assignee.assignType = ($scope.lstAssigneeRole.find(x => x.id == assignee.assign_type)).description;
                //         assignee.sent_email = "";
                //         assignee.assign_date = assignee.submit_time == null ? "" : Utils.formatDate(new Date(assignee.submit_time));;
                //     })
                // }
                if (ctrl.viewDoc.cur) {
                    ctrl.viewDoc.hasApprovalRole = ctrl.viewDoc.cur.email == $scope.loginUser.email && ctrl.viewDoc.cur.assign_type == 1;
                    ctrl.viewDoc.hasSignRole = ctrl.viewDoc.cur.email == $scope.loginUser.email && ctrl.viewDoc.cur.assign_type == 2;
                    ctrl.viewDoc.hasDenyRole = ctrl.viewDoc.cur.email == $scope.loginUser.email;
                    ctrl.viewDoc.hasActionRole = ctrl.viewDoc.hasApprovalRole || ctrl.viewDoc.hasSignRole || ctrl.viewDoc.hasDenyRole;
                    if (ctrl.viewDoc.cur.assign_type == 2) {
                        ctrl.viewDoc.cur.sign_method = ctrl.viewDoc.cur.sign_method ?? "";
                        ctrl.viewDoc.lstSigningMethod = ctrl.viewDoc.cur.sign_method.split(',');
                        ctrl.viewDoc.approveMethod = 0;
                        ctrl.viewDoc.lstSigningMethod.forEach(method => {
                            if (method == "0") {
                                ctrl.viewDoc.canSignToken = true;
                                ctrl.viewDoc.approveMethod++;
                            } else if (method == "1") {
                                ctrl.viewDoc.canSignOtp = true;
                                ctrl.viewDoc.approveMethod++;
                            } else if (method == "2") {
                                ctrl.viewDoc.canSignKyc = true;
                                ctrl.viewDoc.approveMethod++;
                            } else if (method == "3") {
                                ctrl.viewDoc.canSignRemote = true;
                                ctrl.viewDoc.approveMethod++;
                            }
                        });
                    } else {
                        ctrl.viewDoc.approveMethod = 1;
                    }
                    ctrl.signatureUrl = 'vcontract_sign/assets/images/signature-icon.svg';
                    if (ctrl.viewDoc.cur.signature) {
                        $scope.isSelectSignature = true;
                        ctrl.signatureUrl = ctrl.viewDoc.cur.signature.image_signature;
                    }
                }
                ctrl.lstPosition = [];
                $scope.getSignDocument(ctrl.viewDoc.id, $scope.scale);
            }, function (response) {
                NotificationService.error($filter('translate')(response.data.message));
            })
        };

        $scope.onChangeScale = function () {
            ctrl.lstPosition = [];
            $scope.getSignDocument(ctrl.viewDoc.id, $scope.scale);
        }
        $scope.initDocument();

        $scope.getSignDocument = function (docId, scale) {
            if (docId) {
                var formData = new FormData();
                formData.append("id", docId);
                XuLyTaiLieu.getSignDocument(formData).then(function (response) {
                    let oldCanv = document.getElementsByClassName('viewer-canvas-container');
                    while(oldCanv.length > 0){
                        oldCanv[0].parentNode.removeChild(oldCanv[0]);
                    }
                    ctrl.hasChangeSignature = false;
                    ctrl.docFile = response;
                    signature.init(response, scale, true, ctrl.viewDoc.signature_location, ctrl.signatureUrl);
                    $scope.$digest();
                })
            } else {
            }
        }

        ctrl.downloadDocument = function () {
            let fileName = Utils.removeVietnameseTones(ctrl.viewDoc.name) + ".pdf";
            Utils.downloadFormBinary(ctrl.docFile, fileName);
        }

        ctrl.printDocument = function () {
            let blobUrl = Utils.blobUrlFromBinary(ctrl.docFile);
            printJS(blobUrl);
        }

        $scope.onDrawingViewFiles = function (canvas, p, type) {
            signature.currentCanvas = canvas;
            let scale = $scope.scale / 1.5;
            let revertScale = 1.5 / $scope.scale
            let pageNo = canvas.lowerCanvasEl.dataset.pageNo;
            let id = Utils.uuidv4();
            let matruong = `text-${signature.textCount + 1}`;

            if (pageNo == p.page) {
                let imgInstance = new fabric.Image.fromURL(p.url, function (img) {
                    let scaleX = Number.parseFloat(p.width) / img.width * scale;
                    let scaleY = Number.parseFloat(p.height) / img.height * scale;
                    img.set({
                        partner_id: ctrl.viewDoc.cur.partner_id,
                        assignee_id: ctrl.viewDoc.cur.id,
                        page: Number.parseInt(p.page),
                        sigW: img.width,
                        sigH: img.height,
                        scaleX: scaleX,
                        scaleY: scaleY,
                        top: Number.parseFloat(p.top) * scale,
                        left: Number.parseFloat(p.left) * scale,
                        angle: 0,
                        opacity: 1,
                        id: id,
                        signature: signature.textCount,
                        sign_type: 1,
                        type: "signature"
                    })
                    canvas.add(img);
                });
                let sign = new Signature(id,
                    Number.parseInt(p.page),
                    2,
                    Number.parseFloat(p.width),
                    Number.parseFloat(p.height),
                    Number.parseFloat(p.left),
                    Number.parseFloat(p.top),
                    canvas.width * revertScale,
                    canvas.height * revertScale);
                ctrl.lstPosition.push(sign);
            }
        }

        signature.onObjectViewChange = (target) => {
            if (!target) {
                return;
            }
            let scale = 1.5 / $scope.scale
            if (target.type === "signature") {
                ctrl.hasChangeSignature = true;
                let sig = ctrl.lstPosition.find(x => x.id === target.id);
                if (sig) {
                    sig.XAxis = target.left * scale;
                    sig.YAxis = target.top * scale;
                    sig.Width = target.scaleX !== null ? target.width * target.scaleX * scale: target.width;
                    sig.Height = target.scaleY !== null ? target.height * target.scaleY * scale: target.height;
                }
            }

            $scope.$digest();
        }

        $scope.updateSignature = function(signature) {
            ctrl.signatureUrl = signature;
            $scope.isSelectSignature = true;
            if ($scope.signMethod == 'token') {
                ctrl.confirmSign();
            } else if ($scope.signMethod == 'otp') {
                ctrl.confirmSignOtp();
            } else if ($scope.signMethod == 'ekyc') {
                ctrl.confirmSignKyc();
            } else if ($scope.signMethod == 'mySign') {
                ctrl.openChooseSignRemote();
            }
        }

        $scope.updateTransferDocument = function() {
            ctrl.viewDoc.hasActionRole = false;
            ctrl.viewDoc.hasSignRole = false;
            ctrl.viewDoc.hasApprovalRole = false;
            ctrl.viewDoc.hasDenyRole = false;
            $scope.loginUser.state = 4;
            $scope.loginUser.assign_date = Utils.formatDate(new Date());
        }

        ctrl.onSaveLocation = function() {
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_SAVE_LOCATION'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                XuLyTaiLieu.saveSignatureLocation({'location': ctrl.lstPosition, isLoading: true}).then(function (response) {
                    if (response.data.success) {
                        ctrl.hasChangeSignature = false;
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            }).catch(function (err) {
                console.log(err);
            });
        }

        ctrl.finishDocument = function() {
            if (ctrl.viewDoc.hasApprovalRole) {
                ctrl.confirmApproval();
            } else if (ctrl.viewDoc.hasSignRole) {
                if (ctrl.viewDoc.canSignToken) {
                    ctrl.confirmSign()
                } else if (ctrl.viewDoc.canSignOtp) {
                    ctrl.confirmSignOtp()
                } else if (ctrl.viewDoc.canSignKyc) {
                    ctrl.confirmSignKyc()
                } else if (ctrl.viewDoc.canSignRemote) {
                    ctrl.openChooseSignRemote()
                }
            }
        }

        ctrl.openSelectSignature = function () {

            userSignature = $scope.isSelectSignature ? ctrl.signatureUrl : null;
            $scope.userSignature = {
                image_signature: userSignature ? userSignature : Utils.defaultUploadImage(),
                docId: ctrl.viewDoc.id
            }

            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract_sign/views/modal/selectSignature.html',
                windowClass: "fade show modal-blur",
                size: 'md modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalDocInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.confirmApproval = function () {
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_APPROVAL'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                XuLyTaiLieu.approveDocument({docId: ctrl.viewDoc.id, isLoading: true}).then(function (response) {
                    if (response.data.success) {
                        $scope.initDocument()
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            }).catch(function (err) {
                console.log(err);
            });
        }

        ctrl.confirmSign = function () {
            //TODO: kết nối với plugin
            $scope.openAction = false;
            if (!$scope.isSelectSignature) {
                $scope.signMethod = 'token';
                ctrl.openSelectSignature();
                return;
            }
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_SIGN'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                if (!$scope.isSelectSignature) {
                    NotificationService.error($filter('translate')('DOCUMENT.NOT_SELECT_SIGNATURE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    return;
                }
                ctrl.connectPlugin();
                // XuLyTaiLieu.signDocument({docId: ctrl.viewDoc.id, isLoading: true}).then(function (response) {
                //     if (response.data.success) {
                //         $scope.initDocument()
                //         NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                //     }
                // }, function (response) {
                //     NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                // });
            }).catch(function (err) {
                console.log(err);
            });
        }

        ctrl.connectPlugin = function() {
            if (!ctrl.viewDoc.cur.partner.tax && !ctrl.viewDoc.cur.national_id) {
                NotificationService.error($filter('translate')('DOCUMENT.INVALID_TAX_SIGN'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                return;
            }
            let url = Utils.getPluginUrl() + "api/certificates";
            $http({method:'GET',url:url}).then(function(response) {
                console.log(response);
                let cert = null;
                response.data.forEach(certificate => {
                    if ((ctrl.viewDoc.cur.partner.tax && certificate.subjectName.indexOf(ctrl.viewDoc.cur.partner.tax) != -1)
                        || (ctrl.viewDoc.cur.national_id && certificate.subjectName.indexOf(ctrl.viewDoc.cur.national_id) != -1)) {
                        cert= certificate;
                    }
                })
                if (!cert) {
                    NotificationService.error($filter('translate')('DOCUMENT.CHECK_USB_TOKEN'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    return;
                }
                ctrl.encodeDocument(cert);
            }, function(error) {
                NotificationService.error($filter('translate')('DOCUMENT.NOT_OPEN_PLUGIN'), $filter('translate')("COMMON.NOTIFICATION.WARNING"));
                return;
            });
        }

        ctrl.encodeDocument = function(certificate) {
            XuLyTaiLieu.getHashDoc({docId: ctrl.viewDoc.id, pubKey: certificate.publicKey, isLoading: true}).then(function (response) {
                if (response.data.success) {
                    let res = JSON.parse(response.data.data.data);
                    let url = Utils.getPluginUrl() + "api/sign/hash?serialNumber=" + certificate.serialNumber;
                    $http({method:'POST',url:url, data: [res.data]}).then(function(response) {
                        if (response) {
                            let hashed = response.data[0];
                            XuLyTaiLieu.signDocument({docId: ctrl.viewDoc.id, pubca: certificate.publicKey, ca: hashed, isLoading: true}).then(function (response) {
                                if (response.data.success) {
                                    $scope.initDocument(ctrl.viewDoc.id)
                                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                                }
                            }, function (response) {
                                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                            });
                        }

                    }, function(error) {
                        NotificationService.error($filter('translate')('SERVER.PROCESSING_ERROR'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        return;
                    });
                }
            }, function (response) {
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            });
        }

        ctrl.confirmSignOtp = function () {
            $scope.openAction = false;
            if (!$scope.isSelectSignature) {
                $scope.signMethod = 'otp';
                ctrl.openSelectSignature();
                return;
            }

            XuLyTaiLieu.sendOtp({docId: ctrl.viewDoc.id, isLoading: true}).then(function (response) {
                if (response.data.success) {
                    $scope.otpData = {
                        "otp": "",
                        "docId": ctrl.viewDoc.id,
                        "isLoading": true
                    }
                    $uibModal.open({
                        animation: true,
                        templateUrl: 'vcontract_sign/views/modal/sendOtpSignDocument.html',
                        windowClass: "fade show modal-blur",
                        size: 'md modal-dialog-centered',
                        backdrop: 'static',
                        backdropClass: 'show',
                        keyboard: false,
                        controller: ModalDocInstanceCtrl,
                        scope: $scope
                    });
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                }
            }, function (response) {
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            });
        }

        $scope.webcam = WebcamService.webcam;
        $scope.webcam.channel = {
            // the fields below are all optional
            videoHeight: 360,
            videoWidth: 480,
            video: null // Will reference the video element on success
        };
        $scope.webcam.success = function(image, type) {
            if ($scope.signEkycData.currentSignStep == 1) {
                $scope.signEkycData.dataFront = image;
            } else if ($scope.signEkycData.currentSignStep == 2) {
                $scope.signEkycData.dataBack = image;
            } else if ($scope.signEkycData.currentSignStep == 3) {
                $scope.signEkycData.dataFace = image;
            }
            $scope.signEkycData.isCapture = false;
            $scope.signEkycData.fotoContentType = type;
        };

        ctrl.confirmSignKyc = function (type) {
            $scope.isSign = type; // 0 : sign ekyc ; 1 : register chu ky I-CA
            $scope.openAction = false;

            if (!$scope.isSelectSignature) {
                $scope.signMethod = 'ekyc';
                ctrl.openSelectSignature();
                return;
            }

            $scope.signEkycData = {
                currentSignStep: 1,
                isCapture: true,
                dataFront: "",
                dataBack: "",
                dataFace: "",
                fotoContentType: "",
                docId: ctrl.viewDoc.id,
                cccd: ctrl.viewDoc.cur.national_id
            }
            $scope.ekycResult = {
                id: "",
                name: "",
                birthday: "",
                sex: "",
                hometown: "",
                address: "",
                issueDate: "",
                issueBy: "",
                sim:"",
                docId: ctrl.viewDoc.id,
                isLoading: true
            }
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract_sign/views/modal/signEkycDocument.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalDocInstanceCtrl,
                scope: $scope
            });
        }
        ctrl.mySignData = {
            "docId": "",
            "user_id": "",
            "credential_id": "",
            "status": -1,
            "message": "",
            "timeOut": 60
        }

        ctrl.openChooseSignRemote = function() {
            $scope.openAction = false;
            if (!$scope.isSelectSignature) {
                $scope.signMethod = 'mySign';
                ctrl.openSelectSignature();
                return;
            }
            $scope.signRemoteSigning = ctrl.signRemoteSigning;
            $scope.mySignData = ctrl.mySignData;
            $scope.mySignData.docId = ctrl.viewDoc.id;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract_sign/views/modal/chooseRemoteSign.html',
                windowClass: "fade show modal-blur",
                size: 'md modal-dialog-centered modal-dialog-scrollable',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalDocInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.signRemoteSigning = function(type, registerICA) {
            switch (type) {
                case '1':
                    $scope.mySignData = ctrl.mySignData;
                    $scope.countDown = ctrl.countDown;
                    $scope.credential_id = "";
                    $uibModal.open({
                        animation: true,
                        templateUrl: 'vcontract/views/modal/signMySign.html',
                        windowClass: "fade show modal-blur",
                        size: 'lg modal-dialog-centered modal-dialog-scrollable',
                        backdrop: 'static',
                        backdropClass: 'show',
                        keyboard: false,
                        controller: ModalDocInstanceCtrl,
                        scope: $scope
                    });
                    break;
                case '2':
                    $scope.confirmSignKyc = ctrl.confirmSignKyc;
                    $scope.signRemoteSigning = ctrl.signRemoteSigning;
                    $scope.registerICA = registerICA;
                    $scope.docId = ctrl.viewDoc.id;
                    $uibModal.open({
                        animation: true,
                        templateUrl: 'vcontract/views/modal/signChuKyICA.html',
                        windowClass: "fade show modal-blur",
                        size: 'lg modal-dialog-centered modal-dialog-scrollable',
                        backdrop: 'static',
                        backdropClass: 'show',
                        keyboard: false,
                        controller: ModalDocInstanceCtrl,
                        scope: $scope
                    });
                    break;
                default:

                    break;
            }
        }

        ctrl.countDown = function(){
            $timeout(function(){
                ctrl.mySignData.timeOut--;
                if(ctrl.mySignData.status == 3){
                    if(ctrl.mySignData.timeOut > 0){
                        ctrl.countDown();
                    } else {
                        ctrl.mySignData.status = 6;
                        ctrl.mySignData.timeOut = 60;
                    }
                }
            }, 1000)
        }



        ctrl.openTransferModal = function() {
            $scope.openAction = false;
            $scope.transferDoc = {
                docId: ctrl.viewDoc.id,
                name: "",
                email: "",
                national_id: "",
                phone: "",
                message: "",
                isLoading: true
            }
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract_sign/views/modal/transferDocument.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalDocInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openAddApprovalAssigneeModal = function() {
            $scope.openAction = false;
            $scope.addAssignee = {
                docId: ctrl.viewDoc.id,
                name: "",
                email: "",
                phone: "",
                message: "",
                isLoading: true
            }
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract_sign/views/modal/addApprovalAssignee.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalDocInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openDenyApprovalModal = function () {
            $scope.openAction = false;
            $scope.denyDoc = {
                docId: ctrl.viewDoc.id,
                reason: "",
                isLoading: true
            }
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract_sign/views/modal/denyDocument.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalDocInstanceCtrl,
                scope: $scope
            });
        }

    }

    function ModalDocInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, XuLyTaiLieu, $timeout) {
        $scope.onDenyDocument = function () {
            let data = $scope.denyDoc;
            let errorMess = "";
            if (data.reason == "") {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_REASON');
            }

            if (errorMess == "") {
                XuLyTaiLieu.denyDocument(data).then(function (response) {
                    if (response.data.success) {
                        $uibModalInstance.close(false);
                        $scope.initDocument(data.docId);
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }
        $scope.onSignOtp = function () {
            let data = $scope.otpData;
            let errorMess = "";
            if (data.otp == "") {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_OTP');
            }
            if (errorMess == "") {
                XuLyTaiLieu.signOtpDocument(data).then(function (response) {
                    if (response.data.success) {
                        $uibModalInstance.close(false);
                        $scope.initDocument(data.docId);
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $uibModalInstance.close(false);
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.onTransferDocument = function() {
            let data = $scope.transferDoc;
            let errorMess = "";
            if (data.name == "") {
                errorMess += $filter('translate')("DOCUMENT.ERR_EMPTY_NAME");
            }
            if (data.email == "") {
                errorMess += $filter('translate')("DOCUMENT.ERR_EMPTY_EMAIL");
            }
            else if (!Utils.validateEmail(data.email)) {
                errorMess += $filter('translate')("DOCUMENT.INVALID_EMAIL");
            }
            if (data.national_id == "") {
                errorMess += $filter('translate')("DOCUMENT.ERR_EMPTY_NATIONAL_ID");
            }
            else if(!Utils.validateCCCD(data.national_id)){
                errorMess += $filter('translate')("DOCUMENT.INVALID_NATIONAL_ID");
            }
            if (errorMess == "") {
                XuLyTaiLieu.transferDocument(data).then(function (response) {
                    if (response.data.success) {
                        $scope.updateTransferDocument();
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        $uibModalInstance.close(false);
                    } else {
                        NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    }
                }, function (response) {
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            } else {
                NotificationService.error(errorMess);
            }
        }

        $scope.onAddApprovalAssignee = function() {
            let data = $scope.addAssignee;
            let errorMess = "";
            if (data.name == "") {
                errorMess += $filter('translate')("DOCUMENT.ERR_EMPTY_NAME");
            }
            if (data.email == "") {
                errorMess += $filter('translate')("DOCUMENT.ERR_EMPTY_EMAIL");
            }
            else if (!Utils.validateEmail(data.email)) {
                errorMess += $filter('translate')("DOCUMENT.INVALID_EMAIL");
            }
            if (errorMess == "") {
                XuLyTaiLieu.addApprovalAssignee(data).then(function (response) {
                    if (response.data.success) {
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        $uibModalInstance.close(false);
                    } else {
                        NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    }
                }, function (response) {
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            } else {
                NotificationService.error(errorMess);
            }
        }

        $scope.updateSignatureManual = function(data){
            if(data.isEmpty()){
                NotificationService.error($filter('translate')("CONFIG.ACCOUNT.ERR_DRAW_SIGNATURE"));
            } else {
                let editSignature = angular.copy($scope.userSignature);
                editSignature.image_signature = data.toDataURL();
                editSignature.isLoading = true;
                XuLyTaiLieu.updateUserSignature(editSignature).then(function (response) {
                    if (response.data.success) {
                        $scope.updateSignature(data.toDataURL());
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        $uibModalInstance.close(false);
                    } else {
                        NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    }
                }, function (response) {
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            }
        }

        $scope.updateSignatureUpload = function(data){
            var file = angular.element(document.querySelector('input[type=file]')['files'][0]);
            if(file && file[0]){
                if(file[0].size > 1048576){
                    NotificationService.error($filter('translate')('CONFIG.ACCOUNT.ERR_OVERSIZE_UPLOAD'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                } else {
                    var reader = new window.FileReader();
                    reader.onload = function(){
                        var b64 = reader.result;
                        let editSignature = angular.copy($scope.userSignature);
                        if(editSignature.image_signature != b64){
                            editSignature.image_signature = b64;
                            editSignature.isLoading = true;
                            XuLyTaiLieu.updateUserSignature(editSignature).then(function (response) {
                                if (response.data.success) {

                                    $scope.updateSignature(b64);
                                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                                    $uibModalInstance.close(false);
                                } else {
                                    NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                                }
                            }, function (response) {
                                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                            });
                        } else {
                            NotificationService.success($filter('translate')("CONFIG.ACCOUNT.UPDATE_SUCCESSFULLY"));
                            $uibModalInstance.close(false);
                        }
                    }
                    reader.readAsDataURL(file[0]);
                }
            } else {
                NotificationService.success($filter('translate')("CONFIG.ACCOUNT.UPDATE_SUCCESSFULLY"));
                $uibModalInstance.close(false);
            }
        }

        $scope.captureAgain = function() {
            if ($scope.signEkycData.currentSignStep == 1) {
                $scope.signEkycData.dataFront = "";
            } else if ($scope.signEkycData.currentSignStep == 2) {
                $scope.signEkycData.dataBack = "";
            } else if ($scope.signEkycData.currentSignStep == 3) {
                $scope.signEkycData.dataFace = "";
            }
            $scope.signEkycData.isCapture = true;
        }

        $scope.nextStep = function() {
            let data = {
                docId: $scope.signEkycData.docId,
                isLoading: true,
                type: $scope.signEkycData.currentSignStep
            }
            switch ($scope.signEkycData.currentSignStep) {
                case 1:
                    data.image = $scope.signEkycData.dataFront;
                    break;
                case 2:
                    data.image = $scope.signEkycData.dataBack;
                    break;
                case 3:
                    data.image = $scope.signEkycData.dataFace;
                    break;
            }
            XuLyTaiLieu.verifyOcr(data).then(function (response) {
                if (response.data.success) {
                    let tmp = JSON.parse(response.data.data.data);
                    if (!response.data.data.status) {
                        NotificationService.error(tmp.message, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    } else {
                        $scope.signEkycData.currentSignStep++;
                        if ($scope.signEkycData.currentSignStep == 2) {
                            $scope.ekycResult.id = tmp.data.id;
                            $scope.ekycResult.name = tmp.data.name;
                            $scope.ekycResult.birthday = tmp.data.birthday;
                            $scope.ekycResult.sex = tmp.data.sex;
                            $scope.ekycResult.hometown = tmp.data.hometown;
                            $scope.ekycResult.address = tmp.data.address;

                            if ($scope.signEkycData.dataBack == "") {
                                $scope.signEkycData.isCapture = true;
                            } else {
                                $scope.signEkycData.isCapture = false;
                            }
                        } else if ($scope.signEkycData.currentSignStep == 3) {
                            $scope.ekycResult.issueDate = tmp.data.issueDate;
                            $scope.ekycResult.issueBy = tmp.data.issueBy;
                            if ($scope.signEkycData.dataFace == "") {
                                $scope.signEkycData.isCapture = true;
                            } else {
                                $scope.signEkycData.isCapture = false;
                            }
                        } else if ($scope.signEkycData.currentSignStep == 4) {
                            $scope.ekycResult.sim = tmp.data.sim;
                            $scope.signEkycData.isCapture = false;
                        } else {
                            $scope.signEkycData.isCapture = false;
                        }
                    }

                }
            }, function (response) {
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            });

        }

        $scope.prevStep = function() {
            $scope.signEkycData.currentSignStep--;
            $scope.signEkycData.isCapture = false;
        }

        $scope.onSignEkycDocument = function() {
            let data = $scope.ekycResult;
            let errorMess = "";
            if (data.id != $scope.signEkycData.cccd) {
                errorMess += $filter('translate')('DOCUMENT.ERR_NOT_MATCH_NATIONAL_ID');
            }
            if (errorMess == "") {
                XuLyTaiLieu.signKycDocument(data).then(function (response) {
                    if (response.data.success) {
                        $uibModalInstance.close(false);
                        $scope.initDocument(data.docId);
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $uibModalInstance.close(false);
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.choose = function(type) {
            $uibModalInstance.close(false);
            $scope.signRemoteSigning(type);
        }

        $scope.get_cts = function() {
            let data = $scope.mySignData;
            data.status = 7;
            let errorMess = "";
            if (!data.user_id) {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_MYSIGN_ID');
            }

            if (errorMess == "") {
                XuLyTaiLieu.getListCts(data).then(function (response) {
                    if (response.data.success) {
                        let dateRes = response.data.data.data;
                        data.status = 1;
                        $scope.listCts = JSON.parse(dateRes);
                        if($scope.listCts.length == 0){
                            data.status = 0;
                        }
                    }
                }, function (response) {
                    data.status = 0;
                    $scope.listCts.data = [];
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            } else {
                data.status = -1;
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }

        }

        $scope.onSignMySign = function(){
            let data = $scope.mySignData;
            console.log(data);
            let errorMess = "";
            if (!data.user_id) {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_MYSIGN_ID');
            }
            if(!data.credential_id) {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_CREDENTIAL_ID');
            }
            if (errorMess == "") {
                const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_SIGN'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    if (!$scope.isSelectSignature) {
                        NotificationService.error($filter('translate')('DOCUMENT.NOT_SELECT_SIGNATURE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        return;
                    }
                    data.status = 2;
                    $timeout(function() {
                        if(data.status == 2){
                            data.status = 3;
                            $scope.countDown();
                        }
                    },5000)
                    XuLyTaiLieu.signMySign(data).then(function (response) {
                        if (response.data.success) {
                            data.status = 4;
                            data.timeOut = 60;
                            data.credential_id = "";
                            $timeout(function() {
                                $uibModalInstance.close(false);
                                $scope.initDocument(data.docId);
                            },5000);
                        }
                    }, function (response) {
                        data.message = response.data.message;
                        data.status = 5;
                        data.timeOut = 60;
                        data.credential_id = "";
                    });
                }).catch(function (err) {
                    console.log(err);
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }

        }
        $scope.onRegisterCTS = function() {
            let registerICA = $scope.registerICA;
            let data = $scope.ekycResult;
            let errorMess = "";
            if (data.id != $scope.signEkycData.cccd) {
                errorMess += $filter('translate')('DOCUMENT.ERR_NOT_MATCH_NATIONAL_ID');
            }
            if (errorMess == "") {
                XuLyTaiLieu.registerCTS(data).then(function (response) {
                    if (response.data.success) {
                        $uibModalInstance.close(false);
                        registerICA = response.data.data.data;
                        $scope.signRemoteSigning('2',registerICA);
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $uibModalInstance.close(false);
                    registerICA = 0;
                    $scope.signRemoteSigning('2',registerICA);
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }
        $scope.onSignICA = function() {
            let errorMess = "";
            if (!$scope.registerICA) {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_MYSIGN_ID');
            }
            if (errorMess == "") {
                const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_SIGN'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    if (!$scope.loginUser.signature) {
                        NotificationService.error($filter('translate')('DOCUMENT.NOT_SELECT_SIGNATURE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        return;
                    }
                    XuLyTaiLieu.signICA({docId: $scope.docId, isLoading: true}).then(function (response) {
                        if (response.data.success) {
                            $timeout(function() {
                                $uibModalInstance.close(false);
                                $scope.initDocument(data.docId);
                            },5000);
                        }
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                }).catch(function (err) {
                    console.log(err);
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }

        }

        $scope.closeMySign = function() {
            let data = $scope.mySignData;
            if(data.status == 3){
                $uibModalInstance.close(false);
            }  else {
                $uibModalInstance.close(false);
                data.status = -1;
                data.user_id = "";
                data.credential_id = "";
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
