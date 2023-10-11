
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select','DocumentSrvc']).controller("NearExpireDocumentListCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'DanhSachTaiLieuSapHetHan', 'XuLyTaiLieuT', NearExpireDocumentListCtrl]);
    function NearExpireDocumentListCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, DanhSachTaiLieuSapHetHan, XuLyTaiLieuT) {

        var ctrl = this;

        ctrl.searchDocumentList = {
            document_group_id: "-1",
            dc_style : "-1",
            parent_id : "-1",
            document_type_id: "-1",
            keyword: ""
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

        //init
        ctrl.init = function() {
            DanhSachTaiLieuSapHetHan.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.searchDocumentList.document_group_id = response.data.data.firstDocumentGroupId.toString();
                ctrl.lstDocumentGroup = response.data.data.lstDocumentGroup;
                ctrl.lstDocumentType = response.data.data.lstDocumentType;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        //search
        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="nearExpireDocumentListCtrl.selectAll" ng-change="nearExpireDocumentListCtrl.toggleAll(nearExpireDocumentListCtrl.selectAll, nearExpireDocumentListCtrl.selected)">';
        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchDocumentList() }, 300);
                return;
            }
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
                searchData: ctrl.searchDocumentList
            }
            DanhSachTaiLieuSapHetHan.search(params).then(function (response) {
                ctrl.lstDocumentList = response.data.data.data;
                ctrl.lstDocumentList.forEach(e => {
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
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            })
        }

        function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // $compile(nRow)($scope);
        }

        ctrl.dtOptions = DTOptionsBuilder.newOptions()
            .withPaginationType('full_numbers')
            .withDisplayLength(20)
            .withOption('order', [[2, 'asc']])
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="nearExpireDocumentListCtrl.selected[' + data.id + ']" ng-change="nearExpireDocumentListCtrl.toggleOne(nearExpireDocumentListCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.DOCUMENT_LIST_SENT_DATE')),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.DOCUMENT_LIST_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.DOCUMENT_LIST_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.DEFAULT')).notSortable().renderWith(function (data, type, full, meta) {
                return $filter('translate')(Utils.getDocumentStyle(data.dc_style));
            }),
            DTColumnBuilder.newColumn('expired_date').withTitle($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.DOCUMENT_LIST_EXPIRED_TIME')),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "nearExpireDocumentListCtrl.openEditDocumentListModal", false, false, false, false, false, "nearExpireDocumentListCtrl.onDownloadDocumentList", "nearExpireDocumentListCtrl.onHistoryDocumentList", false, false);
            }),
        ];
        ctrl.dtColumnsAddendum = [
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn(null).withTitle(titleHtml).notSortable().renderWith(function (data, type, full, meta) {
                ctrl.selected[full.id] = false;
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="nearExpireDocumentListCtrl.selected[' + data.id + ']" ng-change="nearExpireDocumentListCtrl.toggleOne(nearExpireDocumentListCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.ADDENDUM_LIST_SENT_DATE')),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.ADDENDUM_MANAGE_PARENT_NAME')).notSortable().renderWith(function (data, type, full, meta) {
                var html = `<a role="button" class='text-primary' ng-click='signManageCtrl.openDetailParentDocument(${data.parent_doc.id})'>${data.parent_doc.name}</a> `;
                return html;
            }),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.ADDENDUM_LIST_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.ADDENDUM_LIST_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.DEFAULT')).notSortable().renderWith(function (data, type, full, meta) {
                return $filter('translate')(Utils.getDocumentStyle(data.dc_style));
            }),
            DTColumnBuilder.newColumn('expired_date').withTitle($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.DOCUMENT_LIST_EXPIRED_TIME')),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "nearExpireDocumentListCtrl.openEditDocumentListModal", false, false, false, false, false, "nearExpireDocumentListCtrl.onDownloadDocumentList", "nearExpireDocumentListCtrl.onHistoryDocumentList", false, false);
            }),
        ];

        $scope.onSearchDocumentList = function () {
            ctrl.dtInstance.rerender();
        }

        //view detail
        ctrl.openEditDocumentListModal = function (row) {
            let document = ctrl.lstDocumentList[row];
            if (ctrl.searchDocumentList.document_group_id == 1){
                $state.go("index.internal.viewDocument", {docId: document.id});
            }else{
                $state.go("index.commerce.viewDocument", {docId: document.id});
            }

        }

        //view history
        ctrl.onHistoryDocumentList = function(row) {
            let docId = ctrl.lstDocumentList[row].id;
            $scope.showDocumentHistory(docId);
        }

        //download
        ctrl.onDownloadDocumentList = function(row) {
            let doc = ctrl.lstDocumentList[row];
            let formData = new FormData();
            formData.append("id", doc.id);
            if (ctrl.searchDocumentList.document_group_id == 1){
                XuLyTaiLieuT.getSignDocumentInternal(formData).then(function (response) {
                    let fileName = Utils.removeVietnameseTones(doc.name) + ".pdf";
                    Utils.downloadFormBinary(response, fileName);
                }, function (response) {
                    $scope.initBadRequest(response);
                })
            }else {
                XuLyTaiLieuT.getSignDocumentCommerce(formData).then(function (response) {
                    let fileName = Utils.removeVietnameseTones(doc.name) + ".pdf";
                    Utils.downloadFormBinary(response, fileName);
                }, function (response) {
                    $scope.initBadRequest(response);
                })

            }

        }

        //change group
        $scope.changeDocumentGroup = function(){
            var params = {
                groupId: ctrl.searchDocumentList.document_group_id
            }
            DanhSachTaiLieuSapHetHan.changeGroup(params).then(function (response) {
                ctrl.searchDocumentList.document_type_id = "-1";
                ctrl.lstDocumentType = response.data.data.lstDocumentType;

            }, function(response) {
                $scope.initBadRequest(response);
            });
        };

        //open resend modal
        ctrl.openRenewMultiDocumentModal = function () {
            let lstDocumentList = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstDocumentList.push(id);
                    }
                }
            }

            if (lstDocumentList.length == 0) {
                NotificationService.error($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.ERR_NEED_CHOOSE_DOCUMENT_LIST'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                $scope.resendDocument = {
                    lst_document_list: lstDocumentList,
                    email_content : "",
                    expired_date: moment().add(2, 'months').endOf('month').format('DD/MM/YYYY')
                }

                $uibModal.open({
                    animation: true,
                    templateUrl: 'vcontract/views/modal/renewNearExpireDocument.html',
                    windowClass: "fade show modal-blur",
                    size: 'lg modal-dialog-centered',
                    backdrop: 'static',
                    backdropClass: 'show',
                    keyboard: false,
                    controller: ModalInstanceCtrl,
                    scope: $scope
                });
            }

        }

    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, DanhSachTaiLieuSapHetHan) {

        //gia han
        $scope.onRenewMultiDocumentList = function () {
            let resendDocument = $scope.resendDocument;
            let errorMess = "";
            if (resendDocument.expired_date == "") {
                errorMess += $filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.ERR_NEED_CHOOSE_EXPIRED_DATE');
            }

            if (errorMess == "") {
                const confirm = NotificationService.confirm($filter('translate')('DOCUMENT_HANDLE.NEAR_EXPIRED_DOCUMENT_LIST.RENEW_MULTI_CONFIRM', { 'documentListLength': resendDocument.lst_document_list.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    DanhSachTaiLieuSapHetHan.renewMulti(resendDocument).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchDocumentList();
                            NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        } else {
                            NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        }
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }).catch(function (err) {
                });
            }else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }

})();
