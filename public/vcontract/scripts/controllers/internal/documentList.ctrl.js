
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select']).controller("DocumentListCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'DanhSachTaiLieu', 'XuLyTaiLieu', DocumentListCtrl]);
    function DocumentListCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, DanhSachTaiLieu, XuLyTaiLieu) {

        var ctrl = this;

        ctrl.searchDocumentList = {
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

        ctrl.init = function() {
            DanhSachTaiLieu.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.lstDocumentType = response.data.data.lstDocumentType;
                ctrl.lstCreator = response.data.data.lstCreator;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="documentListCtrl.selectAll" ng-change="documentListCtrl.toggleAll(documentListCtrl.selectAll, documentListCtrl.selected)">';
        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchDocumentList() }, 300);
                return;
            }
            ctrl.searchDocumentList.startDate = Utils.parseDate(ctrl.searchDocumentList.start_date);
            ctrl.searchDocumentList.endDate = Utils.parseDateEnd(ctrl.searchDocumentList.end_date);
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
                searchData: ctrl.searchDocumentList,
                isLoading: true
            }
            DanhSachTaiLieu.search(params).then(function (response) {
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="documentListCtrl.selected[' + data.id + ']" ng-change="documentListCtrl.toggleOne(documentListCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_SENT_DATE')),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.DEFAULT')).notSortable().renderWith(function (data, type, full, meta) {
                return $filter('translate')(Utils.getDocumentStyle(data.dc_style));
            }),
            DTColumnBuilder.newColumn('expired_date').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_EXPIRED_TIME')),
            DTColumnBuilder.newColumn('finished_date').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_IS_COMPLETED')),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_DOC_STATE')).renderWith(function(data, type, full, meta){
                return Utils.getDataTableDocumentStateColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('DOCUMENT.ADDENDUM')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return data.addendum.length;
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                let state = ctrl.lstDocumentList[meta.row].document_state;
                let is_verify = ctrl.lstDocumentList[meta.row].is_verify_content == 1;
                return Utils.renderDataTableAction(meta, "documentListCtrl.openEditDocumentListModal", false, false, false, false, false, "documentListCtrl.onDownloadDocumentList", "documentListCtrl.onHistoryDocumentList", false, false, state >= 7 && is_verify ? "documentListCtrl.onHistoryTransactionDocumentList": false);
            }),
        ];

        ctrl.onHistoryDocumentList = function(row) {
            let docId = ctrl.lstDocumentList[row].id;
            $scope.showDocumentHistory(docId);
        }

        ctrl.onHistoryTransactionDocumentList = function(row) {
            let docId = ctrl.lstDocumentList[row].id;
            let docCode = ctrl.lstDocumentList[row].code;
            let docName = ctrl.lstDocumentList[row].name;
            $scope.showTransactionDocumentHistory(docId, docCode, docName);
        }


        ctrl.onDownloadDocumentList = function(row) {
            let doc = ctrl.lstDocumentList[row];
            let formData = new FormData();
            formData.append("id", doc.id);
            XuLyTaiLieu.getSignDocument(formData).then(function (response) {
                let fileName = Utils.removeVietnameseTones(doc.name) + ".pdf";
                Utils.downloadFormBinary(response, fileName);
            }, function (response) {
                $scope.initBadRequest(response);
            })
        }

        $scope.onSearchDocumentList = function () {
            ctrl.dtInstance.rerender();
        }

        ctrl.openEditDocumentListModal = function (row) {
            let document = ctrl.lstDocumentList[row];
            if (document.document_state == 1) {
                $state.go("editInternal", {docId: document.id});
            } else {
                $state.go("index.internal.viewDocument", {docId: document.id});
            }
        }

        ctrl.onDeleteMultiDocumentList = function () {
            let lstDocumentList = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstDocumentList.push(id);
                    }
                }
            }
            if (lstDocumentList.length == 0) {
                NotificationService.error($filter('translate')('INTERNAL.DOCUMENT_LIST.ERR_NEED_CHOOSE_DOCUMENT_LIST'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('INTERNAL.DOCUMENT_LIST.DELETE_MULTI_CONFIRM', { 'documentListLength': lstDocumentList.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    DanhSachTaiLieu.deleteMulti({'lst': lstDocumentList, isLoading: true}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchDocumentList();
                            lstDocumentList.forEach(e => delete ctrl.selected[e]);
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

        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, DanhSachTaiLieu) {
        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
