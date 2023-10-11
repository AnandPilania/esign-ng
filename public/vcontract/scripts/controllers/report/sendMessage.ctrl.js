
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select']).controller("SendMessageReportCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'BaoCaoGuiTin', SendMessageReportCtrl]);
    function SendMessageReportCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, BaoCaoGuiTin) {

        var ctrl = this;

        ctrl.searchSendMessage = {
            document_group_id: "1",
            document_type_id: "-1",
            dc_style:"-1",
            start_date: moment().startOf('month').format('DD/MM/YYYY'),
            end_date: moment().endOf('month').format('DD/MM/YYYY')
        }

        $rootScope.sms_status_label = [
            $filter("translate")("REPORT.SMS_SENT"),
            $filter("translate")("REPORT.SMS_WAIT_TO_SEND"),
            $filter("translate")("REPORT.SMS_SENT_FAILED"),
        ];

        $rootScope.email_status_label = [
            $filter("translate")("REPORT.EMAIL_SENT"),
            $filter("translate")("REPORT.EMAIL_WAIT_TO_SEND"),
            $filter("translate")("REPORT.EMAIL_SENT_FAILED"),
        ];

        ctrl.init = function() {
            BaoCaoGuiTin.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.lstDocumentType = response.data.data.lstDocumentType;
                ctrl.lstDocumentGroup = response.data.data.lstDocumentGroup;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchSendMessage() }, 300);
                return;
            }
            ctrl.searchSendMessage.startDate = Utils.parseDate(ctrl.searchSendMessage.start_date);
            ctrl.searchSendMessage.endDate = Utils.parseDateEnd(ctrl.searchSendMessage.end_date);
            ctrl.lstDocumentTypeTmp = angular.copy(ctrl.lstDocumentType);
            ctrl.lstDocumentTypeSearch = ctrl.lstDocumentTypeTmp.filter(e => e.document_group_id == ctrl.searchSendMessage.document_group_id);
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
                searchData: ctrl.searchSendMessage,
                isLoading: true
            }
            $rootScope.pieData_sms = [0, 0, 0];
            $rootScope.pieData_email = [0, 0, 0];
            BaoCaoGuiTin.search(params).then(function (response) {
                ctrl.lstSendMessage = response.data.data.data;
                $rootScope.pieData_email[1] = response.data.data.total_email_not_send;
                $rootScope.pieData_email[0] = response.data.data.total_email_success;
                $rootScope.pieData_email[2] = response.data.data.total_email_failed;
                $rootScope.pieData_sms[1] = response.data.data.total_sms_not_send;
                $rootScope.pieData_sms[0] = response.data.data.total_sms_success;
                $rootScope.pieData_sms[2] = response.data.data.total_sms_failed;
                // ctrl.lstSendMessage.forEach(e => {
                //     e.sent_date = Utils.formatDateNoTime(new Date(e.sent_date));
                //     e.created_at = Utils.formatDateNoTime(new Date(e.created_at));
                //     if(e.send_type == 1){
                //         if(e.status == 0){
                //             $rootScope.pieData_email[1] += 1;
                //         } else if(e.status == 1){
                //             $rootScope.pieData_email[0] += 1;
                //         } else if(e.status == 2){
                //             $rootScope.pieData_email[2] += 1;
                //         }
                //     } else {
                //         if(e.status == 0){
                //             $rootScope.pieData_sms[1] += 1;
                //         } else if(e.status == 1){
                //             $rootScope.pieData_sms[0] += 1;
                //         } else if(e.status == 2){
                //             $rootScope.pieData_sms[2] += 1;
                //         }
                //     }
                // });
                $timeout(function () {
                    angular.element("#renderMessageChart").click();
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
            DTColumnBuilder.newColumn('created_at').withTitle($filter('translate')('INTERNAL.SEND_SMS.SEND_SMS_SENDER_DATE')),
            DTColumnBuilder.newColumn('sender_name').withTitle($filter('translate')('INTERNAL.SEND_SMS.SEND_SMS_SENDER_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('sender_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('receiver_name').withTitle($filter('translate')('INTERNAL.SEND_SMS.SEND_SMS_RECEIVER_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('receiver_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('receiver_phone').withTitle($filter('translate')('INTERNAL.SEND_SMS.SEND_SMS_RECEIVER_PHONE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('receiver_phone', meta.row, data);
            }),
            DTColumnBuilder.newColumn('receiver_email').withTitle($filter('translate')('INTERNAL.SEND_EMAIL.SEND_EMAIL_RECEIVER_EMAIL')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('receiver_email', meta.row, data);
            }),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('INTERNAL.SEND_SMS.SEND_SMS_DOCUMENT_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('INTERNAL.SEND_SMS.SEND_SMS_DOCUMENT_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('COMMON.STATUS')).renderWith(function(data, type, full, meta){
                switch(data.status){
                    case 0:
                        return "<span class='badge bg-secondary me-1'></span> " + $filter('translate')('REPORT.SEND_SMS_STATUS_0');
                    case 2:
                        return "<span class='badge bg-red me-1'></span> " + $filter('translate')('REPORT.SEND_SMS_STATUS_1');
                    case 1:
                        return "<span class='badge bg-green me-1'></span> " + $filter('translate')('REPORT.SEND_SMS_STATUS_2');
                    default:
                        return ""
                }
            }),
        ];

        $scope.onSearchSendMessage = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.onSearchDocumentGroupChange = function(){
            ctrl.searchSendMessage.document_type_id = "-1";
            ctrl.searchSendMessage.dc_style = "-1";
            $scope.onSearchSendMessage();
        }

        $scope.onViewReportDetail = function () {
            $("#reportMessageDetail").modal("show");
        };

        $scope.export = function(){
            ctrl.searchSendMessage.startDate = Utils.parseDate(ctrl.searchSendMessage.start_date);
            ctrl.searchSendMessage.endDate = Utils.parseDate(ctrl.searchSendMessage.end_date);
            var date_diff = Utils.dateDiffInDays(new Date(ctrl.searchSendMessage.startDate), new Date(ctrl.searchSendMessage.endDate));
            if(date_diff < 0){
                NotificationService.error($filter('translate')('REPORT.ERROR_START_DATE_SMALLER_END_DATE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else if(date_diff > 30){
                NotificationService.error($filter('translate')('REPORT.ERROR_DATE_RANGE_OVER_30_DAYS'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                $('.loadingapp').removeClass('hidden');
                let formData = new FormData();
                formData.append('startDate', ctrl.searchSendMessage.startDate);
                formData.append('endDate', ctrl.searchSendMessage.endDate);
                formData.append('keyword', ctrl.searchSendMessage.keyword);
                formData.append('document_group_id', ctrl.searchSendMessage.document_group_id);
                formData.append('document_type_id', ctrl.searchSendMessage.document_type_id);
                formData.append("dc_style", ctrl.searchSendMessage.dc_style);
                BaoCaoGuiTin.export(formData).then(function(response){
                    $('.loadingapp').addClass('hidden');
                    let fileName = "BaoCaoGuiTin_" + ctrl.searchSendMessage.start_date + "_" + ctrl.searchSendMessage.end_date + ".xlsx";
                    Utils.downloadFormBinary(response, fileName);
                }, function (response) {
                    $('.loadingapp').addClass('hidden');
                    $scope.initBadRequest(response);
                });
            }
        }
    }
})();
