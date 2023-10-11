
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'UtilitiesSrvc']).controller("DepartmentCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'PhongBan', DepartmentCtrl]);
    function DepartmentCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, PhongBan) {

        var ctrl = this;

        ctrl.searchDepartment = {
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
            PhongBan.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="departmentCtrl.selectAll" ng-change="departmentCtrl.toggleAll(departmentCtrl.selectAll, departmentCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchDepartment() }, 300);
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
                searchData: ctrl.searchDepartment,
                isLoading: true
            }
            PhongBan.search(params).then(function (response) {
                ctrl.lstDepartment = response.data.data.data;
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="departmentCtrl.selected[' + data.id + ']" ng-change="departmentCtrl.toggleOne(departmentCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('department_code').withTitle($filter('translate')('UTILITES.DEPARTMENT.DEPARTMENT_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('department_code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('UTILITES.DEPARTMENT.DEPARTMENT_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
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
                return Utils.renderDataTableAction(meta, "departmentCtrl.openEditDepartmentModal", false, false, false, false, false, false, false, "departmentCtrl.onDeleteDepartment", false);
            }),
        ];

        $scope.onSearchDepartment = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.initNewDepartment = function() {
            return {
                name: "",
                department_code: "",
                note: "",
                status: true
            }
        };

        ctrl.openAddDepartmentModal = function () {
            $scope.editDepartment = $scope.initNewDepartment();
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateDepartment.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openEditDepartmentModal = function (row) {
            $scope.editDepartment = angular.copy(ctrl.lstDepartment[row]);
            $scope.editDepartment.status = $scope.editDepartment.status == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateDepartment.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.onDeleteDepartment = function (row) {
            let department = ctrl.lstDepartment[row];
            const confirm = NotificationService.confirm($filter('translate')('UTILITES.DEPARTMENT.DELETE_CONFIRM', {'departmentName': department.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                PhongBan.delete(department).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchDepartment();
                        delete ctrl.selected[department.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiDepartments = function () {
            let lstDepartment = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstDepartment.push(id);
                    }
                }
            }
            if (lstDepartment.length == 0) {
                NotificationService.error($filter('translate')('UTILITES.DEPARTMENT.ERR_NEED_CHOOSE_DEPARTMENT'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('UTILITES.DEPARTMENT.DELETE_MULTI_CONFIRM', { 'departmentLength': lstDepartment.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    PhongBan.deleteMulti({'lst': lstDepartment}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchDepartment();
                            lstDepartment.forEach(e => delete ctrl.selected[e]);
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

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, PhongBan) {
        $scope.onCreateUpdateDepartment = function (type) {
            let department = $scope.editDepartment;
            let errorMess = "";
            if (department.department_code == "") {
                errorMess += $filter('translate')('UTILITES.DEPARTMENT.ERR_EMPTY_DEPARTMENT_CODE');
            } else if(!Utils.validateCode(department.department_code)){
                errorMess += $filter('translate')('UTILITES.DEPARTMENT.ERR_INVALID_DEPARTMENT_CODE');
            }
            if (department.name == "") {
                errorMess += $filter('translate')('UTILITES.DEPARTMENT.ERR_EMPTY_DEPARTMENT_NAME');
            } else if(department.name != "" && !Utils.validateName(department.name)){
                errorMess += $filter('translate')('UTILITES.DEPARTMENT.ERR_INVALID_DEPARTMENT_NAME');
            }
            if (errorMess == "") {
                if (!department.id) {
                    PhongBan.create(department).then(function (response) {
                        handleUpdatePhongBanResponse(response, type);
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                } else {
                    PhongBan.update(department).then(function (response) {
                        handleUpdatePhongBanResponse(response, type);
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdatePhongBanResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchDepartment();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editDepartment = $scope.initNewDepartment();
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
