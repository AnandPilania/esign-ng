
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select']).controller("SignEkycReportCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'BaoCaoEkyc', SignEkycReportCtrl]);
    function SignEkycReportCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, BaoCaoEkyc) {

        var ctrl = this;
        ctrl.dashboardYear = new Date().getFullYear();

        ctrl.searchSignEkyc = {
            start_date: moment().startOf('month').format('DD/MM/YYYY'),
            end_date: moment().endOf('month').format('DD/MM/YYYY')
        }

        $rootScope.kyc_status_label = [
            $filter("translate")("REPORT.KYC_SUCCESS"),
            $filter("translate")("REPORT.KYC_SUCCESS"),
            $filter("translate")("REPORT.KYC_FAIL"),
        ];

        $rootScope.lineCtx_label = [
			$filter('translate')("DASHBOARD.JANUARY"),
			$filter('translate')("DASHBOARD.FEBRUARY"),
			$filter('translate')("DASHBOARD.MARCH"),
			$filter('translate')("DASHBOARD.APRIL"),
			$filter('translate')("DASHBOARD.MAY"),
			$filter('translate')("DASHBOARD.JUNE"),
			$filter('translate')("DASHBOARD.JULY"),
			$filter('translate')("DASHBOARD.AUGUST"),
			$filter('translate')("DASHBOARD.SEPTEMBER"),
			$filter('translate')("DASHBOARD.OCTOBER"),
			$filter('translate')("DASHBOARD.NOVEMBER"),
			$filter('translate')("DASHBOARD.DECEMBER"),
		]

        ctrl.init = function() {
            BaoCaoEkyc.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchSignEkyc() }, 300);
                return;
            }
            ctrl.searchSignEkyc.startDate = Utils.parseDate(ctrl.searchSignEkyc.start_date);
            ctrl.searchSignEkyc.endDate = Utils.parseDateEnd(ctrl.searchSignEkyc.end_date);
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
                searchData: ctrl.searchSignEkyc,
                isLoading: true
            }
            $rootScope.pieData_kyc = [0,0];
            BaoCaoEkyc.search(params).then(function (response) {
                ctrl.lstSendSignKyc = response.data.data.data;
				$rootScope.lineData = [0,0,0,0,0,0,0,0,0,0,0,0];
                $rootScope.pieData_kyc[0] = response.data.data.total_kyc_success;
                $rootScope.pieData_kyc[2] = response.data.data.total_kyc_failed;
                response.data.data.line_report.forEach(element => {
                    $rootScope.lineData[element.month - 1] = element.total;
                });
                $timeout(function () {
                    angular.element("#renderSignKycChart").click();
                }, 500);
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
        ctrl.dtColumns = [
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn('created_at').withTitle($filter('translate')('REPORT.REPORT_KYC.SEND_TIME')),
            DTColumnBuilder.newColumn('type').withTitle($filter('translate')('REPORT.REPORT_KYC.TYPE.DEFAULT')).renderWith(function (data, type, full, meta){
                switch(data.type){
                    case 1:
                        return  $filter('translate')('REPORT.REPORT_KYC.TYPE.TYPE_1');
                    case 2:
                        return  $filter('translate')('REPORT.REPORT_KYC.TYPE.TYPE_2');
                    case 3:
                        return  $filter('translate')('REPORT.REPORT_KYC.TYPE.TYPE_3');
                    default:
                        return  $filter('translate')('REPORT.REPORT_KYC.TYPE.TYPE_0');
                }
            }),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('REPORT.REPORT_KYC.CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('receiver_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('REPORT.REPORT_KYC.STATUS')).renderWith(function (data, type, full, meta){
                switch(data.code){
                    case 200:
                        return "<span class='badge bg-green me-1'></span> " + $filter('translate')('REPORT.REPORT_KYC.STATUS_SUCCESS');
                    default:
                        return "<span class='badge bg-danger me-1'></span> " + $filter('translate')('REPORT.REPORT_KYC.STATUS_FAIL');
                }
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('REPORT.REPORT_KYC.TIME')).renderWith(function (data, type, full, meta){
                return data.end_time - data.start_time + "ms";
            }),

        ];

        $scope.onSearchSignEkyc = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.onViewReportDetail = function () {
            $("#reportMessageDetail").modal("show");
        };

        $scope.export = function(){
            ctrl.searchSignEkyc.startDate = Utils.parseDate(ctrl.searchSignEkyc.start_date);
            ctrl.searchSignEkyc.endDate = Utils.parseDate(ctrl.searchSignEkyc.end_date);
            var date_diff = Utils.dateDiffInDays(new Date(ctrl.searchSignEkyc.startDate), new Date(ctrl.searchSignEkyc.endDate));
            if(date_diff < 0){
                NotificationService.error($filter('translate')('REPORT.ERROR_START_DATE_SMALLER_END_DATE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else if(date_diff > 30){
                NotificationService.error($filter('translate')('REPORT.ERROR_DATE_RANGE_OVER_30_DAYS'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                $('.loadingapp').removeClass('hidden');
                let formData = new FormData();
                formData.append('startDate', ctrl.searchSignEkyc.startDate);
                formData.append('endDate', ctrl.searchSignEkyc.endDate);
                formData.append('keyword', ctrl.searchSignEkyc.keyword);
                formData.append("dc_style", ctrl.searchSignEkyc.dc_style);
                BaoCaoEkyc.export(formData).then(function(response){
                    $('.loadingapp').addClass('hidden');
                    let fileName = "BaoCaoEkyc_" + ctrl.searchSignEkyc.start_date + "_" + ctrl.searchSignEkyc.end_date + ".xlsx";
                    Utils.downloadFormBinary(response, fileName);
                }, function (response) {
                    $('.loadingapp').addClass('hidden');
                    $scope.initBadRequest(response);
                });
            }
        }
    }
})();
