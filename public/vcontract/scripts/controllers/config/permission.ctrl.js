
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'ConfigService']).controller("PermissionCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'PhanQuyen', PermissionCtrl]);
    function PermissionCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, PhanQuyen) {

        var ctrl = this;

        ctrl.searchRole = {
            keyword: "",
            status: "-1",
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

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="permissionCtrl.selectAll" ng-change="permissionCtrl.toggleAll(permissionCtrl.selectAll, permissionCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchRole() }, 300);
                return;
            }
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
                searchData: ctrl.searchRole,
                isLoading: true
            }
            PhanQuyen.search(params).then(function (response) {
                ctrl.lstRole = response.data.data.data;
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
            DTColumnBuilder.newColumn(null).withTitle(titleHtml).notSortable().renderWith(function (data, type, full, meta) {
                ctrl.selected[full.id] = false;
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="permissionCtrl.selected[' + data.id + ']" ng-change="permissionCtrl.toggleOne(permissionCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('role_name').withTitle($filter('translate')('CONFIG.PERMISSION.PERMISSION_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('role_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('note').withTitle($filter('translate')('COMMON.NOTE')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('note', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('COMMON.USING')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.getDataTableStatusColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "permissionCtrl.openPermissionDetail", false, false, false, false, false, false, false, "permissionCtrl.onDeletePermission", false);
            }),
        ];

        ctrl.init = function() {
            PhanQuyen.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
            }, function(response) {
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                $state.go("index.dashboard");
            });
        }();

        $scope.onSearchRole = function () {
            ctrl.dtInstance.rerender();
        }

        ctrl.openAddPermissionDetail = function () {
            $state.go("index.config.addPermission");
        }

        ctrl.openPermissionDetail = function (row) {
            $state.go("index.config.editPermission", {'roleId': ctrl.lstRole[row].id});
        }

        ctrl.onDeletePermission = function (row) {
            let role = ctrl.lstRole[row];
            const confirm = NotificationService.confirm($filter('translate')('CONFIG.PERMISSION.DELETE_CONFIRM', {'roleName': role.role_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                PhanQuyen.delete(role).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchRole();
                        delete ctrl.selected[role.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiPermissions = function () {
            let lstRoles = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstRoles.push(id);
                    }
                }
            }
            if (lstRoles.length == 0) {
                NotificationService.error($filter('translate')('CONFIG.PERMISSION.ERR_NEED_CHOOSE_PERMISSION'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('CONFIG.PERMISSION.DELETE_MULTI_CONFIRM', { 'roleLength': lstRoles.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    PhanQuyen.deleteMulti({'lst': lstRoles}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchRole();
                            lstRoles.forEach(e => delete ctrl.selected[e]);
                            NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        } else {
                            NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        }
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }).catch(function (err) {
                });
            }

        }
    }
})();
