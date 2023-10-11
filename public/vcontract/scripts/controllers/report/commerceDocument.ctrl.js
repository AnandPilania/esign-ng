(function () {
    "use strict";
    angular
        .module("app", ["datatables", "datatables.select"])
        .controller("CommerceDocumentReportCtrl", [
            "$scope", "$rootScope", "$compile", "$uibModal", "$http", "$state", "$window", "$timeout", "DTOptionsBuilder", "DTColumnBuilder", "$filter", "BaoCaoTaiLieuThuongMai", CommerceDocumentReportCtrl,
        ]);
    function CommerceDocumentReportCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, BaoCaoTaiLieuThuongMai) {
        var ctrl = this;

        $rootScope.document_state_label = [
            $filter("translate")("REPORT.COMPLETED_DOCUMENT"),
            $filter("translate")("REPORT.PROCESSING_DOCUMENT"),
            $filter("translate")("REPORT.DENIED_DOCUMENT"),
            $filter("translate")("REPORT.CANCELLED_DOCUMENT"),
            $filter("translate")("REPORT.EXPIRED_DOCUMENT"),
        ];

        $rootScope.document_state_label_2 = [
            $filter("translate")("REPORT.IS_COMPLETED"),
            $filter("translate")("REPORT.NOT_COMPLETED"),
        ];

        $rootScope.barChartTitle = $filter("translate")("REPORT.BAR_CHART_TITLE");
        $rootScope.doughnutChartTitle = $filter("translate")("REPORT.DOUGHNUT_CHART_TITLE");

        $rootScope.time_to_complete_label = [
            "<1 h",
            "1-6 h",
            "6-12 h",
            "12-24 h",
            "1-2 d",
            "2-7 d",
            "7-21 d",
            ">21 d",
        ];

        ctrl.searchCommerceDocument = {
            document_state: "-1",
            document_type_id: "-1",
            dc_style:"-1",
            start_date: moment().startOf("month").format("DD/MM/YYYY"),
            end_date: moment().endOf("month").format("DD/MM/YYYY"),
        };

        ctrl.lstDocumentState = [
            { id: "0", description: "REPORT.PROCESSING_DOCUMENT" },
            { id: "4", description: "REPORT.DENIED_DOCUMENT" },
            { id: "6", description: "REPORT.CANCELLED_DOCUMENT" },
            { id: "8", description: "REPORT.COMPLETED_DOCUMENT" },
            { id: "11", description: "REPORT.EXPIRED_DOCUMENT" },
        ];

        ctrl.init = (function () {
            BaoCaoTaiLieuThuongMai.init().then(
                function (response) {
                    ctrl.permission = response.data.data.permission;
                    ctrl.lstDocumentType = response.data.data.lstDocumentType;
                },
                function (response) {
                    $scope.initBadRequest(response);
                }
            );
        })();

        ctrl.dtInstance = {};

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => {
                    $scope.onSearchCommerceDocument();
                }, 300);
                return;
            }
            ctrl.searchCommerceDocument.startDate = Utils.parseDate(
                ctrl.searchCommerceDocument.start_date
            );
            ctrl.searchCommerceDocument.endDate = Utils.parseDateEnd(
                ctrl.searchCommerceDocument.end_date
            );
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
                searchData: ctrl.searchCommerceDocument,
                isLoading: true,
            };
            $rootScope.completedData = [];
            $rootScope.processingData = [];
            $rootScope.deniedData = [];
            $rootScope.cancelledData = [];
            $rootScope.expiredData = [];
            $rootScope.pieData = [0, 0, 0, 0, 0];
            $rootScope.pieData_2 = [0, 0];
            $rootScope.barData = [0, 0, 0, 0, 0, 0, 0, 0];
            BaoCaoTaiLieuThuongMai.search(params).then(
                function (response) {
                    ctrl.lstCommerceDocument = response.data.data.data;
                    ctrl.lstCommerceDocument.forEach((e) => {
                        e.sent_date = moment(e.sent_date).format("DD/MM/YYYY");
                        e.expired_date = moment(e.expired_date).format("DD/MM/YYYY");
                        e.created_at = moment(e.created_at).format("DD/MM/YYYY");
                        e.finished_date = e.finished_date != null ? moment(e.finished_date).format("DD/MM/YYYY") : "";
                    });
                    ctrl.lstCommerceReport = response.data.data.data_report;
                    ctrl.lstCommerceReport.forEach((e) => {
                        if (e.document_state == 8) {
                            $rootScope.pieData[0] += 1;
                            $rootScope.completedData.push(e);
                        } else if (e.document_state == 4) {
                            $rootScope.pieData[2] += 1;
                            $rootScope.deniedData.push(e);
                        } else if (e.document_state == 6) {
                            $rootScope.pieData[3] += 1;
                            $rootScope.cancelledData.push(e);
                        } else if (e.document_state == 11) {
                            $rootScope.pieData[4] += 1;
                            $rootScope.expiredData.push(e);
                        } else {
                            $rootScope.pieData[1] += 1;
                            $rootScope.processingData.push(e);
                        }
                        if (e.finished_date != null) {
                            $rootScope.pieData_2[0] += 1;
                            e.time_to_complete = Utils.dateDiffInHours(
                                new Date(e.created_at),
                                new Date(e.finished_date)
                            );
                        } else {
                            e.time_to_complete = 505; // > 21 day
                            $rootScope.pieData_2[1] += 1;
                        }
                        e.created_at = moment(e.created_at).format("DD/MM/YYYY");
                        e.finished_date = e.finished_date != null ? moment(e.finished_date).format("DD/MM/YYYY") : "";
                        if (e.time_to_complete <= 1) {
                            $rootScope.barData[0] += 1;
                        } else if (e.time_to_complete > 1 && e.time_to_complete <= 6) {
                            $rootScope.barData[1] += 1;
                        } else if (e.time_to_complete > 6 && e.time_to_complete <= 12) {
                            $rootScope.barData[2] += 1;
                        } else if (e.time_to_complete > 12 && e.time_to_complete <= 24) {
                            $rootScope.barData[3] += 1;
                        } else if (e.time_to_complete > 24 && e.time_to_complete <= 48 ) {
                            $rootScope.barData[4] += 1;
                        } else if (e.time_to_complete > 48 && e.time_to_complete <= 168) {
                            $rootScope.barData[5] += 1;
                        } else if (e.time_to_complete > 168 && e.time_to_complete <= 504) {
                            $rootScope.barData[6] += 1;
                        } else {
                            $rootScope.barData[7] += 1;
                        }
                    });
                    $timeout(function () {
                        angular.element("#renderCommerceChart").click();
                    }, 500);
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
                    NotificationService.error($filter("translate")(response.data.message), $filter("translate")("COMMON.NOTIFICATION.ERROR"));
                }
            );
        }

        function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // $compile(nRow)($scope);
        }

        ctrl.dtOptions = DTOptionsBuilder.newOptions()
            .withPaginationType("full_numbers")
            .withDisplayLength(20)
            .withOption("order", [[2, "desc"]])
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
            DTColumnBuilder.newColumn("code").withTitle(
                $filter("translate")("REPORT.DOCUMENT_CODE")
            ).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn("name").withTitle(
                $filter("translate")("REPORT.DOCUMENT_NAME")
            ).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn("dc_type_name").withTitle(
                $filter("translate")("REPORT.DOCUMENT_TYPE")
            ).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('dc_type_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null)
            .withTitle($filter('translate')('REPORT.DOCUMENT_STYLE.DEFAULT'))
            .notSortable()
            .renderWith(function (data, type, full, meta) {
                if(data.dc_style == 1){
                    return $filter('translate')('REPORT.DOCUMENT_STYLE.BUY_IN');
                } else if(data.dc_style == 2) {
                    return $filter('translate')('REPORT.DOCUMENT_STYLE.SELL_OUT');
                } else {
                    return $filter('translate')('REPORT.DOCUMENT_STYLE.ELSE');
                }
            }),
            DTColumnBuilder.newColumn("created_at").withTitle(
                $filter("translate")("REPORT.DOCUMENT_CREATED_TIME")
            ),
            DTColumnBuilder.newColumn("expired_date").withTitle(
                $filter("translate")("REPORT.DOCUMENT_EXPIRED_TIME")
            ),
            DTColumnBuilder.newColumn("finished_date").withTitle(
                $filter("translate")("REPORT.DOCUMENT_COMPLETED_TIME")
            ),
            DTColumnBuilder.newColumn(null)
                .withTitle($filter("translate")("REPORT.DOCUMENT_STATE"))
                .renderWith(function (data, type, full, meta) {
                    return $filter("translate")(`REPORT.DOCUMENT_STATE_${data.document_state}`);
                }),
        ];

        $scope.onSearchCommerceDocument = function () {
            ctrl.dtInstance.rerender();
        };

        $scope.onViewReportDetail = function () {
            $("#reportCommerceDetail").modal("show");
        };

        $scope.export = function () {
            ctrl.searchCommerceDocument.startDate = Utils.parseDate(ctrl.searchCommerceDocument.start_date);
            ctrl.searchCommerceDocument.endDate = Utils.parseDate(ctrl.searchCommerceDocument.end_date);
            var date_diff = Utils.dateDiffInDays(new Date(ctrl.searchCommerceDocument.startDate),new Date(ctrl.searchCommerceDocument.endDate));
            if (date_diff < 0) {
                NotificationService.error($filter("translate")("REPORT.ERROR_START_DATE_SMALLER_END_DATE"), $filter("translate")("COMMON.NOTIFICATION.ERROR"));
            } else if (date_diff > 30) {
                NotificationService.error($filter("translate")("REPORT.ERROR_DATE_RANGE_OVER_30_DAYS"), $filter("translate")("COMMON.NOTIFICATION.ERROR"));
            } else {
                $(".loadingapp").removeClass("hidden");
                let formData = new FormData();
                formData.append("startDate",ctrl.searchCommerceDocument.startDate);
                formData.append("endDate", ctrl.searchCommerceDocument.endDate);
                formData.append("document_state", ctrl.searchCommerceDocument.document_state);
                formData.append("document_type_id", ctrl.searchCommerceDocument.document_type_id);
                formData.append("dc_style", ctrl.searchCommerceDocument.dc_style);
                BaoCaoTaiLieuThuongMai.export(formData).then(
                    function (response) {
                        $(".loadingapp").addClass("hidden");
                        let fileName = "BaoCaoTaiLieuThuongMai_" + ctrl.searchCommerceDocument.start_date + "_" + ctrl.searchCommerceDocument.end_date + ".xlsx";
                        Utils.downloadFormBinary(response, fileName);
                    },
                    function (response) {
                        $(".loadingapp").addClass("hidden");
                        $scope.initBadRequest(response);
                    }
                );
            }
        };
    }
})();
