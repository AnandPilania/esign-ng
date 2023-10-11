(function () {
    "use strict";
    angular
        .module("app", ["datatables", "datatables.select"])
        .controller("CustomerReportCtrl", [
            "$scope", "$rootScope", "$compile", "$uibModal", "$http", "$state", "$window", "$timeout", "DTOptionsBuilder", "DTColumnBuilder", "$filter", "BaoCao", CustomerReportCtrl,
        ]);
    function CustomerReportCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, BaoCao) {
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

        $rootScope.lineLabels = $filter('translate')("DASHBOARD.COMPANY");

        ctrl.searchReport = {
            type: 1,
            new: -1,
            agency_id: '-1',
            status: -1,
            no_used: -1,
            company_id: -1,
            dashboardYear: new Date().getFullYear()
        };

        if($scope.loginUser == 2){
            ctrl.searchReport.agency_id = $scope.loginUser.agency_id;
        }

        ctrl.init = (function () {
            BaoCao.init().then(
                function (response) {
                    $scope.lstAgency = [];
                    let lstAgency = response.data.data.lstAgency;
                    lstAgency.forEach(l => {
                        $scope.lstAgency.push({
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
                searchData: ctrl.searchReport,
                isLoading: true,
            };
            BaoCao.search(params).then(
                function (response) {
                    ctrl.lstData = response.data.data.data;
                    ctrl.newCustomer = response.data.data.newCustomer;
                    ctrl.activeCustomer = response.data.data.activeCustomer;
                    ctrl.pausedCustomer = response.data.data.pausedCustomer;
                    ctrl.noUsed = response.data.data.noUsed;
                    $rootScope.lineData = response.data.data.initData;
                    if(ctrl.reRender == 0) {
                        $timeout(function () {
                            angular.element("#renderDashboardChart").click();
                        }, 500);
                        ctrl.reRender++;
                    } else {
                        angular.element("#updateDashboardLineChart").click();
                    }
                    ctrl.lstData.forEach((e) => {
                        e.created_at = moment(e.created_at).format("DD/MM/YYYY");
                        if (e.last_active) {
                            e.last_active = moment(e.last_active).format("DD/MM/YYYY");
                        }
                    });
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
            .withOption('order', [[1, 'asc']])
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
            DTColumnBuilder.newColumn("agency_name").withTitle($filter('translate')('REPORT.AGENCY')).notSortable()
            .renderWith(function (data, type, full, meta) {
                return $scope.avoidXSSRender('agency_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn("total_doc").withTitle($filter('translate')('REPORT.TOTAL_DOC'))
            .renderWith(function (data, type, full, meta) {
                return $scope.avoidXSSRender('total_doc', meta.row, data);
            }),
            DTColumnBuilder.newColumn("last_active").withTitle($filter('translate')('REPORT.LAST_ACTIVE')),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('REPORT.STATUS')).notSortable()
            .renderWith(function (data, type, full, meta) {
                return Utils.getDataTableStatusColumn(data);
            }),
            DTColumnBuilder.newColumn("created_at").withTitle($filter('translate')('REPORT.DOCUMENT_CREATED_TIME'))
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

        $scope.onViewReportDetail = function () {
            $("#turnOverReportDetail").modal("show");
        };

        $scope.searchByType = function (type) {
            switch (type) {
                case 0:
                    ctrl.searchReport.new = 1;
                    break;
                case 1:
                    ctrl.searchReport.status = 1;
                    break;
                case 2:
                    ctrl.searchReport.status = 0;
                    break;
                case 3:
                    ctrl.searchReport.no_used = 1;
                    break;
            }
            $scope.onSearchReport()
        }

        $scope.closeModal = function () {
            ctrl.searchReport.new =-1;
            ctrl.searchReport.status = -1;
            ctrl.searchReport.no_used = -1;
        }

        $scope.export = function () {
            $('.loadingapp').removeClass('hidden');
            let current = Utils.formatFullDate(new Date())
            let formData = new FormData();
            formData.append('type', 1);
            formData.append('agency_id', ctrl.searchReport.agency_id);
                BaoCao.export(formData).then(
                    function (response) {
                        $(".loadingapp").addClass("hidden");
                        let fileName = "BaoCao_Customer_"+ current +".xlsx";
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
