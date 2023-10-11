
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select']).controller("SendEmailCommerceCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'QuanLyGuiEmailThuongMai', SendEmailCommerceCtrl]);
    function SendEmailCommerceCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, QuanLyGuiEmailThuongMai) {

        var ctrl = this;

        ctrl.searchSendEmail = {
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
            QuanLyGuiEmailThuongMai.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.lstDocumentType = response.data.data.lstDocumentType;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="sendEmailCtrl.selectAll" ng-change="sendEmailCtrl.toggleAll(sendEmailCtrl.selectAll, sendEmailCtrl.selected)">';
        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchSendEmail() }, 300);
                return;
            }
            ctrl.searchSendEmail.startDate = Utils.parseDate(ctrl.searchSendEmail.start_date);
            ctrl.searchSendEmail.endDate = Utils.parseDateEnd(ctrl.searchSendEmail.end_date);
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
                searchData: ctrl.searchSendEmail,
                isLoading: true
            }
            QuanLyGuiEmailThuongMai.search(params).then(function (response) {
                ctrl.lstSendEmail = response.data.data.data;
                ctrl.lstSendEmail.forEach(e => {
                    e.sent_date = Utils.formatDateNoTime(new Date(e.sent_date));
                    e.created_at = Utils.formatDate(new Date(e.created_at));
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="sendEmailCtrl.selected[' + data.id + ']" ng-change="sendEmailCtrl.toggleOne(sendEmailCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('created_at').withTitle($filter('translate')('COMMERCE.SEND_EMAIL.SEND_EMAIL_SENDER_DATE')),
            DTColumnBuilder.newColumn('sender_name').withTitle($filter('translate')('COMMERCE.SEND_EMAIL.SEND_EMAIL_SENDER_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('sender_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('receiver_name').withTitle($filter('translate')('COMMERCE.SEND_EMAIL.SEND_EMAIL_RECEIVER_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('receiver_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('receiver_email').withTitle($filter('translate')('COMMERCE.SEND_EMAIL.SEND_EMAIL_RECEIVER_EMAIL')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('receiver_email', meta.row, data);
            }),
            DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('COMMERCE.SEND_EMAIL.SEND_EMAIL_DOCUMENT_SENT_DATE')),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('COMMERCE.SEND_EMAIL.SEND_EMAIL_DOCUMENT_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('COMMERCE.SEND_EMAIL.SEND_EMAIL_DOCUMENT_NAME')).renderWith(function (data, type, full, meta){
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
                return Utils.renderDataTableAction2(meta, "sendEmailCtrl.openDetailEmailModal", "sendEmailCtrl.openEditSendEmailModal",  "sendEmailCtrl.onSendEmail", false, "sendEmailCtrl.onDeleteSendEmail", false);
            }),
        ];

        $scope.onSearchSendEmail = function () {
            ctrl.dtInstance.rerender();
        }
        ctrl.openDetailEmailModal = function (row) {
            $scope.detail = ctrl.lstSendEmail[row];
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
        ctrl.openEditSendEmailModal = function (row) {
            let document = ctrl.lstSendEmail[row];
            $state.go("index.commerce.viewDocument", {docId: document.doc_id});
        }

        ctrl.onSendEmail = function (row) {
            let email = ctrl.lstSendEmail[row];
            const confirm = NotificationService.confirm($filter('translate')('COMMERCE.SEND_EMAIL.SEND_CONFIRM', {'sendEmailCode': email.code, 'sendEmailReceiver': email.receiver_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                QuanLyGuiEmailThuongMai.send({convId: email.id, isLoading: true}).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchSendEmail();
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onSendMultiEmail = function () {
            let lstSendEmail = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstSendEmail.push(id);
                    }
                }
            }
            if (lstSendEmail.length == 0) {
                NotificationService.error($filter('translate')('COMMERCE.SEND_EMAIL.ERR_NEED_CHOOSE_SEND_EMAIL'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('COMMERCE.SEND_EMAIL.SEND_MULTI_CONFIRM', { 'sendEmailLength': lstSendEmail.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    QuanLyGuiEmailThuongMai.sendMulti({'lst': lstSendEmail, isLoading: true}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchSendEmail();
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

        ctrl.onDeleteSendEmail = function (row) {
            let email = ctrl.lstSendEmail[row];
            const confirm = NotificationService.confirm($filter('translate')('COMMERCE.SEND_EMAIL.DELETE_CONFIRM', {'sendEmailCode': email.code}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                QuanLyGuiEmailThuongMai.delete({convId: email.id, isLoading: true}).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchSendEmail();
                        delete ctrl.selected[email.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiSendEmail = function () {
            let lstSendEmail = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstSendEmail.push(id);
                    }
                }
            }
            if (lstSendEmail.length == 0) {
                NotificationService.error($filter('translate')('COMMERCE.SEND_EMAIL.ERR_NEED_CHOOSE_SEND_EMAIL'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('COMMERCE.SEND_EMAIL.DELETE_MULTI_CONFIRM', { 'sendEmailLength': lstSendEmail.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    QuanLyGuiEmailThuongMai.deleteMulti({'lst': lstSendEmail, isLoading: true}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchSendEmail();
                            lstSendEmail.forEach(e => delete ctrl.selected[e]);
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
