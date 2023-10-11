
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'CommerceSrvc', 'webcam']).controller("ViewDocumentCommerceCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$stateParams', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'XuLyTaiLieuThuongMai', 'WebcamService', ViewDocumentCommerceCtrl]);
    function ViewDocumentCommerceCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $stateParams, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, XuLyTaiLieuThuongMai, WebcamService) {

        var ctrl = this;

        ctrl.searchAddendumList = {
            document_state: "-1",
            dc_style: "-1",
            document_type_id: "-1",
            creator_id: "-1",
            parent_id: "-1",
            addendum_type: "-1",
            keyword: "",
            start_date: moment().startOf('month').format('DD/MM/YYYY'),
            end_date: moment().endOf('month').format('DD/MM/YYYY')
        }

        ctrl.isDetail = true;
        // khai báo chu ky ICA
        ctrl.registerICA = 0;

        $scope.initDocument = function(docId) {
            ctrl.searchAddendumList.parent_id = docId;

            XuLyTaiLieuThuongMai.initViewDoc({ docId: docId, isLoading: true }).then(function (response) {
                ctrl.viewDoc = response.data.data.document;

                ctrl.invalidDoc = response.data.data.invalid_doc;
                if (ctrl.invalidDoc) {
                    NotificationService.error($filter('translate')('DOCUMENT.INVALID_DOCUMENT'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                }
                ctrl.verify = response.data.data.verify_signature;
                if(!ctrl.verify) {
                    NotificationService.error($filter('translate')('DOCUMENT.VERIFY_SIGNATURE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                }
                ctrl.hadSignature = response.data.data.had_signature;
                ctrl.signatureData = response.data.data.signature_data;
                if (ctrl.hadSignature){
                    ctrl.signatureData.forEach(s => {
                        s.date = Utils.convertDateFromTimestamp(s.date);
                        s.validFrom = Utils.convertDateFromTimestamp(s.validFrom);
                        s.validTo = Utils.convertDateFromTimestamp(s.validTo);
                    })
                }
                ctrl.parentDoc = response.data.data.parent_doc;
                if(ctrl.viewDoc.parent_id != -1){
                    ctrl.isAddendum = true;
                }
                if(ctrl.viewDoc.issetHsm){
                    ctrl.registerICA = ctrl.viewDoc.issetHsm;
                }
                ctrl.companySignature = response.data.data.company;
                ctrl.rejectReason = response.data.data.reject_reason;
                ctrl.viewDoc.doc_expired_date = Utils.formatDateNoTime(new Date(ctrl.viewDoc.doc_expired_date));
                ctrl.viewDoc.sent_date = Utils.formatDateNoTime(new Date(ctrl.viewDoc.sent_date));
                ctrl.viewDoc.expired_date = Utils.formatDateNoTime(new Date(ctrl.viewDoc.expired_date));
                ctrl.viewDoc.created_at = Utils.formatDate(new Date(ctrl.viewDoc.created_at));
                if(!ctrl.scale){
                    ctrl.scale = "" + 1.5
                }
                let state = $scope.lstDocumentState.find(x => x.id == ctrl.viewDoc.document_state);
                ctrl.viewDoc.state_name = state.description;
                ctrl.viewDoc.send_doc_date = ctrl.viewDoc.send_doc_date == null ? "N/A" : Utils.formatDate(new Date(ctrl.viewDoc.send_doc_date));
                ctrl.viewDoc.finished_date = ctrl.viewDoc.finished_date == null ? $filter('translate')("DOCUMENT.NOT_COMPLETE") : Utils.formatDate(new Date(ctrl.viewDoc.finished_date));
                for (let i = 0; i < ctrl.viewDoc.partners.length; i++) {
                    let partner = ctrl.viewDoc.partners[i];
                    if (partner.organisation_type == 3) {
                        partner.displayName = `[${i+1}] ` + $filter('translate')("DOCUMENT.PERSONAL");
                    } else {
                        partner.displayName = `[${i+1}] ${partner.company_name} - ${partner.tax}`;
                    }
                    partner.assignees.forEach(assignee => {
                        assignee.assignType = ($scope.lstAssigneeRole.find(x => x.id == assignee.assign_type)).description;
                        assignee.sent_email = "";
                        assignee.assign_date = assignee.submit_time == null ? "" : Utils.formatDate(new Date(assignee.submit_time));;
                    })
                }
                if(ctrl.viewDoc.expired_type == 0){
                    ctrl.viewDoc.addendum_expire = 'Vô thời hạn';
                } else if(ctrl.viewDoc.expired_type == 1){
                    ctrl.viewDoc.addendum_expire = $filter('translate')('DOCUMENT.RENEW_EXPIRED_TIME', { 'time' : ctrl.viewDoc.doc_expired_date } );
                } else if(ctrl.viewDoc.expired_type == 2){
                    ctrl.viewDoc.addendum_expire = $filter('translate')('DOCUMENT.RENEW_EXPIRED_MONTH', { 'month' : ctrl.viewDoc.expired_month } );
                }
                if (ctrl.viewDoc.cur) {
                    ctrl.viewDoc.hasApprovalRole = ctrl.viewDoc.cur.email == $scope.loginUser.email && ctrl.viewDoc.cur.assign_type == 1 && !ctrl.invalidDoc && ctrl.verify;
                    ctrl.viewDoc.hasSignRole = ctrl.viewDoc.cur.email == $scope.loginUser.email && ctrl.viewDoc.cur.assign_type == 2 && !ctrl.invalidDoc && ctrl.verify;
                    ctrl.viewDoc.hasDenyRole = ctrl.viewDoc.cur.email == $scope.loginUser.email;
                    if (ctrl.viewDoc.cur.assign_type == 2) {
                        ctrl.viewDoc.cur.sign_method = ctrl.viewDoc.cur.sign_method ?? "";
                        ctrl.viewDoc.lstSigningMethod = ctrl.viewDoc.cur.sign_method.split(',');
                        ctrl.viewDoc.lstSigningMethod.forEach(method => {
                            if (method == '0') {
                                ctrl.viewDoc.canSignToken = true;
                            } else if (method == '1') {
                                ctrl.viewDoc.canSignOtp = true;
                            } else if (method == '2') {
                                ctrl.viewDoc.canSignKyc = true;
                            } else if (method == '3') {
                                ctrl.viewDoc.canSignRemote = true;
                            }
                        });

                    }
                    ctrl.signatureUrl = 'vcontract/assets/images/signature-icon.svg';
                    if (ctrl.viewDoc.cur.signature) {
                        ctrl.signatureUrl = ctrl.viewDoc.cur.signature.image_signature;
                    }
                }
                ctrl.lstPosition = [];
                ctrl.viewDoc.hasEditRole = ctrl.viewDoc.document_state == 4 && !ctrl.viewDoc.document_sample_id && ctrl.viewDoc.creator_email == $scope.loginUser.email;
                ctrl.getSignDocument(docId, ctrl.scale);
            }, function (response) {
                $scope.initBadRequest(response);
                $scope.goBack();
            })
        }

        $scope.updateSignature = function(signature) {
            ctrl.signatureUrl = signature;
        }

        ctrl.showRejectReason = function(){
            let reason = ctrl.rejectReason;
            var re = /\n/gi
            var rejectReason = reason.replace(re, "<br>")
            return NotificationService.alert(rejectReason, "Lý do từ chối");
        }

        ctrl.openSelectSignature = function () {
            $scope.selectSignature = {
                personal_signature: $scope.loginUser.signature ? angular.copy($scope.loginUser.signature) : Utils.defaultUploadImage(),
                has_personal_signature: $scope.loginUser.signature ? true : false,
                assignee: ctrl.viewDoc.cur.id,
                docId: ctrl.viewDoc.id
            }
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/selectSignature.html',
                windowClass: "fade show modal-blur",
                size: 'md modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalDocInstanceCtrl,
                scope: $scope
            });
        }
        ctrl.showLstSignatureDocument = function () {
            $scope.signatureData = ctrl.signatureData;
            $scope.showDetailSignatureDocument = ctrl.showDetailSignatureDocument;
            $scope.showLstSignatureDocument = ctrl.showLstSignatureDocument;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/lstSignatureDocument.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalDocInstanceCtrl,
                scope: $scope
            });
        }
        ctrl.showDetailSignatureDocument = function (i) {
            $scope.data = ctrl.signatureData[i];
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/detailSignatureDocument.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalDocInstanceCtrl,
                scope: $scope
            });
        }


        ctrl.changeView = function () {
            ctrl.isDetail = !ctrl.isDetail;
        }

        ctrl.openDetailParentDocument = function (id) {
            $state.go("index.commerce.viewDocument", {docId: id});
        }

        ctrl.lstAddendumType = [
			{ 'id': '0', 'description': 'INTERNAL.DOCUMENT_LIST.TYPE.SUPPLEMENT' },
			{ 'id': '1', 'description': 'INTERNAL.DOCUMENT_LIST.TYPE.RENEW' },
			{ 'id': '2', 'description': 'INTERNAL.DOCUMENT_LIST.TYPE.DENY' },
        ]


        ctrl.init = function () {
            let id = $stateParams.docId;
            $scope.initDocument(id);
        }();

        ctrl.dtInstance = {}
        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="viewDocumentCtrl.selectAll" ng-change="viewDocumentCtrl.toggleAll(viewDocumentCtrl.selectAll, viewDocumentCtrl.selected)">';
        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                // setTimeout(() => { $scope.onSearchAddendumList() }, 300);
                // return;
            }
            ctrl.searchAddendumList.startDate = Utils.parseDate(ctrl.searchAddendumList.start_date);
            ctrl.searchAddendumList.endDate = Utils.parseDateEnd(ctrl.searchAddendumList.end_date);
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
                searchData: ctrl.searchAddendumList,
                isLoading: true
            }
            XuLyTaiLieuThuongMai.searchAddendum(params).then(function (response) {
                ctrl.lstAddendumList = response.data.data.data;
                ctrl.lstAddendumList.forEach(e => {
                    e.sent_date = moment(e.sent_date).format("DD/MM/YYYY");
                    e.expired_date = moment(e.expired_date).format("DD/MM/YYYY");
                    e.finished_date = e.finished_date != null ? moment(e.finished_date).format("DD/MM/YYYY") : '';
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

        ctrl.dtOptions = DTOptionsBuilder.newOptions()
                .withPaginationType('full_numbers')
                .withDisplayLength(20)
                .withOption('order', [[2, 'desc']])
                .withOption('searching', false)
                .withLanguage(Utils.getDataTableLanguage($scope.loginUser.language))
                .withDOM(Utils.getDataTableDom())
                .withOption('lengthMenu', [20, 50, 100])
                .withOption('responsive', true)
                .withOption('processing', true)
                .withOption('serverSide', true)
                .withOption('paging', true)
                .withFnServerData(getData)
                .withOption('createdRow', function (row, data, dataIndex) {
                    $compile(angular.element(row).contents())($scope);
                })
                .withOption('headerCallback', function (header) {
                    ctrl.selectAll = false;
                    $compile(angular.element(header).contents())($scope);
                })
            ctrl.dtColumns = [
                DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }),
                DTColumnBuilder.newColumn(null).withTitle(titleHtml).notSortable().renderWith(function (data, type, full, meta) {
                    // ctrl.selected[full.id] = false;
                    return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="viewDocumentCtrl.selected[' + data.id + ']" ng-change="viewDocumentCtrl.toggleOne(viewDocumentCtrl.selected)"/>';
                }),
                DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.ADDENDUM_LIST_SENT_DATE')),
                DTColumnBuilder.newColumn('code').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.ADDENDUM_LIST_CODE')),
                DTColumnBuilder.newColumn('name').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.ADDENDUM_LIST_NAME')),
                DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.ADDENDUM_TYPE')).notSortable().renderWith(function (data, type, full, meta) {
                    let html = '';
                    ctrl.lstAddendumType.forEach(lst => {
                        if(lst.id == data.addendum_type){
                            html = $filter('translate')(lst.description);
                        }
                    });
                    return html;
                }),
                DTColumnBuilder.newColumn('expired_date').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_EXPIRED_TIME')),
                DTColumnBuilder.newColumn('finished_date').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_IS_COMPLETED')),
                DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_DOC_STATE')).renderWith(function(data, type, full, meta){
                    return Utils.getDataTableDocumentStateColumn(data);
                }),
                DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                    // if (ctrl.permission.is_write != 1) {
                    //     return "";
                    // }
                    let state = ctrl.lstAddendumList[meta.row].document_state;
                    let is_verify = ctrl.lstAddendumList[meta.row].is_verify_content == 1;
                    return Utils.renderDataTableAction(meta, "viewDocumentCtrl.openEditAddendumListModal", false, false, false, false, false, "viewDocumentCtrl.onDownloadAddendumList", "viewDocumentCtrl.onHistoryAddendumList", false, false, state >= 7 && is_verify ? "viewDocumentCtrl.onHistoryTransactionAddendumList": false);
                }),
            ];

        $scope.onSearchAddendumList = function () {
            ctrl.dtInstance.rerender();
        }

        ctrl.createAddendum = function () {
            $state.go("supplementCommerce", {type: 0,parentId: ctrl.viewDoc.id , docId: ''});
        };


        ctrl.onEditDenyDocument = function() {
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_EDIT'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                XuLyTaiLieuThuongMai.editDocument({ docId: ctrl.viewDoc.id, isLoading: true }).then(function (response) {
                    if (response.data.success) {
                        if (ctrl.viewDoc.parent_id != -1) {
                            $state.go("editAddendumCommerce", { parentId: ctrl.viewDoc.parent_id, docId: ctrl.viewDoc.id });
                        }
                        else {
                            $state.go("editCommerce", { docId: ctrl.viewDoc.id });
                        }
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
                console.log(err);
            });
        }

        ctrl.downloadDocument = function() {
            let fileName = Utils.removeVietnameseTones(ctrl.viewDoc.name) + ".pdf";
            Utils.downloadFormBinary(ctrl.docFile, fileName);
        }

        ctrl.openEditAddendumListModal = function (row) {
            let document = ctrl.lstAddendumList[row];
            if (document.document_state == 1) {
                $state.go("editAddendumCommerce", {parentId: ctrl.viewDoc.id , docId: document.id});
            } else {
                $state.go("index.commerce.viewDocument", {docId: document.id});
            }
        }

        ctrl.onDownloadAddendumList = function(row) {
            let doc = ctrl.lstAddendumList[row];
            let formData = new FormData();
            formData.append("id", doc.id);
            XuLyTaiLieuThuongMai.getSignDocument(formData).then(function (response) {
                let fileName = Utils.removeVietnameseTones(doc.name) + ".pdf";
                Utils.downloadFormBinary(response, fileName);
            }, function (response) {
                $scope.initBadRequest(response);
            })
        }

        ctrl.onHistoryAddendumList = function(row) {
            let docId = ctrl.lstAddendumList[row].id;
            $scope.showDocumentHistory(docId);
        }



        ctrl.printDocument = function() {
            let blobUrl = Utils.blobUrlFromBinary(ctrl.docFile);
            printJS(blobUrl);
        }

        ctrl.getSignDocument = function (docId, scale) {
            if (docId) {
                var formData = new FormData();
                formData.append("id", docId);
                XuLyTaiLieuThuongMai.getSignDocument(formData).then(function (response) {
                    let oldCanv = document.getElementsByClassName('viewer-canvas-container');
                    while(oldCanv.length > 0){
                        oldCanv[0].parentNode.removeChild(oldCanv[0]);
                    }
                    ctrl.hasChangeSignature = false;
                    ctrl.docFile = response;
                    signature.init(response, scale, true, ctrl.viewDoc.signature_location, ctrl.signatureUrl);
                    $scope.$digest();
                },function (response) {
                    $scope.initBadRequest(response);
                })
            } else {
            }
        }

        $scope.onChangeScale = function(){
            ctrl.lstPosition = [];
            let id = $stateParams.docId;
            ctrl.getSignDocument(id, ctrl.scale);
        }

        $scope.onDrawingViewFiles = function (canvas, p, type) {
            signature.currentCanvas = canvas;
            let scale = ctrl.scale / 1.5;
            let revertScale = 1.5 / ctrl.scale;
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
            if (target.type === "signature") {
                ctrl.hasChangeSignature = true;
                let sig = ctrl.lstPosition.find(x => x.id === target.id);
                if (sig) {
                    sig.XAxis = target.left * scale;
                    sig.YAxis = target.top * scale;
                    sig.Width = target.scaleX !== null ? target.width * target.scaleX : target.width;
                    sig.Height = target.scaleY !== null ? target.height * target.scaleY : target.height;
                }
            }

            $scope.$digest();
        }

        ctrl.onSaveLocation = function() {
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_SAVE_LOCATION'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                XuLyTaiLieuThuongMai.saveSignatureLocation({assigneeId: ctrl.viewDoc.cur.id, 'location': ctrl.lstPosition, isLoading: true}).then(function (response) {
                    if (response.data.success) {
                        ctrl.hasChangeSignature = false;
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
                console.log(err);
            });
        }

        ctrl.confirmApproval = function() {
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_APPROVAL'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                XuLyTaiLieuThuongMai.approveDocument({ docId: ctrl.viewDoc.id, isLoading: true }).then(function (response) {
                    if (response.data.success) {
                        $scope.initDocument(ctrl.viewDoc.id)
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
                console.log(err);
            });
        }

        ctrl.confirmSign = function() {
            //TODO: kết nối với plugin
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_SIGN'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                if (!$scope.loginUser.signature) {
                    NotificationService.error($filter('translate')('DOCUMENT.NOT_SELECT_SIGNATURE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    return;
                }
                ctrl.connectPlugin();
                // XuLyTaiLieuThuongMai.signDocument({ docId: ctrl.viewDoc.id, isLoading: true }).then(function (response) {
                //     if (response.data.success) {
                //         $scope.initDocument(ctrl.viewDoc.id)
                //         NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                //     }
                // }, function (response) {
                //     NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                // });
            }).catch(function (err) {
                console.log(err);
            });
        }

        ctrl.confirmPreOverLapse = function () {
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_PRE_OVERLAPSE'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                $state.go("supplementInternal", {type: 2,parentId: ctrl.viewDoc.id , docId: ''});
            }).catch(function (err) {
                console.log(err);
            });
        }

        ctrl.confirmOverLapse = function () {
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT.CONFIRM_OVERLAPSE'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                $state.go("supplementCommerce", {type: 2,parentId: ctrl.viewDoc.id , docId: ''});
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
            XuLyTaiLieuThuongMai.getHashDoc({docId: ctrl.viewDoc.id, pubKey: certificate.publicKey, isLoading: true}).then(function (response) {
                if (response.data.success) {
                    let res = JSON.parse(response.data.data.data);
                    let url = Utils.getPluginUrl() + "api/sign/hash?serialNumber=" + certificate.serialNumber;
                    $http({method:'POST',url:url, data: [res.data]}).then(function(response) {
                        if (response) {
                            let hashed = response.data[0];
                            XuLyTaiLieuThuongMai.signDocument({docId: ctrl.viewDoc.id, pubca: certificate.publicKey, ca: hashed, isLoading: true}).then(function (response) {
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
                $scope.initBadRequest(response);
            });
        }

        ctrl.confirmSignOtp = function () {
            if (!$scope.loginUser.signature) {
                NotificationService.error($filter('translate')('DOCUMENT.NOT_SELECT_SIGNATURE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                return;
            }
            XuLyTaiLieuThuongMai.sendOtp({docId: ctrl.viewDoc.id, isLoading: true}).then(function (response) {
                if (response.data.success) {
                    $scope.otpData = {
                        "otp": "",
                        "docId": ctrl.viewDoc.id,
                        "isLoading": true
                    }
                    $uibModal.open({
                        animation: true,
                        templateUrl: 'vcontract/views/modal/sendOtpSignDocument.html',
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
                $scope.initBadRequest(response);
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
                templateUrl: 'vcontract/views/modal/signEkycDocument.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalDocInstanceCtrl,
                scope: $scope
            });
        }
        // khai báo mysign
        ctrl.mySignData = {
            "docId": "",
            "user_id": "",
            "credential_id": "",
            "status": -1,
            "message": "",
            "timeOut": 60
        }

        ctrl.openChooseSignRemote = function() {
            $scope.signRemoteSigning = ctrl.signRemoteSigning;
            $scope.mySignData = ctrl.mySignData;
            $scope.mySignData.docId = ctrl.viewDoc.id;
            $scope.re = ctrl.registerICA;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/chooseRemoteSign.html',
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

        ctrl.openDenyApprovalModal = function() {
            $scope.denyDoc = {
                docId: ctrl.viewDoc.id,
                reason: "",
                isLoading: true
            }
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/denyDocument.html',
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

    function ModalDocInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, XuLyTaiLieuThuongMai, $timeout) {
        $scope.onDenyDocument = function () {
            let data = $scope.denyDoc;
            let errorMess = "";
            if (data.reason == "") {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_REASON');
            }
            if (errorMess == "") {
                XuLyTaiLieuThuongMai.denyDocument(data).then(function (response) {
                    if (response.data.success) {
                        $uibModalInstance.close(false);
                        $scope.initDocument(data.docId);
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
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
            XuLyTaiLieuThuongMai.signOtpDocument(data).then(function (response) {
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
            XuLyTaiLieuThuongMai.verifyOcr(data).then(function (response) {
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
                XuLyTaiLieuThuongMai.signKycDocument(data).then(function (response) {
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
            $scope.signRemoteSigning(type, $scope.re);
        }

        $scope.get_cts = function() {
            let data = $scope.mySignData;
            data.status = 7;
            let errorMess = "";
            if (!data.user_id) {
                errorMess += $filter('translate')('DOCUMENT.ERR_EMPTY_MYSIGN_ID');
            }
            if (errorMess == "") {
                XuLyTaiLieuThuongMai.getListCts(data).then(function (response) {
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
                    if (!$scope.loginUser.signature) {
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
                    XuLyTaiLieuThuongMai.signMySign(data).then(function (response) {
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

        $scope.viewDetail = function (i) {
            $uibModalInstance.close(false);
            setTimeout(function () {
                $scope.showDetailSignatureDocument(i);
            },500)
        }
        $scope.closeViewDetailSignature = function() {
            $uibModalInstance.close(false);
            setTimeout(function () {
                $scope.showLstSignatureDocument();
            },500)
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

        $scope.onRegisterCTS = function() {
            let registerICA = $scope.registerICA;
            let data = $scope.ekycResult;
            let errorMess = "";
            if (data.id != $scope.signEkycData.cccd) {
                errorMess += $filter('translate')('DOCUMENT.ERR_NOT_MATCH_NATIONAL_ID');
            }
            if (errorMess == "") {
                XuLyTaiLieuThuongMai.registerCTS(data).then(function (response) {
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
                    XuLyTaiLieuThuongMai.signICA({docId: $scope.docId, isLoading: true}).then(function (response) {
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

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
