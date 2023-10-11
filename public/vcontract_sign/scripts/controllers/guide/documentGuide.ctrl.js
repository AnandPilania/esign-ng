
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'GuideService']).controller("DocumentGuideCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'TaiLieuHuongDan', DocumentGuideCtrl]);
    function DocumentGuideCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, TaiLieuHuongDan) {

        var ctrl = this;

        ctrl.searchDocumentTutorial = {
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

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="documentTutorialCtrl.selectAll" ng-change="documentTutorialCtrl.toggleAll(documentTutorialCtrl.selectAll, documentTutorialCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
            // if (!ctrl.permission) {
            //     setTimeout(() => { $scope.onSearchDocumentTutorial() }, 300);
            //     return;
            // }
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
                searchData: ctrl.searchDocumentTutorial,
                isLoading: true
            }
            TaiLieuHuongDan.search(params).then(function (response) {
                ctrl.lstDocumentTutorial = response.data.data.data;
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
            .withLanguage(Utils.getDataTableLanguage('vi'))
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
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().withOption('width', '10%').renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('DOCUMENT_TUTORIAL.NAME')).withOption('width', '30%'),
            DTColumnBuilder.newColumn('description').withTitle($filter('translate')('DOCUMENT_TUTORIAL.DESCRIPTION')).withOption('width', '40%'),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                // if (ctrl.permission.is_write != 1) {
                //     return "";
                // }
                var html = `<div class="btn btn-outline-primary btn-outline-primary-customer" ng-click="openViewDocumentTutorialModal(${meta.row});"> Xem hướng dẫn </div>`
                return html;
                //return Utils.renderDataTableAction(meta, "documentGuideCtrl.openEditDocumentTutorialModal", false, false, false, false, false, false, false, "documentTutorialCtrl.onDeleteDocumentTutorial", false);
            }),
        ];

        ctrl.init = function () {
            TaiLieuHuongDan.init()
            // .then(function (response) {
            //     ctrl.permission = response.data.data.permission;
            // }, function (response) {
            //     NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            //     $state.go("index.dashboard");
            // });
        }();

        $scope.onSearchDocumentTutorial = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.openViewDocumentTutorialModal = function (row) {
            $state.go("guide.detail_document_tutorial", {'id': ctrl.lstDocumentTutorial[row].id});
        }


    }



})();
