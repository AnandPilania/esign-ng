
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'ConfigService']).controller("ServiceConfigCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'GoiCuoc', ServiceConfigCtrl]);
    function ServiceConfigCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, GoiCuoc) {

        var ctrl = this;

        ctrl.searchServiceConfig = {
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
            GoiCuoc.init().then(function (response) {
                // ctrl.permission = response.data.data.permission;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="serviceConfigCtrl.selectAll" ng-change="serviceConfigCtrl.toggleAll(serviceConfigCtrl.selectAll, serviceConfigCtrl.selected)">';

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
                searchData: ctrl.searchServiceConfig,
                isLoading: true
            }
            GoiCuoc.search(params).then(function (response) {
                ctrl.lstServiceConfig = response.data.data.data;
                ctrl.lstServiceConfig.forEach(e => {
                    e.created_at = moment(e.created_at).format("DD/MM/YYYY HH:mm");
                    e.updated_at = moment(e.updated_at).format("DD/MM/YYYY HH:mm");
                })
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
            //     return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="serviceConfigCtrl.selected[' + data.id + ']" ng-change="serviceConfigCtrl.toggleOne(serviceConfigCtrl.selected)"/>';
            // }),
            DTColumnBuilder.newColumn('service_name').withTitle($filter('translate')('CONFIG.SERVICE_CONFIG.NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('service_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('service_code').withTitle($filter('translate')('CONFIG.SERVICE_CONFIG.CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('service_code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('created_at').withTitle($filter('translate')('CONFIG.SERVICE_CONFIG.CREATED_AT')),
            DTColumnBuilder.newColumn('updated_at').withTitle($filter('translate')('CONFIG.SERVICE_CONFIG.UPDATED_AT')),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('COMMON.STATUS')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.getDataTableStatusColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                // if(data.status == 0){
                //     return Utils.renderDataTableAction(meta, "serviceConfigCtrl.openEditServiceConfigModal", false, false, false, false, false, false, false, "serviceConfigCtrl.onDeleteServiceConfig", false);
                // } else {

                // }
                var html = '<div class="action-button-group">';
                    html += `<a class="btn btn-outline-primary btn-icon action w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Xem chi tiết" onclick="openEditServiceConfigModal(${meta.row});"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><circle cx=\"12\" cy=\"12\" r=\"2\" /><path d=\"M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7\" /></svg></a>&nbsp;`;
                if(data.status == 0){
                    html += `<a ng-if="loginUser.role_id == 1" class="btn btn-outline-primary btn-icon action w-4 h-4" href="" title="Xóa" data-toggle="tooltip" data-placement="top" onclick="onDeleteServiceConfig(${meta.row});"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><line x1=\"4\" y1=\"7\" x2=\"20\" y2=\"7\" /><line x1=\"10\" y1=\"11\" x2=\"10\" y2=\"17\" /><line x1=\"14\" y1=\"11\" x2=\"14\" y2=\"17\" /><path d=\"M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12\" /><path d=\"M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3\" /></svg></a>&nbsp;
                            <a ng-if="loginUser.role_id == 1" class="btn btn-outline-primary btn-icon action w-4 h-4" href="" title="Khôi phục" data-toggle="tooltip" data-placement="top" onclick="changeServiceConfigStatus(${meta.row});"><i style="color:green;" class="fas fa-play"></i></a>`;
                } else {
                    html += `<a ng-if="loginUser.role_id == 1" class="btn btn-outline-primary btn-icon action w-4 h-4" href="" data-toggle="tooltip" data-placement="top" title="Tạm ngưng" onclick="changeServiceConfigStatus(${meta.row});"><i style="color: orange;" class="fas fa-pause"></i></a>`;
                }
                html += '</div>';
                return html;
            }),
        ];

        $scope.onSearchServiceConfig = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.initNewServiceConfig = function() {
            return {
                service_name: "",
                service_code: "",
                description: "",
                service_type: "-1",
                status: true,
                unlimited: true,
                price: "",
                quantity: "",
                expires_time: "",
                service_detail: []
            }
        };

        ctrl.openAddServiceConfigModal = function () {
            $scope.editServiceConfig = $scope.initNewServiceConfig();
            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/addUpdateServiceConfig.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        $rootScope.openEditServiceConfigModal = function (row) {
            $scope.editServiceConfig = angular.copy(ctrl.lstServiceConfig[row]);
            $scope.editServiceConfig.status = $scope.editServiceConfig.status == 1;
            $scope.editServiceConfig.service_type = "" + $scope.editServiceConfig.service_type;
            GoiCuoc.getDetail($scope.editServiceConfig).then(function(response){
                if (response.data.success) {
                    $scope.editServiceConfig.service_detail = response.data.data.lstDetail;
                    $uibModal.open({
                        animation: true,
                        templateUrl: 'admin/views/modal/addUpdateServiceConfig.html',
                        windowClass: "fade show modal-blur",
                        size: 'lg modal-dialog-centered',
                        backdrop: 'static',
                        backdropClass: 'show',
                        keyboard: false,
                        controller: ModalInstanceCtrl,
                        scope: $scope
                    });
                }
            }, function(response){
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            })
        }

        $rootScope.onDeleteServiceConfig = function (row) {
            let serviceConfig = ctrl.lstServiceConfig[row];
            const confirm = NotificationService.confirm($filter('translate')('CONFIG.SERVICE_CONFIG.DELETE_CONFIRM', {'service_name': serviceConfig.service_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                GoiCuoc.delete(serviceConfig).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchServiceConfig();
                        delete ctrl.selected[serviceConfig.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiServiceConfigs = function () {
            let lstServiceConfig = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstServiceConfig.push(id);
                    }
                }
            }
            if (lstServiceConfig.length == 0) {
                NotificationService.error($filter('translate')('CONFIG.SERVICE_CONFIG.ERR_NEED_CHOOSE_SERVICE_CONFIG'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('CONFIG.SERVICE_CONFIG.DELETE_MULTI_CONFIRM', { 'serviceConfigLength': lstServiceConfig.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    GoiCuoc.deleteMulti({'lst': lstServiceConfig}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchServiceConfig();
                            lstServiceConfig.forEach(e => delete ctrl.selected[e]);
                            NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        } else {
                            NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        }
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                }).catch(function (err) {
                });
            }

        }

        $rootScope.changeServiceConfigStatus = function(row){
            var serviceConfig = ctrl.lstServiceConfig[row];
            serviceConfig.status = !serviceConfig.status;
            var confirm;
            if(serviceConfig.status == 0){
                confirm = NotificationService.confirm($filter('translate')('CONFIG.SERVICE_CONFIG.PAUSE_STATUS_CONFIRM', {'service_name': serviceConfig.service_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            } else {
                confirm = NotificationService.confirm($filter('translate')('CONFIG.SERVICE_CONFIG.ACTIVE_STATUS_CONFIRM', {'service_name': serviceConfig.service_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            }
            confirm.then(function () {
                GoiCuoc.changeStatus(serviceConfig).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchServiceConfig();
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            }).catch(function (err) {
            });
        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, GoiCuoc) {
        $scope.onCreateUpdateServiceConfig = function (type) {
            let serviceConfig = $scope.editServiceConfig;
            let errorMess = "";
            if (serviceConfig.service_code == "") {
                errorMess += $filter('translate')('CONFIG.SERVICE_CONFIG.ERR_EMPTY_SERVICE_CONFIG_CODE');
            } else if(serviceConfig.service_code != "" && !Utils.validateCode(serviceConfig.service_code)){
                errorMess += $filter('translate')('CONFIG.SERVICE_CONFIG.ERR_INVALID_SERVICE_CONFIG_CODE');
            }
            if(serviceConfig.description != "" && !Utils.validateVietnameseAddress(serviceConfig.description)){
                errorMess += $filter('translate')('CONFIG.SERVICE_CONFIG.ERR_INVALID_SERVICE_CONFIG_DESCRIPTION');
            }
            if (serviceConfig.service_name == "") {
                errorMess += $filter('translate')('CONFIG.SERVICE_CONFIG.ERR_EMPTY_SERVICE_CONFIG_NAME');
            } else if(serviceConfig.service_name != "" && !Utils.validateName(serviceConfig.service_name)){
                errorMess += $filter('translate')('CONFIG.SERVICE_CONFIG.ERR_INVALID_SERVICE_CONFIG_NAME');
            }

            if (serviceConfig.service_type == "-1" || serviceConfig.service_type == "") {
                errorMess += $filter('translate')('CONFIG.SERVICE_CONFIG.ERR_EMPTY_SERVICE_CONFIG_TYPE');
            }
            if (serviceConfig.price == "") {
                errorMess += $filter('translate')('CONFIG.SERVICE_CONFIG.ERR_EMPTY_SERVICE_CONFIG_PRICE');
            }
            // if (serviceConf == "") {
            //     errorMess += $filter('translate')('CONFIG.SERVICE_CONFIG.ERR_EMPTY_SERVICE_CONFIG_PRICE');
            // }
            if (errorMess == "") {
                if (!serviceConfig.id) {
                    GoiCuoc.create(serviceConfig).then(function (response) {
                        handleUpdateGoiCuocResponse(response, type);
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                } else {
                    GoiCuoc.update(serviceConfig).then(function (response) {
                        handleUpdateGoiCuocResponse(response, type);
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdateGoiCuocResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchServiceConfig();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editServiceConfig = $scope.initNewServiceConfig();
                }
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
            } else {
                $scope.initBadRequest(response);
            }
        }

        $scope.addServiceConfigDetail = function () {
            $scope.editServiceConfig.service_detail.push({
                "edit": true,
                "service_config_id": $scope.editServiceConfig.id ? $scope.editServiceConfig.id : null,
                "from": "",
                "to": "",
                "fee": ""
            })
        }

        $scope.saveServiceConfigDetail = function(data){
            GoiCuoc.saveDetail(data).then(function(response){
                if (response.data.success) {
                    delete data.edit;
                    data.id = response.data.data.id;
                    data.updated_at = response.data.data.updated_at;
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                }
            }, function (response) {
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            });
        }

        $scope.editServiceConfigDetail = function (data) {
            data.edit = true;
            data.service_config_id = angular.copy($scope.currentService.id);
            return data;
        }

        $scope.deleteServiceConfigDetail = function (index) {
            let serviceConfigDetail = $scope.editServiceConfig.service_detail[index];
            const confirm = NotificationService.confirm($filter('translate')('CONFIG.SERVICE_CONFIG.DELETE_DETAIL_CONFIRM'), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                GoiCuoc.deleteDetail(serviceConfigDetail).then(function (response) {
                    if (response.data.success) {
                        $scope.editServiceConfig.service_detail.splice(index, 1);
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            }).catch(function (err) {
            });
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
