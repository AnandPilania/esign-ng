(function () {
    "use strict";
    angular
        .module("app", ["datatables", "datatables.select"])
        .controller("TurnOverReportCtrl", [
            "$scope", "$rootScope", "$compile", "$uibModal", "$http", "$state", "$window", "$timeout", "DTOptionsBuilder", "DTColumnBuilder", "$filter", "BaoCao", TurnOverReportCtrl,
        ]);
    function TurnOverReportCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, BaoCao) {
        var ctrl = this;

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

        $rootScope.lineLabels = $filter('translate')("DASHBOARD.TURN_OVER");

        ctrl.currentYear = new Date().getFullYear();
        ctrl.searchReport = {
            type: 0,
            agency_id: '-1',
            company_id: '-1',
            dashboardYear: ctrl.currentYear,
        };

        if($scope.loginUser == 2){
            ctrl.searchReport.agency_id = $scope.loginUser.agency_id;
        }

        ctrl.init = (function () {
            BaoCao.init().then(
                function (response) {
                    $scope.lstAgency = [];
                    $scope.lstCompany = [];
                    let lstAgency = response.data.data.lstAgency;
                    let lstCompany = response.data.data.lstCompany;
                    lstAgency.forEach(l => {
                        $scope.lstAgency.push({
                            id: l.id,
                            description: l.name
                        })
                        let key = l.id;
                        $scope.lstCompany[key] = [];
                    })
                    lstCompany.forEach(l => {
                        $scope.lstCompany[''+ l.agency_id].push({
                            id: l.id,
                            description: l.name
                        })
                    })
                },
                function (response) {
                    $scope.initBadRequest(response);
                }
            );
        })();

        ctrl.dtInstance = {};
        ctrl.reRender = 0;

        function getData(sSource, aoData, fnCallback, oSettings) {
            if(ctrl.searchReport.agency_id == -1) {
                ctrl.searchReport.company_id = '-1';
            }
            var draw = aoData[0].value;
            var order = aoData[2].value[0];
            order.columnName = aoData[1].value[order.column].data;
            var start = aoData[3].value;
            var length = aoData[4].value;
            var params = {
                draw: draw,
                start: start,
                limit: length,
                searchData: ctrl.searchReport,
                isLoading: true,
            };
            BaoCao.search(params).then(
                function (response) {
                    ctrl.lstData = response.data.data.data;
                    $rootScope.lineData = response.data.data.initData;
                    $rootScope.lineData.forEach(l => {
                        ctrl.totalDocument += l;
                    })
                    if(ctrl.reRender == 0) {
                        $timeout(function () {
                            angular.element("#renderDashboardChart").click();
                        }, 500);
                        ctrl.reRender++;
                    } else {
                        angular.element("#updateDashboardLineChart").click();
                    }
                    ctrl.lstData.forEach((e) => {
                        e.sent_date = moment(e.sent_date).format("DD/MM/YYYY");
                        e.expired_date = moment(e.expired_date).format("DD/MM/YYYY");
                        e.created_at = moment(e.created_at).format("DD/MM/YYYY");
                        e.finished_date = e.finished_date != null ? moment(e.finished_date).format("DD/MM/YYYY") : "";
                        ctrl.totalTurnOver += e.turn_over;
                    });
                    ctrl.totalTurnOver = new Intl.NumberFormat().format(ctrl.totalTurnOver);
                    fnCallback(response.data.data);
                },
                function (response) {
                    var records = {
                        draw: draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: [],
                    };
                    fnCallback(records);
                    $scope.initBadRequest(response);
                }
            );
        }

        function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // $compile(nRow)($scope);
        }

        ctrl.dtOptions = DTOptionsBuilder.newOptions()
            .withPaginationType("full_numbers")
            .withDisplayLength(20)
            .withOption("order", false)
            .withOption("searching", false)
            .withLanguage(Utils.getDataTableLanguage($scope.loginUser.language))
            .withDOM(Utils.getDataTableDom())
            .withOption("lengthMenu", [20, 50, 100])
            .withOption("responsive", true)
            .withOption("processing", true)
            .withOption("serverSide", true)
            .withOption("paging", true)
            .withFnServerData(getData)
            .withOption("createdRow", function (row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            })
            .withOption("headerCallback", function (header) {
                ctrl.selectAll = false;
                $compile(angular.element(header).contents())($scope);
            });
        ctrl.dtColumns = [
            DTColumnBuilder.newColumn(null)
                .withTitle("#")
                .withClass("text-center")
                .notSortable()
                .renderWith(function (data, type, full, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }),
            DTColumnBuilder.newColumn("name").withTitle($filter("translate")("REPORT.NAME")).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('REPORT.MONTH_1')).notSortable()
            .renderWith(function (data, type, full, meta) {
                return new Intl.NumberFormat().format(data.m1) + ' VND'
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('REPORT.TURN_OVER')).notSortable()
            .renderWith(function (data, type, full, meta) {
               return new Intl.NumberFormat().format(data.m2) + ' VND'
            })
        ];

        $scope.onSearchReport = function () {
            if(!ctrl.searchReport.agency_id || !ctrl.searchReport.company_id) {
                return;
            }
            ctrl.dtInstance.rerender();
        };
        ctrl.clickArrow = function (state) {
            if(state == 0){
                if(ctrl.searchReport.dashboardYear != ctrl.currentYear) {
                    ctrl.searchReport.dashboardYear++;
                    $scope.onSearchReport()
                }
            } else if(state == 1) {
                ctrl.searchReport.dashboardYear--;
                $scope.onSearchReport()
            }
        }
        $scope.searchByType = function (type) {
            switch (type) {
                case 1:
                    ctrl.searchReport.completed = 1;
                    break;
                case 2:
                    ctrl.searchReport.in_process = 1;
                    break;
                case 3:
                    ctrl.searchReport.abort = 1;
                    break;
            }
            $scope.onSearchReport()
        }

        $scope.closeModal = function () {
            ctrl.searchReport.completed =-1;
            ctrl.searchReport.in_process = -1;
            ctrl.searchReport.abort = -1;
        }

        $scope.onViewReportDetail = function () {
            $("#turnOverReportDetail").modal("show");
        };

        $scope.export = function () {
            $(".loadingapp").removeClass("hidden");
            let current = Utils.formatFullDate(new Date())
            let formData = new FormData();
            formData.append('type', 2);
            formData.append('agency_id', ctrl.searchReport.agency_id);
            formData.append('company_id', ctrl.searchReport.company_id);
            formData.append('dashboardYear', ctrl.searchReport.dashboardYear);
            BaoCao.export(formData).then(
                function (response) {
                    $(".loadingapp").addClass("hidden");
                    let fileName = "BaoCao_" + current + ".xlsx";
                    Utils.downloadFormBinary(response, fileName);
                },
                function (response) {
                    $(".loadingapp").addClass("hidden");
                    $scope.initBadRequest(response);
                }
            );
        };
    }
})();
