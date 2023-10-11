// const { filter } = require("lodash");

(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'webcam']).controller("SignManageCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'KySoTaiLieu', 'XuLyTaiLieu', 'WebcamService', SignManageCtrl]);
    function SignManageCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, KySoTaiLieu, XuLyTaiLieu, WebcamService) {

        var ctrl = this;

        ctrl.searchSignManage = {
            document_type_id: "-1",
            sign_method: "-1",
            addendum_type: "-1",
            dc_style: "-1",
            consignee_type_id: "-1",
            creator_type_id: "-1",
            parent_id: "-1",
            keyword: "",
            start_date: moment().startOf('month').format('DD/MM/YYYY'),
            end_date: moment().endOf('month').format('DD/MM/YYYY')
        }

        ctrl.selectAll = false;
        ctrl.selected = {};
        ctrl.toggleAll = function (selectAll, selectedItems) {
            for (var id in selectedItems) {
                if (selectedItems.hasOwnProperty(id)) {
                    selectedItems[id] = selectAll;
                }
            }
        }
        ctrl.toggleOne = function (selectedItems) {
            for (var id in selectedItems) {
                if (selectedItems.hasOwnProperty(id)) {
                    if (!selectedItems[id]) {
                        ctrl.selectAll = false;
                        return;
                    }
                }
            }
            ctrl.selectAll = true;
        }

        ctrl.lstAddendumType = [
			{ 'id': '0', 'description': 'INTERNAL.DOCUMENT_LIST.TYPE.SUPPLEMENT' },
			{ 'id': '1', 'description': 'INTERNAL.DOCUMENT_LIST.TYPE.RENEW' },
			{ 'id': '2', 'description': 'INTERNAL.DOCUMENT_LIST.TYPE.DENY' },
        ]

        ctrl.init = function() {
            KySoTaiLieu.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.lstDocumentType = response.data.data.lstDocumentType;
                ctrl.cur = response.data.data.cur;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();


        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="signManageCtrl.selectAll" ng-change="signManageCtrl.toggleAll(signManageCtrl.selectAll, signManageCtrl.selected)">';
        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchSignManage() }, 300);
                return;
            }
            ctrl.selected = {};
            ctrl.searchSignManage.startDate = Utils.parseDate(ctrl.searchSignManage.start_date);
            ctrl.searchSignManage.endDate = Utils.parseDateEnd(ctrl.searchSignManage.end_date);
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
                searchData: ctrl.searchSignManage,
                isLoading: true
            }
            KySoTaiLieu.search(params).then(function (response) {
                ctrl.lstSignManage = response.data.data.data;
                ctrl.lstSignManage.forEach(e => {
                    e.sent_date = moment(e.sent_date).format("DD/MM/YYYY");
                });
                if(ctrl.searchSignManage.consignee_type_id != 0){
                    ctrl.searchSignManage.creator_type_id = "-1";
                }
                fnCallback(response.data.data);
            }, function (response) {
                var records = {
                    'draw': draw,
                    'recordsTotal': 0,
                    'recordsFiltered': 0,
                    'data': []
                };
                fnCallback(records);
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            })
        }

        function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // $compile(nRow)($scope);
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
                ctrl.selected[full.id] = false;
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="signManageCtrl.selected[' + data.id + ']" ng-change="signManageCtrl.toggleOne(signManageCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_MANAGE_SENT_DATE')),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_MANAGE_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_MANAGE_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),

            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.DEFAULT')).notSortable().renderWith(function (data, type, full, meta) {
                return $filter('translate')(Utils.getDocumentStyle(data.dc_style));
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_MANAGE_METHOD')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                var sign_method = [];
                var html = '';
                if (data.myOrganisation){
                    data.myOrganisation.forEach(e => {
                       let a = e.sign_method.split(',');
                       a.forEach(o => {
                            if ( !sign_method.includes(o) ){
                                sign_method.push(o);
                            }
                       });
                    })
                }
                if (data.partner){
                    data.partner.forEach(e => {
                        let a = e.sign_method.split(',');
                        a.forEach(o => {
                             if ( !sign_method.includes(o) ){
                                sign_method.push(o);
                             }
                        });
                    })
                }
                html += Utils.getSignMethod(sign_method);
                return html;
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_MANAGE_MY_ORGANIZATION')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                var html = '';
                data.myOrganisation.forEach(e => {
                    if (e.state == 2) {
                        html += '<a class="btn btn-teal rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fas fa-pen-alt"></i></a>';
                    } else {
                        html += '<a class="btn btn-gray rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fas fa-pen-alt"></i></a>';
                    }
                })
                return html;
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_MANAGE_PARTNER')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                var html = '';
                data.partner.forEach(e => {
                    if (e.state == 2) {
                        html += '<a class="btn btn-teal rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fas fa-pen-alt"></i></a>';
                    } else {
                        html += '<a class="btn btn-gray rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fas fa-pen-alt"></i></a>';
                    }
                })
                return html;
            }),
            DTColumnBuilder.newColumn('creator_name').withTitle($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_MANAGE_CREATOR')),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "signManageCtrl.openDetailSignManageModal", false, false, "signManageCtrl.openSignManageModal" , false, false, "signManageCtrl.onDownloadDocument", false, false, false);
                // return Utils.renderDataTableAction(meta, "signManageCtrl.openDetailSignManageModal", false, false, ["signManageCtrl.signDocument", "signManageCtrl.signOtpDocument","signManageCtrl.signKycDocument"] , false, false, "signManageCtrl.onDownloadDocument", false, false, false);
            }),
        ];

        ctrl.dtColumnsAddendum = [
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn(null).withTitle(titleHtml).notSortable().renderWith(function (data, type, full, meta) {
                ctrl.selected[full.id] = false;
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="signManageCtrl.selected[' + data.id + ']" ng-change="signManageCtrl.toggleOne(signManageCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_SENT_DATE')),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_PARENT_NAME')).notSortable().renderWith(function (data, type, full, meta) {
                var html = `<a role="button" class='text-primary' ng-click='signManageCtrl.openDetailParentDocument(${data.parent_doc.id})'>${data.parent_doc.name}</a> `;
                return html;
            }),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_CODE')),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_NAME')),

            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.ADDENDUM_TYPE')).notSortable().renderWith(function (data, type, full, meta) {
                let html = '';
                ctrl.lstAddendumType.forEach(lst => {
                    if(lst.id == data.addendum_type){
                        html = $filter('translate')(lst.description);
                    }
                });
                return html;
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_METHOD')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                var sign_method = [];
                var html = '';
                if (data.myOrganisation){
                    data.myOrganisation.forEach(e => {
                       let a = e.sign_method.split(',');
                       a.forEach(o => {
                            if ( !sign_method.includes(o) ){
                                sign_method.push(o);
                            }
                       });
                    })
                }
                if (data.partner){
                    data.partner.forEach(e => {
                        let a = e.sign_method.split(',');
                        a.forEach(o => {
                             if ( !sign_method.includes(o) ){
                                sign_method.push(o);
                             }
                        });
                    })
                }
                html += Utils.getSignMethod(sign_method);
                return html;
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_MY_ORGANIZATION')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                var html = '';
                data.myOrganisation.forEach(e => {
                    if (e.state == 2) {
                        html += '<a class="btn btn-teal rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fas fa-pen-alt"></i></a>';
                    } else {
                        html += '<a class="btn btn-gray rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fas fa-pen-alt"></i></a>';
                    }
                })
                return html;
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_PARTNER')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                var html = '';
                data.partner.forEach(e => {
                    if (e.state == 2) {
                        html += '<a class="btn btn-teal rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fas fa-pen-alt"></i></a>';
                    } else {
                        html += '<a class="btn btn-gray rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fas fa-pen-alt"></i></a>';
                    }
                })
                return html;
            }),
            DTColumnBuilder.newColumn('creator_name').withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_CREATOR')),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "signManageCtrl.openDetailSignManageModal", false, false, "signManageCtrl.openSignManageModal" , false, false, "signManageCtrl.onDownloadDocument", false, false, false);
                // return Utils.renderDataTableAction(meta, "signManageCtrl.openDetailSignManageModal", false, false, ["signManageCtrl.signDocument", "signManageCtrl.signOtpDocument","signManageCtrl.signKycDocument"] , false, false, "signManageCtrl.onDownloadDocument", false, false, false);
            }),
        ];

        $scope.onSearchSignManage = function () {
            ctrl.dtInstance.rerender();
        }

        // ctrl.openCreateAddendumModal = function () {

        // }

        ctrl.openDetailSignManageModal = function (row) {
            let document = ctrl.lstSignManage[row];
            $state.go("index.internal.viewDocument", {docId: document.id});
        }

        ctrl.openDetailParentDocument = function (id) {
            $state.go("index.internal.viewDocument", {docId: id});
        }

        ctrl.openSignManageModal = function(row) {
            $scope.document = angular.copy(ctrl.lstSignManage[row]);
            let document = ctrl.lstSignManage[row];
            var sign_method = [];
            if (document.myOrganisation){
                document.myOrganisation.forEach(e => {
                   let a = e.sign_method.split(',');
                   a.forEach(o => {
                        if ( !sign_method.includes(o) ){
                            sign_method.push(o);
                        }
                   });
                })
            }
            if (document.partner){
                document.partner.forEach(e => {
                    let a = e.sign_method.split(',');
                    a.forEach(o => {
                         if ( !sign_method.includes(o) ){
                            sign_method.push(o);
                         }
                    });
                })
            }

            $scope.lstSignMethod = sign_method;
            $scope.lstSignMethod.approveMethod = 0;
            $scope.lstSignMethod.forEach(method => {
                if (method == "0") {
                    $scope.lstSignMethod.canSignToken = true;
                    $scope.lstSignMethod.approveMethod++;
                } else if (method == "1") {
                    $scope.lstSignMethod.canSignOtp = true;
                    $scope.lstSignMethod.approveMethod++;
                } else if (method == "2") {
                    $scope.lstSignMethod.canSignKyc = true;
                    $scope.lstSignMethod.approveMethod++;
                }
            });
            if ($scope.lstSignMethod.approveMethod == 1) {
                if ($scope.lstSignMethod.canSignToken){
                    ctrl.confirmSign(document);
                }
                if ($scope.lstSignMethod.canSignOtp){
                    ctrl.confirmSignOtp(document);
                }
                if ($scope.lstSignMethod.canSignKyc){
                    ctrl.confirmSignKyc(document);
                }
            }else{
                $uibModal.open({
                    animation: true,
                    templateUrl: 'vcontract/views/modal/chooseSignMethod.html',
                    windowClass: "fade show modal-blur",
                    size: 'sm modal-dialog-centered',
                    backdrop: 'static',
                    backdropClass: 'show',
                    keyboard: false,
                    controller: ModalInstanceCtrl,
                    scope: $scope
                });
            }

        }


        function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, XuLyTaiLieu, KySoTaiLieu) {
            var document = $scope.document ;
            let lstSignManage = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstSignManage.push(id);
                    }
                }
            }
            let cccd = [];
            let lstdocument = ctrl.lstSignManage
            lstSignManage.forEach(id=>{
                lstdocument.find(x => x.id == id).myOrganisation.forEach(e=>{
                    let a = e.national_id
                    cccd.push(a);
                })
            })
            $scope.cancel = function () {
                $uibModalInstance.close(false);
            };
            $scope.confirmSign = function () {
                ctrl.confirmSign(document);
                $scope.cancel();
            }
            $scope.confirmSignOtp = function () {
                ctrl.confirmSignOtp(document);
                $scope.cancel();
            }
            $scope.confirmSignKyc = function () {
                ctrl.confirmSignKyc(document);
                $scope.cancel();
            }
            $scope.confirmSignMulti = function () {
                ctrl.confirmMultiSign(lstSignManage);
                $scope.cancel();
            }
            $scope.confirmSignMultiOtp = function () {
                ctrl.confirmMultiSignOtp();
            }
            $scope.confirmSignMultiKyc = function () {
                ctrl.confirmMultiSignKyc(lstSignManage, cccd);
                $scope.cancel();
            }
        }

        ctrl.confirmSign = function (document) {
            const confirm = NotificationService.confirm($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_DIGITAL_SIGN_CONFIRM', {'documentName': document.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    KySoTaiLieu.signDocument({ docId: document.id, isLoading: true }).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchSignManage();
                            NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        }
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }).catch(function (err) {
                    console.log(err);
                });
        }

        ctrl.confirmSignOtp = function (document) {
            const confirm = NotificationService.confirm($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_OTP_SIGN_CONFIRM', {'documentName': document.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    XuLyTaiLieu.sendOtp({docId: document.id, isLoading: true}).then(function (response) {
                        if (response.data.success) {
                            $scope.otpData = {
                                "otp": "",
                                "docId": document.id,
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
                            $scope.onSearchSignManage();
                            NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        }
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }).catch(function (err) {
                    console.log(err);
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
        $scope.webcam.error = function(error) {

        };

        ctrl.confirmSignKyc = function (document) {
            const confirm = NotificationService.confirm($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_EKYC_SIGN_CONFIRM', {'documentName': document.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    $scope.signEkycData = {
                        currentSignStep: 1,
                        isCapture: true,
                        dataFront: "",
                        dataBack: "",
                        dataFace: "",
                        fotoContentType: "",
                        docId: document.id,
                        cccd: document.myOrganisation.national_id
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
                        docId: document.id,
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
                })
        }

        ctrl.onDownloadDocument = function(row){
            let doc = ctrl.lstSignManage[row];
            let formData = new FormData();
            formData.append("id", doc.id);
            XuLyTaiLieu.getSignDocument(formData).then(function (response) {
                let fileName = Utils.removeVietnameseTones(doc.name) + ".pdf";
                Utils.downloadFormBinary(response, fileName);
            }, function (response) {
                $scope.initBadRequest(response);
            })
        }

        ctrl.onSignMultiDocument = function () {
            let lstSignManage = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstSignManage.push(id);
                    }
                }
            }
            //lấy method ký mà tất cả các tài liệu có chung
            let getLstSignMethod = [];
            let cccd = [];
            let lstdocument = ctrl.lstSignManage
            lstSignManage.forEach(id=>{
                lstdocument.find(x => x.id == id).myOrganisation.forEach(e=>{
                    let a = e.national_id
                    cccd.push(a);
                })
            })
            lstSignManage.forEach(id=>{
                lstdocument.find(x => x.id == id).myOrganisation.forEach(e=>{
                    let a = e.sign_method.split(',')
                    getLstSignMethod.push(a);
                })

            })
            var canSign = true;
            let i=0;
            for(let k = i+1; k<getLstSignMethod.length; k++){
                var signMethod = Utils.removeItemInArray(getLstSignMethod[i].filter(element => getLstSignMethod[k].includes(element)) ,'')
                if(signMethod.length == 0){
                    NotificationService.error($filter('translate')("INTERNAL.SIGN_MANAGE.SIGN_METHOD_ERROR"));
                    return canSign = false;
                }
            }
            if (lstSignManage.length == 0) {
                NotificationService.error($filter('translate')('INTERNAL.SIGN_MANAGE.ERR_NEED_CHOOSE_SIGN_MANAGE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
            else if(canSign == true || lstSignManage.length == 1) {
                if(getLstSignMethod.length > 1){
                    $scope.lstSignMethod = signMethod
                }
                else{
                    $scope.lstSignMethod = getLstSignMethod[0]
                }
                $scope.lstSignMethod.approveMethod = 0;
                $scope.lstSignMethod.forEach(method => {
                    if (method == "0") {
                        $scope.lstSignMethod.canSignToken = true;
                        $scope.lstSignMethod.approveMethod++;
                    }else if (method == "1") {
                         $scope.lstSignMethod.canSignOtp = true;
                         $scope.lstSignMethod.approveMethod++;
                    }else if (method == "2") {
                        $scope.lstSignMethod.canSignKyc = true;
                        $scope.lstSignMethod.approveMethod++;
                    }

                });
                if ($scope.lstSignMethod.approveMethod == 1) {
                    if ($scope.lstSignMethod.canSignToken){
                        ctrl.confirmMultiSign(lstSignManage);
                    }
                    if($scope.lstSignMethod.canSignOtp){
                        ctrl.confirmMultiSignOtp();
                    }
                    if ($scope.lstSignMethod.canSignKyc){
                        ctrl.confirmMultiSignKyc(lstSignManage, cccd);
                    }

                }
                else{
                    $scope.onSignMulti = true;
                    $uibModal.open({
                        animation: true,
                        templateUrl: 'vcontract/views/modal/chooseMultiSignMethod.html',
                        windowClass: "fade show modal-blur",
                        size: 'sm modal-dialog-centered',
                        backdrop: 'static',
                        backdropClass: 'show',
                        keyboard: false,
                        controller: ModalInstanceCtrl,
                        scope: $scope
                    });
                }
            }

        }
        ctrl.confirmMultiSign = function (lstSignManage) {
            const confirm = NotificationService.confirm($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_DIGITAL_SIGN_CONFIRM', {'documentLength': lstSignManage.length}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    KySoTaiLieu.signMulti({ lstDocId: lstSignManage, isLoading: true }).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchSignManage();
                            NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        }
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }).catch(function (err) {
                    console.log(err);
                });
        }
        ctrl.confirmMultiSignOtp = function () {
            NotificationService.error($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_MULTI_OTP_ERROR'));
        }

        ctrl.confirmMultiSignKyc = function (lstSignManage, cccd) {
            const confirm = NotificationService.confirm($filter('translate')('INTERNAL.SIGN_MANAGE.SIGN_EKYC_SIGN_CONFIRM', {'documentLength': lstSignManage.length}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    $scope.signEkycData = {
                        currentSignStep: 1,
                        isCapture: true,
                        dataFront: "",
                        dataBack: "",
                        dataFace: "",
                        fotoContentType: "",
                        lstDocId: lstSignManage,
                        cccd: cccd[0]
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
                        lstDocId: lstSignManage,
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
                })
        }

    }

    function ModalDocInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, XuLyTaiLieu) {
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
                        $scope.onSearchSignManage();
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $uibModalInstance.close(false);
                    $scope.initBadRequest(response);
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
                $scope.initBadRequest(response);
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
                        $scope.onSearchSignManage();
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $uibModalInstance.close(false);
                    $scope.initBadRequest(response);
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
