
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select']).controller("DocumentListCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'DanhSachTaiLieu', 'XuLyTaiLieu', DocumentListCtrl]);
    function DocumentListCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, DanhSachTaiLieu, XuLyTaiLieu) {

        var ctrl = this;

        ctrl.searchDocumentList = {
            keyword: "",
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

        // ctrl.init = function() {
        //     DanhSachTaiLieu.init().then(function (response) {
        //         ctrl.permission = response.data.data.permission;
        //         ctrl.lstDocumentType = response.data.data.lstDocumentType;
        //         ctrl.lstCreator = response.data.data.lstCreator;
        //     }, function(response) {
        //         NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //         $state.go("index.dashboard");
        //     });
        // }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="documentListCtrl.selectAll" ng-change="documentListCtrl.toggleAll(documentListCtrl.selectAll, documentListCtrl.selected)">';
        function getData(sSource, aoData, fnCallback, oSettings) {
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
            .withDisplayLength(1)
            .withOption('order', [[2, 'desc']])
            .withOption('searching', false)
            .withLanguage(Utils.getDataTableLanguage($scope.loginUser.language))
            .withDOM(Utils.getDataTableDom())
            .withOption('lengthMenu', [1, 50, 100])
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
            DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_SENT_DATE')).notSortable(),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_CODE')).notSortable(),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_NAME')).notSortable(),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.DEFAULT')).notSortable().renderWith(function (data, type, full, meta) {
                return $filter('translate')(Utils.getDocumentStyle(data.dc_style));
            }),
            DTColumnBuilder.newColumn('expired_date').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_EXPIRED_TIME')).notSortable(),
            DTColumnBuilder.newColumn('finished_date').withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_IS_COMPLETED')).notSortable(),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('INTERNAL.DOCUMENT_LIST.DOCUMENT_LIST_DOC_STATE')).notSortable().renderWith(function(data, type, full, meta){
                return Utils.getDataTableDocumentStateColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
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
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            })
        }

        $scope.onSearchDocumentList = function () {
            $scope.showTable = true;
            ctrl.dtInstance.rerender();
        }

        ctrl.openEditDocumentListModal = function (row) {
            let document = ctrl.lstDocumentList[row];
             $state.go("index.viewDocument", {docId: document.id});
        }

    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, DanhSachTaiLieu) {
        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
