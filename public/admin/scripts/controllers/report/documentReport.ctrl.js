// import Da from "../../../../bower_components/moment/src/locale/da";

(function () {
    "use strict";
    angular
        .module("app", ["datatables", "datatables.select"])
        .controller("DocumentReportCtrl", [
            "$scope", "$rootScope", "$compile", "$uibModal", "$http", "$state", "$window", "$timeout", "DTOptionsBuilder", "DTColumnBuilder", "$filter", "BaoCao", DocumentReportCtrl,
        ]);
    function DocumentReportCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, BaoCao) {
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

        $rootScope.lineLabels = $filter('translate')("REPORT.DOCUMENT");
        $rootScope.document_type_label = [
            $filter('translate')("REPORT.DOCUMENT_COMPLETED"),
            $filter('translate')("REPORT.DOCUMENT_PROCESSING"),
            $filter('translate')("REPORT.DOCUMENT_FAILED"),
            $filter('translate')("REPORT.DOCUMENT_CANCEL"),
            $filter('translate')("REPORT.DOCUMENT_OVERDUE"),
,
        ];

        ctrl.currentYear = new Date().getFullYear();
        ctrl.searchReport = {
            document_state :-1,
            type: 2,
            agency_id: "-1",
            company_id: "-1",
            completed: false,
            in_process: false,
            cancel: false,
            abort: false,
            overdue : false,
            not_authorize : false,
            verify_fail : false,
            start_date: moment().startOf("year").format("DD/MM/YYYY"),
            end_date: moment().endOf("month").format("DD/MM/YYYY"),
            startDate: "",
            endDate: "",
            // dashboardYear: ctrl.currentYear
        };

        if($scope.loginUser == 2){
            ctrl.searchReport.agency_id = $scope.loginUser.agency_id;
        }
        ctrl.lstDocumentState = [
            { id: "0", description: "REPORT.IN_PROCESS" },
            { id: "4", description: "REPORT.ABORT" },
            { id: "6", description: "REPORT.CANCEL" },
            { id: "8", description: "REPORT.COMPLETED" },
            { id: "11", description: "REPORT.OVERDUE" },
        ];
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
                            description: l.agency_name
                        })
                        let key = l.id;
                        $scope.lstCompany[key] = [];
                    })
                    lstCompany.forEach(l => {
                        $scope.lstCompany['' + l.agency_id].push({
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
            ctrl.searchReport.startDate=Utils.parseDate(ctrl.searchReport.start_date);
            ctrl.searchReport.endDate=Utils.parseDate(ctrl.searchReport.end_date);
            console.log(ctrl.searchReport);
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
            $rootScope.pieData=[0, 0, 0, 0, 0];
            $rootScope.lineData=[0,0,0,0,0,0,0,0,0,0,0,0];
            BaoCao.search(params).then(
                function (response) {
                    ctrl.totalDocument = 0;
                    ctrl.completeDocument = 0;
                    ctrl.inProcessDocument = 0;
                    ctrl.abortDocument = 0;
                    ctrl.cancelDocument = 0;
                    ctrl.overdueDocument = 0;
                    ctrl.not_authorize = 0;
                    ctrl.verify_fail = 0;

                    ctrl.lstData = response.data.data.data;
                    $rootScope.pieData=[0, 0, 0, 0, 0];
                    ctrl.pie = response.data.data.pie;
                    ctrl.pie.forEach(e => {
                        if (e.document_state == 11 || e.document_state == 8) {
                            $rootScope.pieData[0] += e.total;
                        } else if (e.document_state == 4 || e.document_state == 6 || e.document_state == 7 || e.document_state == 5 || e.document_state == 10) {
                            $rootScope.pieData[2] += e.total;
                        } else {
                            $rootScope.pieData[1] += e.total;
                        }
                    })
                    ctrl.lstData.forEach((e) => {
                        e.sent_date = moment(e.sent_date).format("DD/MM/YYYY");
                        e.expired_date = moment(e.expired_date).format("DD/MM/YYYY");
                        e.created_at = moment(e.created_at).format("DD/MM/YYYY");
                        e.finished_date = e.finished_date != null ? moment(e.finished_date).format("DD/MM/YYYY") : "";
                    });

                    $rootScope.lineData = response.data.data.initData;

                    ctrl.lstReport = response.data.data.report;
                    ctrl.lstReport.forEach((e)=>{
                        ctrl.totalDocument += e.total;
                        if (e.document_state == 11 || e.document_state == 8) {
                            ctrl.completeDocument += e.total;
                        } else if (e.document_state == 4) {
                            ctrl.cancelDocument += e.total;
                        } else if (e.document_state == 6) {
                            ctrl.abortDocument += e.total;
                        }else if (e.document_state == 7) {
                            ctrl.not_authorize += e.total;
                        }else if (e.document_state == 10) {
                            ctrl.verify_fail += e.total;
                        } else if (e.document_state == 5) {
                            ctrl.overdueDocument += e.total;
                        } else {
                            ctrl.inProcessDocument += e.total;
                        }
                    });
                    if(ctrl.reRender == 0) {
                        $timeout(function () {
                            angular.element("#renderDashboardChart").click();
                        }, 500);
                        ctrl.reRender++;
                    } else {
                        angular.element("#updateDashboardLineChart").click();
                        angular.element("#updateDashboardPieChart").click();
                    }
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
            DTColumnBuilder.newColumn("code").withTitle($filter("translate")("REPORT.DOCUMENT_CODE")).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn("addendum").withTitle($filter('translate')('REPORT.ADDENDUM')).notSortable()
            .renderWith(function (data, type, full, meta) {
                return $scope.avoidXSSRender('addendum', meta.row, data);
            }),
            DTColumnBuilder.newColumn("sent_date").withTitle($filter('translate')('REPORT.SENT_DATE')),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('REPORT.DOCUMENT_STATE'))
            .renderWith(function (data, type, full, meta) {
                return Utils.getDataTableDocumentStateColumn(data);
            }),
            DTColumnBuilder.newColumn("price").withTitle($filter('translate')('REPORT.PRICE'))
            .renderWith(function (data, type, full, meta) {
                return new Intl.NumberFormat().format(data) + ' VND';
            }),
            DTColumnBuilder.newColumn("assignees").withTitle($filter('translate')('REPORT.ASSIGNEES'))
        ];

        $scope.onSearchReport = function () {
            if(!ctrl.searchReport.agency_id || !ctrl.searchReport.company_id) {
                return;
            }
            ctrl.dtInstance.rerender();
        };
        // ctrl.clickArrow = function (state) {
        //     if(state == 0){
        //         if(ctrl.searchReport.dashboardYear != ctrl.currentYear) {
        //             ctrl.searchReport.dashboardYear++;
        //             $scope.onSearchReport()
        //         }
        //     } else if(state == 1) {
        //         ctrl.searchReport.dashboardYear--;
        //         $scope.onSearchReport()
        //     }
        // }

        $scope.onViewReportDetail = function () {
            $("#turnOverReportDetail").modal("show");
        };
        $scope.stateSearch = {
            1: false,
            2: false,
            3: false,
            4: false,
            5: false,
            6: false,
            7: false,
        }
        $scope.searchByType = function (type) {
            Object.keys($scope.stateSearch).forEach(key => {
                if (type == key) {
                    $scope.stateSearch[key] = !$scope.stateSearch[key];
                } else {
                    $scope.stateSearch[key] = false;
                }
                switch (key) {
                    case '1':
                        ctrl.searchReport.completed = $scope.stateSearch[key];
                        break;
                    case '2':
                        ctrl.searchReport.in_process = $scope.stateSearch[key];
                        break;
                    case '3':
                        ctrl.searchReport.abort = $scope.stateSearch[key];
                        break;
                    case '4':
                        ctrl.searchReport.cancel = $scope.stateSearch[key];
                        break;
                    case '5':
                        ctrl.searchReport.overdue = $scope.stateSearch[key];
                        break;
                    case '6':
                        ctrl.searchReport.not_authorize = $scope.stateSearch[key];
                        break;
                    case '7':
                        ctrl.searchReport.verify_fail = $scope.stateSearch[key];
                        break;
                }
            })
            $scope.onSearchReport();
        }

        $scope.export = function () {
            ctrl.searchReport.startDate = Utils.parseDate(ctrl.searchReport.start_date);
            ctrl.searchReport.endDate = Utils.parseDate(ctrl.searchReport.end_date);
            var date_diff = Utils.dateDiffInDays(new Date(ctrl.searchReport.startDate), new Date (ctrl.searchReport.endDate));
            if (date_diff <0){
                NotificationService.error($filter("translate")("REPORT.ERROR_START_DATE_SMALLER_END_DATE"), $filter("translate")("COMMON.NOTIFICATION.ERROR"));
            } else {
                $('.loadingapp').removeClass('hidden');
                let current = Utils.formatFullDate(new Date())
                let formData = new FormData();
                formData.append('type', 2);
                formData.append('agency_id', ctrl.searchReport.agency_id);
                formData.append('company_id', ctrl.searchReport.company_id);
                formData.append('completed', ctrl.searchReport.completed ? 1 : -1);
                formData.append('in_process', ctrl.searchReport.in_process ? 1 : -1);
                formData.append('abort', ctrl.searchReport.abort ? 1 : -1);
                formData.append("cancel",ctrl.searchReport.cancel ? 1 : -1);
                formData.append("overdue", ctrl.searchReport.overdue ? 1 : -1);
                formData.append("not_authorize", ctrl.searchReport.overdue ? 1 : -1);
                formData.append("verify_fail", ctrl.searchReport.overdue ? 1 : -1);
                formData.append("start_date", ctrl.searchReport.start_date);
                formData.append("end_date", ctrl.searchReport.end_date);
                formData.append("startDate", ctrl.searchReport.startDate);
                formData.append("endDate", ctrl.searchReport.endDate);
                formData.append("document_state", ctrl.searchReport.document_state);
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
            }
        };
        $scope.onSearchDocument = function () {
           $scope.onSearchReport();
        };
    }

})();
