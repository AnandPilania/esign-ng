
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'GuideService']).controller("GuideVideoCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'QuanLyVideoHuongDan', GuideVideoCtrl]);
    function GuideVideoCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, QuanLyVideoHuongDan) {

        var ctrl = this;

        ctrl.searchGuideVideo = {
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

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="guideVideoCtrl.selectAll" ng-change="guideVideoCtrl.toggleAll(guideVideoCtrl.selectAll, guideVideoCtrl.selected)">';

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
                searchData: ctrl.searchGuideVideo,
                isLoading: true
            }
            QuanLyVideoHuongDan.search(params).then(function (response) {
                ctrl.lstGuideVideo = response.data.data.data;
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
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').withOption('width', '10%').notSortable().renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('GUIDE_VIDEO.NAME')).withOption('width', '30%'),
            DTColumnBuilder.newColumn('description').withTitle($filter('translate')('GUIDE_VIDEO.DESCRIPTION')).withOption('width', '40%'),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                var html = `<div class="btn btn-outline-primary btn-outline-primary-customer" ng-click="guideVideoCtrl.openViewGuideVideoModal(${meta.row});"> Xem hướng dẫn </div>`
                return html;
            }),
        ];

        ctrl.init = function () {
            QuanLyVideoHuongDan.init()
        }();

        $scope.onSearchGuideVideo = function () {
            ctrl.dtInstance.rerender();
        }


        ctrl.openViewGuideVideoModal = function (row) {
            $state.go("guide.detail_guide_video", {'id': ctrl.lstGuideVideo[row].id});
        }

    }
})();
