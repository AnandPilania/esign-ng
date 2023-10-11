
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select']).controller("SendSmsCommerceCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'QuanLyGuiSmsThuongMai', SendSmsCommerceCtrl]);
    function SendSmsCommerceCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, QuanLyGuiSmsThuongMai) {

        var ctrl = this;

        ctrl.searchSendSms = {
            document_type_id: "-1",
            dc_style: "-1",
            status: "-1",
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
            QuanLyGuiSmsThuongMai.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.lstDocumentType = response.data.data.lstDocumentType;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="sendSmsCtrl.selectAll" ng-change="sendSmsCtrl.toggleAll(sendSmsCtrl.selectAll, sendSmsCtrl.selected)">';
        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchSendSms() }, 300);
                return;
            }
            ctrl.searchSendSms.startDate = Utils.parseDate(ctrl.searchSendSms.start_date);
            ctrl.searchSendSms.endDate = Utils.parseDateEnd(ctrl.searchSendSms.end_date);
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
                searchData: ctrl.searchSendSms,
                isLoading: true
            }
            QuanLyGuiSmsThuongMai.search(params).then(function (response) {
                console.log(response.data.data.data)
                ctrl.lstSendSms = response.data.data.data;
                ctrl.lstSendSms.forEach(e => {
                    e.sent_date = Utils.formatDateNoTime(new Date(e.sent_date));
                    e.created_at = Utils.formatDateNoTime(new Date(e.created_at));
                });
                ctrl.selectAll = false;
                ctrl.selected = {};
                fnCallback(response.data.data);
            }, function (response) {
                var records = {
                    'draw': draw,
                    'recordsTotal': 0,
                    'recordsFiltered': 0,
                    'data': []
                };
                fnCallback(records);
                $scope.initBadRequest(response);
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="sendSmsCtrl.selected[' + data.id + ']" ng-change="sendSmsCtrl.toggleOne(sendSmsCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('created_at').withTitle($filter('translate')('COMMERCE.SEND_SMS.SEND_SMS_SENDER_DATE')),
            DTColumnBuilder.newColumn('sender_name').withTitle($filter('translate')('COMMERCE.SEND_SMS.SEND_SMS_SENDER_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('sender_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('receiver_name').withTitle($filter('translate')('COMMERCE.SEND_SMS.SEND_SMS_RECEIVER_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('receiver_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('receiver_phone').withTitle($filter('translate')('COMMERCE.SEND_SMS.SEND_SMS_RECEIVER_PHONE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('receiver_phone', meta.row, data);
            }),
            DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('COMMERCE.SEND_SMS.SEND_SMS_DOCUMENT_SENT_DATE')),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('COMMERCE.SEND_SMS.SEND_SMS_DOCUMENT_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('COMMERCE.SEND_SMS.SEND_SMS_DOCUMENT_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('COMMON.STATUS')).renderWith(function(data, type, full, meta){
                switch(data.status){
                    case 0:
                        return "<span class='badge bg-secondary me-1'></span> Chưa gửi ";
                    case 2:
                        return "<span class='badge bg-red me-1'></span> Gửi lỗi ";
                    case 1:
                        return "<span class='badge bg-green me-1'></span> Thành công ";
                    default:
                        return ""
                }
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction2(meta, "sendSmsCtrl.openDetailSmsModal", "sendSmsCtrl.openEditSendSmsModal",  "sendSmsCtrl.onSendSms", false, "sendSmsCtrl.onDeleteSendSms", false);
            }),
        ];

        $scope.onSearchSendSms = function () {
            ctrl.dtInstance.rerender();
        }
        ctrl.openDetailSmsModal = function (row) {
            $scope.detail = ctrl.lstSendSms[row];
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/sendEmailSmsDetail.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }
        ctrl.openEditSendSmsModal = function (row) {
            let document = ctrl.lstSendSms[row];
            $state.go("index.commerce.viewDocument", {docId: document.doc_id});
        }

        ctrl.onSendSms = function (row) {
            let sms = ctrl.lstSendSms[row];
            const confirm = NotificationService.confirm($filter('translate')('COMMERCE.SEND_SMS.SEND_CONFIRM', {'sendSmsCode': sms.code, 'sendSmsReceiver': sms.receiver_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                QuanLyGuiSmsThuongMai.send({convId: sms.id, isLoading: true}).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchSendSms();
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onSendMultiSms = function () {
            let lstSendSms = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstSendSms.push(id);
                    }
                }
            }
            if (lstSendSms.length == 0) {
                NotificationService.error($filter('translate')('COMMERCE.SEND_SMS.ERR_NEED_CHOOSE_SEND_SMS'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('COMMERCE.SEND_SMS.SEND_MULTI_CONFIRM', { 'sendSmsLength': lstSendSms.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    QuanLyGuiSmsThuongMai.sendMulti({'lst': lstSendSms, isLoading: true}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchSendSms();
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

        ctrl.onDeleteSendSms = function (row) {
            let sms = ctrl.lstSendSms[row];
            const confirm = NotificationService.confirm($filter('translate')('COMMERCE.SEND_SMS.DELETE_CONFIRM', {'sendSmsCode': sms.code}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                QuanLyGuiSmsThuongMai.delete({convId: sms.id, isLoading: true}).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchSendSms();
                        delete ctrl.selected[sms.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiSendSms = function () {
            let lstSendSms = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstSendSms.push(id);
                    }
                }
            }
            if (lstSendSms.length == 0) {
                NotificationService.error($filter('translate')('COMMERCE.SEND_SMS.ERR_NEED_CHOOSE_SEND_SMS'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('COMMERCE.SEND_SMS.DELETE_MULTI_CONFIRM', { 'sendSmsLength': lstSendSms.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    QuanLyGuiSmsThuongMai.deleteMulti({'lst': lstSendSms, isLoading: true}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchSendSms();
                            lstSendSms.forEach(e => delete ctrl.selected[e]);
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
    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, QuanLyGuiEmail) {
        let detail = $scope.detail;
        $scope.getContent = function () {
            var content = '<div style="white-space:normal; word-break:break-all;">' + detail.content + '</div>'
            document.getElementById("content").innerHTML = content
        }
        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
