// const { filter } = require("lodash");

(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select']).controller("ApprovalCommerceManageCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'PheDuyetTaiLieuThuongMai', 'XuLyTaiLieuThuongMai', ApprovalCommerceManageCtrl]);

    function ApprovalCommerceManageCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, PheDuyetTaiLieuThuongMai, XuLyTaiLieuThuongMai) {

        var ctrl = this;

        ctrl.searchApprovalManage = {
            document_type_id: "-1",
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

        ctrl.init = function () {
            PheDuyetTaiLieuThuongMai.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.lstDocumentType = response.data.data.lstDocumentType;
            }, function (response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="approvalManageCtrl.selectAll" ng-change="approvalManageCtrl.toggleAll(approvalManageCtrl.selectAll, approvalManageCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => {
                    $scope.onSearchApprovalManage()
                }, 300);
                return;
            }
            ctrl.searchApprovalManage.startDate = Utils.parseDate(ctrl.searchApprovalManage.start_date);
            ctrl.searchApprovalManage.endDate = Utils.parseDateEnd(ctrl.searchApprovalManage.end_date);
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
                searchData: ctrl.searchApprovalManage,
                isLoading: true
            }
            PheDuyetTaiLieuThuongMai.search(params).then(function (response) {
                ctrl.lstApprovalManage = response.data.data.data;
                ctrl.lstApprovalManage.forEach(e => {
                    e.sent_date = moment(e.sent_date).format("DD/MM/YYYY");
                });
                if (ctrl.searchApprovalManage.consignee_type_id != 0) {
                    ctrl.searchApprovalManage.creator_type_id = "-1";
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
                $scope.initBadRequest(response);
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
            ctrl.dtColumnsAddendum = [
                DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }),
                DTColumnBuilder.newColumn(null).withTitle(titleHtml).notSortable().renderWith(function (data, type, full, meta) {
                    ctrl.selected[full.id] = false;
                    return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="approvalManageCtrl.selected[' + data.id + ']" ng-change="approvalManageCtrl.toggleOne(approvalManageCtrl.selected)"/>';
                }),
                DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_SENT_DATE')),
                DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_PARENT_NAME')).notSortable().renderWith(function (data, type, full, meta) {
                    var html = `<a role="button" class='text-primary' ng-click='approvalManageCtrl.openDetailParentDocument(${data.parent_doc.id})'>${data.parent_doc.name}</a> `;
                    return html;
                }),
                DTColumnBuilder.newColumn('code').withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_CODE')).renderWith(function (data, type, full, meta){
                    return $scope.avoidXSSRender('code', meta.row, data);
                }),
                DTColumnBuilder.newColumn('name').withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_NAME')).renderWith(function (data, type, full, meta){
                    return $scope.avoidXSSRender('name', meta.row, data);
                }),
                DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.ADDENDUM_TYPE')).notSortable().renderWith(function (data, type, full, meta) {
                    let html = '';
                    ctrl.lstAddendumType.forEach(lst => {
                        if(lst.id == data.addendum_type){
                            html = $filter('translate')(lst.description);
                        }
                    });
                    return html;
                }),
                DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.ADDENDUM_MANAGE.ADDENDUM_MANAGE_MY_ORGANIZATION')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                    var html = '';
                    data.myOrganisation.forEach(e => {
                        if (e.state == 2) {
                            html += '<a class="btn btn-teal rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fa fa-check"></i></a>';
                        } else {
                            html += '<a class="btn btn-gray rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fa fa-check"></i></a>';
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
                    return Utils.renderDataTableAction(meta, "approvalManageCtrl.openDetailApprovalManageModal", false,"approvalManageCtrl.openApprovalManageModal", false , false, false, "approvalManageCtrl.onDownloadDocument", false, false, false);
                    // return Utils.renderDataTableAction(meta, "approvalManageCtrl.openDetailSignManageModal", false, false, ["approvalManageCtrl.signDocument", "approvalManageCtrl.signOtpDocument","approvalManageCtrl.signKycDocument"] , false, false, "approvalManageCtrl.onDownloadDocument", false, false, false);
                }),
            ];

            ctrl.dtColumns = [
                DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }),
                DTColumnBuilder.newColumn(null).withTitle(titleHtml).notSortable().renderWith(function (data, type, full, meta) {
                    ctrl.selected[full.id] = false;
                    return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="approvalManageCtrl.selected[' + data.id + ']" ng-change="approvalManageCtrl.toggleOne(approvalManageCtrl.selected)"/>';
                }),
                DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('COMMERCE.APPROVAL_MANAGE.APPROVAL_MANAGE_SENT_DATE')),
                DTColumnBuilder.newColumn('code').withTitle($filter('translate')('COMMERCE.APPROVAL_MANAGE.APPROVAL_MANAGE_CODE')).renderWith(function (data, type, full, meta){
                    return $scope.avoidXSSRender('code', meta.row, data);
                }),
                DTColumnBuilder.newColumn('name').withTitle($filter('translate')('COMMERCE.APPROVAL_MANAGE.APPROVAL_MANAGE_NAME')).renderWith(function (data, type, full, meta){
                    return $scope.avoidXSSRender('name', meta.row, data);
                }),
                DTColumnBuilder.newColumn(null).withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.DEFAULT')).notSortable().renderWith(function (data, type, full, meta) {
                    return $filter('translate')(Utils.getDocumentStyle(data.dc_style));
                }),
                DTColumnBuilder.newColumn(null).withTitle($filter('translate')('COMMERCE.APPROVAL_MANAGE.APPROVAL_MANAGE_MY_ORGANIZATION')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                    var html = '';
                    data.myOrganisation.forEach(e => {
                        if (e.state == 2) {
                            html += '<a class="btn btn-teal rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fa fa-check"></i></a>';
                        } else {
                            html += '<a class="btn btn-gray rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fa fa-check"></i></a>';
                        }
                    })
                    return html;
                }),
                DTColumnBuilder.newColumn(null).withTitle($filter('translate')('COMMERCE.APPROVAL_MANAGE.APPROVAL_MANAGE_PARTNER')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                    var html = '';
                    data.partner.forEach(e => {
                        if (e.state == 2) {
                            html += '<a class="btn btn-teal rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fa fa-check"></i></a>';
                        } else {
                            html += '<a class="btn btn-gray rounded-circle popover-item w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Người duyệt: ' + e.name + ' - ' + e.email + '"><i class="fa fa-check"></i></a>';
                        }
                    })
                    return html;
                }),
                DTColumnBuilder.newColumn('creator_name').withTitle($filter('translate')('COMMERCE.APPROVAL_MANAGE.APPROVAL_MANAGE_CREATOR')),
                DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                    if (ctrl.permission.is_write != 1) {
                        return "";
                    }
                    return Utils.renderDataTableAction(meta, "approvalManageCtrl.openDetailApprovalManageModal", false, "approvalManageCtrl.openApprovalManageModal", false, false, false, "approvalManageCtrl.onDownloadDocument", false, false, false);
                }),
            ];

        $scope.onSearchApprovalManage = function () {
            ctrl.dtInstance.rerender();
        }

        ctrl.openDetailApprovalManageModal = function (row) {
            let document = ctrl.lstApprovalManage[row];
            $state.go("index.commerce.viewDocument", {docId: document.id});
        }

        ctrl.openDetailParentDocument = function (id) {
            $state.go("index.internal.viewDocument", {docId: id});
        }

        ctrl.onDownloadDocument = function (row) {
            let doc = ctrl.lstApprovalManage[row];
            let formData = new FormData();
            formData.append("id", doc.id);
            XuLyTaiLieuThuongMai.getSignDocument(formData).then(function (response) {
                let fileName = Utils.removeVietnameseTones(doc.name) + ".pdf";
                Utils.downloadFormBinary(response, fileName);
            }, function (response) {
                $scope.initBadRequest(response);
            })
        }

        ctrl.openApprovalManageModal = function (row) {
            $scope.approveType = 0;
            $scope.approveDocument = angular.copy(ctrl.lstApprovalManage[row]);
            if ($scope.approveDocument.email != $scope.loginUser.email) {
                NotificationService.error($filter('translate')('COMMERCE.APPROVAL_MANAGE.ERR_NOT_TURN_APPROVAL'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                return;
            }
            $scope.approveParams = {
                isAgree: true,
                content: '',
            };
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/approveDocumentModal.html',
                windowClass: "fade show modal-blur",
                size: 'md modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openApproveMultiDocumentModal = function () {
            $scope.lstApprovalManageTmp = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        $scope.lstApprovalManageTmp.push(ctrl.lstApprovalManage.filter(e => e.id == id)[0].id);
                    }
                }
            }
            if ($scope.lstApprovalManageTmp.length == 0) {
                NotificationService.error($filter('translate')('COMMERCE.APPROVAL_MANAGE.ERR_NEED_CHOOSE_APPROVAL_MANAGE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                $scope.approveType = 1;
                $scope.approveParams = {
                    isAgree: true,
                    content: ''
                };
                $uibModal.open({
                    animation: true,
                    templateUrl: 'vcontract/views/modal/approveDocumentModal.html',
                    windowClass: "fade show modal-blur",
                    size: 'md modal-dialog-centered',
                    backdrop: 'static',
                    backdropClass: 'show',
                    keyboard: false,
                    controller: ModalInstanceCtrl,
                    scope: $scope
                });
            }
        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, PheDuyetTaiLieuThuongMai) {
        $scope.onApproveDocumentConfirm = function (type) {
            var errorMess = "";
            if (!$scope.approveParams.isAgree && $scope.approveParams.content == '') {
                errorMess += $filter('translate')('COMMERCE.APPROVAL_MANAGE.ERR_EMPTY_APPROVE_DENIED_REASON');
                ;
            }
            if (errorMess == "") {
                if (type == 0) {
                    const confirm = NotificationService.confirm($scope.approveParams.isAgree ? $filter('translate')('COMMERCE.APPROVAL_MANAGE.APPROVE_CONFIRM', {'approveManage': $scope.approveDocument.name}) : $filter('translate')('COMMERCE.APPROVAL_MANAGE.DENY_CONFIRM', {'approveManage': $scope.approveDocument.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                    confirm.then(function () {
                        PheDuyetTaiLieuThuongMai.approve({
                            'docId': $scope.approveDocument.id,
                            'params': $scope.approveParams,
                            isLoading: true
                        }).then(function (response) {
                            if (response.data.success) {
                                $scope.onSearchApprovalManage();
                                $uibModalInstance.close(false);
                                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                            }
                        }, function (response) {
                            $scope.initBadRequest(response);
                        });
                    }).catch(function (err) {
                    });
                } else {
                    const confirm = NotificationService.confirm($scope.approveParams.isAgree ? $filter('translate')('COMMERCE.APPROVAL_MANAGE.APPROVE_MULTI_CONFIRM', {'approvalManageLength': $scope.lstApprovalManageTmp.length}) : $filter('translate')('COMMERCE.APPROVAL_MANAGE.DENY_MULTI_CONFIRM', {'approvalManageLength': $scope.lstApprovalManageTmp.length}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                    confirm.then(function () {
                        PheDuyetTaiLieuThuongMai.approveMulti({
                            'lstDocId': $scope.lstApprovalManageTmp,
                            'params': $scope.approveParams,
                            isLoading: true
                        }).then(function (response) {
                            if (response.data.success) {
                                $scope.onSearchApprovalManage();
                                $uibModalInstance.close(false);
                                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                            } else {
                                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                            }
                        }, function (response) {
                            $scope.initBadRequest(response);
                        });
                    }).catch(function (err) {
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
