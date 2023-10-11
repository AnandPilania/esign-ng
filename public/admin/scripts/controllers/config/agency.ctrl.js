
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'ConfigService']).controller("AgencyCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'DaiLy', AgencyCtrl]);
    function AgencyCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, DaiLy) {

        var ctrl = this;

        ctrl.searchAgency = {
            status: "-1",
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

        ctrl.init = function() {
            DaiLy.init().then(function (response) {
                // ctrl.permission = response.data.data.permission;
                }, function(response) {
                    $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="agencyCtrl.selectAll" ng-change="agencyCtrl.toggleAll(agencyCtrl.selectAll, agencyCtrl.selected)">';

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
                searchData: ctrl.searchAgency,
                isLoading: true
            }
            DaiLy.search(params).then(function (response) {
                ctrl.lstAgency = response.data.data.data;
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
            .withOption('order', [[2, 'asc']])
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
            // DTColumnBuilder.newColumn(null).withTitle(titleHtml).notSortable().renderWith(function (data, type, full, meta) {
            //     ctrl.selected[full.id] = false;
            //     return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="agencyCtrl.selected[' + data.id + ']" ng-change="agencyCtrl.toggleOne(agencyCtrl.selected)"/>';
            // }),
            DTColumnBuilder.newColumn('agency_name').withTitle($filter('translate')('CONFIG.AGENCY.NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('agency_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('agency_phone').withTitle($filter('translate')('CONFIG.AGENCY.PHONE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('agency_phone', meta.row, data);
            }),
            DTColumnBuilder.newColumn('agency_email').withTitle($filter('translate')('CONFIG.AGENCY.EMAIL')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('agency_email', meta.row, data);
            }),
            DTColumnBuilder.newColumn('agency_address').withTitle($filter('translate')('CONFIG.AGENCY.ADDRESS')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('agency_address', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('COMMON.STATUS')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.getDataTableStatusColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                // if(data.status == 0){
                //     return Utils.renderDataTableAction(meta, "agencyCtrl.openEditAgencyModal", false, false, false, false, false, false, false, "agencyCtrl.onDeleteAgency", false);
                // } else {

                // }
                var html = '';
                if(data.status == 0){
                    html += `<a class="btn btn-outline-primary btn-icon action w-4 h-4" href="" title="Xóa" data-toggle="tooltip" data-placement="top" onclick="onDeleteAgency(${meta.row});"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><line x1=\"4\" y1=\"7\" x2=\"20\" y2=\"7\" /><line x1=\"10\" y1=\"11\" x2=\"10\" y2=\"17\" /><line x1=\"14\" y1=\"11\" x2=\"14\" y2=\"17\" /><path d=\"M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12\" /><path d=\"M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3\" /></svg></a><a class="btn btn-outline-primary btn-icon action w-4 h-4" href="" title="Khôi phục" data-toggle="tooltip" data-placement="top" onclick="changeAgencyStatus(${meta.row});"><i style="color:green;" class="fas fa-play"></i></a>`;
                } else {
                    html += `<a class="btn btn-outline-primary btn-icon action w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Xem chi tiết" onclick="openEditAgencyModal(${meta.row});"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><circle cx=\"12\" cy=\"12\" r=\"2\" /><path d=\"M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7\" /></svg></a><a class="btn btn-outline-primary btn-icon action w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Tạm ngưng" onclick="changeAgencyStatus(${meta.row});"><i style="color: orange;" class="fas fa-pause"></i></a>`;
                }
                return html;
            }),
        ];

        $scope.onSearchAgency = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.initNewAgency = function() {
            return {
                agency_name: "",
                agency_phone: "",
                agency_email: "",
                agency_address: "",
                agency_fax: "",
                status: "1",
                create_acc: true
            }
        };

        ctrl.openAddAgencyModal = function () {
            $scope.editAgency = $scope.initNewAgency();
            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/addUpdateAgency.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        $rootScope.openEditAgencyModal = function (row) {
            $scope.editAgency = angular.copy(ctrl.lstAgency[row]);
            $scope.editAgency.status = $scope.editAgency.status == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/addUpdateAgency.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        $rootScope.onDeleteAgency = function (row) {
            let agency = ctrl.lstAgency[row];
            const confirm = NotificationService.confirm($filter('translate')('CONFIG.AGENCY.DELETE_CONFIRM', {'agency_name': agency.agency_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                DaiLy.delete(agency).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchAgency();
                        delete ctrl.selected[agency.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiAgencys = function () {
            let lstAgency = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstAgency.push(id);
                    }
                }
            }
            if (lstAgency.length == 0) {
                NotificationService.error($filter('translate')('CONFIG.AGENCY.ERR_NEED_CHOOSE_AGENCY'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('CONFIG.AGENCY.DELETE_MULTI_CONFIRM', { 'agencyLength': lstAgency.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    DaiLy.deleteMulti({'lst': lstAgency}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchAgency();
                            lstAgency.forEach(e => delete ctrl.selected[e]);
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

        $rootScope.changeAgencyStatus = function(row){
            var agency = ctrl.lstAgency[row];
            agency.status = !agency.status;
            var confirm;
            if(agency.status == 0){
                confirm = NotificationService.confirm($filter('translate')('CONFIG.AGENCY.PAUSE_STATUS_CONFIRM', {'agency_name': agency.agency_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            } else {
                confirm = NotificationService.confirm($filter('translate')('CONFIG.AGENCY.ACTIVE_STATUS_CONFIRM', {'agency_name': agency.agency_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            }
            confirm.then(function () {
                DaiLy.changeStatus(agency).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchAgency();
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, DaiLy) {
        $scope.onCreateUpdateAgency = function (type) {
            let agency = $scope.editAgency;
            let errorMess = "";
            if (agency.agency_address == "") {
                errorMess += $filter('translate')('CONFIG.AGENCY.ERR_EMPTY_AGENCY_ADDRESS');
            } else if(agency.agency_address != "" && !Utils.validateVietnameseAddress(agency.agency_address)){
                errorMess += $filter('translate')('CONFIG.AGENCY.ERR_INVALID_AGENCY_ADDRESS');
            }
            if(agency.agency_phone != "" && (!Utils.isValidPhoneNumber(agency.agency_phone))){
                errorMess += $filter('translate')('CONFIG.AGENCY.ERR_INVALID_AGENCY_PHONE');
            }

            if(agency.agency_name != "" && !Utils.validateName(agency.agency_name)){
                errorMess += $filter('translate')('CONFIG.AGENCY.ERR_INVALID_AGENCY_NAME');
            }

            if(agency.agency_fax && (!/^[0-9]+$/.test(agency.agency_fax))){
                errorMess += $filter('translate')('CONFIG.AGENCY.ERR_INVALID_AGENCY_FAX');
            }
            if (agency.agency_email == "") {
                errorMess += $filter('translate')('CONFIG.AGENCY.ERR_EMPTY_AGENCY_EMAIL');
            } else if(!Utils.validateEmail(agency.agency_email) || (!agency.agency_email.endsWith('.vn') && !agency.agency_email.endsWith('.com'))){
                errorMess += $filter('translate')('CONFIG.AGENCY.ERR_INVALID_AGENCY_EMAIL');
            }

            if (errorMess == "") {
                if (!agency.id) {
                    DaiLy.create(agency).then(function (response) {
                        handleUpdateDaiLyResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                } else {
                    DaiLy.update(agency).then(function (response) {
                        handleUpdateDaiLyResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdateDaiLyResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchAgency();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editAgency = $scope.initNewAgency();
                }
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
            } else {
                NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
