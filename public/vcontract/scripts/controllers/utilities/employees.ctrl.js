
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'UtilitiesSrvc']).controller("EmployeeCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'NhanVien', EmployeeCtrl]);
    function EmployeeCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, NhanVien) {

        var ctrl = this;

        ctrl.searchEmployee = {
            position_id: "-1",
            department_id: "-1",
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
            NhanVien.init().then(function (response) {
                ctrl.lstDepartment = response.data.data.lstDepartment;
                ctrl.lstPosition = response.data.data.lstPosition;
                ctrl.permission = response.data.data.permission;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        $scope.initNewEmployee = function() {
            return {
                emp_code: "",
                reference_code: "",
                emp_name: "",
                dob: null,
                sex: "-1",
                address1: "",
                address2: "",
                ethnic: "",
                nationality: "",
                national_id: "",
                national_date: null,
                national_address_provide: "",
                email: "",
                phone: "",
                note: "",
                department_id: "-1",
                position_id: "-1",
                status: true
            }
        }

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="employeeCtrl.selectAll" ng-change="employeeCtrl.toggleAll(employeeCtrl.selectAll, employeeCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchEmployee() }, 300);
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
                searchData: ctrl.searchEmployee,
                isLoading: true
            }
            NhanVien.search(params).then(function (response) {
                ctrl.lstEmployee = response.data.data.data;
                ctrl.lstEmployee.forEach(employee => {
                    employee.gender = $scope.lstGender[employee.sex].description;
                    employee.sex += "";
                    employee.department_id += "";
                    employee.position_id += "";
                    employee.dob = Utils.formatDateNoTime(new Date(employee.dob));
                    employee.national_date = employee.national_date != null ? Utils.formatDateNoTime(new Date(employee.national_date)) : null;
                });
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="employeeCtrl.selected[' + data.id + ']" ng-change="employeeCtrl.toggleOne(employeeCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('emp_code').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('emp_code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('emp_name').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('emp_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('dob').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_DOB')),
            DTColumnBuilder.newColumn('gender').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_GENDER')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('gender', meta.row, data);
            }),
            DTColumnBuilder.newColumn('phone').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_PHONE')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('phone', meta.row, data);
            }),
            DTColumnBuilder.newColumn('department_name').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_DEPARTMENT_NAME')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('department_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('position_name').withTitle($filter('translate')('UTILITES.EMPLOYEE.EMPLOYEE_POSITION_NAME')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('position_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('COMMON.USING')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.getDataTableStatusColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "employeeCtrl.openEditEmployeeModal", false, false, false, false, false, false, false, "employeeCtrl.onDeleteEmployee", false);
            }),
        ];

        $scope.onSearchEmployee = function () {
            ctrl.dtInstance.rerender();
        }

        ctrl.openAddEmployeeModal = function () {
            $scope.editEmployee = $scope.initNewEmployee();
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateEmployee.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered modal-dialog-scrollable',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openEditEmployeeModal = function (row) {
            $scope.editEmployee = angular.copy(ctrl.lstEmployee[row]);
            $scope.editEmployee.status = $scope.editEmployee.status == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateEmployee.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.onDeleteEmployee = function (row) {
            let employee = ctrl.lstEmployee[row];
            const confirm = NotificationService.confirm($filter('translate')('UTILITES.EMPLOYEE.DELETE_CONFIRM', {'employeeName': employee.emp_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                NhanVien.delete(employee).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchEmployee();
                        delete ctrl.selected[employee.id];
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

        ctrl.onDeleteMultiEmployees = function () {
            let lstEmployee = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstEmployee.push(id);
                    }
                }
            }
            if (lstEmployee.length == 0) {
                NotificationService.error($filter('translate')('UTILITES.EMPLOYEE.ERR_NEED_CHOOSE_EMPLOYEE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('UTILITES.EMPLOYEE.DELETE_MULTI_CONFIRM', { 'employeeLength': lstEmployee.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    NhanVien.deleteMulti({'lst': lstEmployee}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchEmployee();
                            lstEmployee.forEach(e => delete ctrl.selected[e]);
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

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, NhanVien) {
        $scope.onCreateUpdateEmployee = function (type) {
            let employee = $scope.editEmployee;
            let errorMess = "";
            // if (employee.emp_code == "") {
            //     errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_CODE');
            // } else
            if(employee.emp_code != "" && !Utils.validateCode(employee.emp_code)){
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_INVALID_EMPLOYEE_CODE');
            }
            if (employee.emp_name == "") {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_NAME');
            } else if(employee.emp_name != "" && !Utils.validateVietnameseCharacterWithoutNumber(employee.emp_name)){
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_INVALID_EMPLOYEE_NAME');
            }
            if (employee.dob == "") {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_DOB');
            }
            if (employee.sex == "-1" || employee.sex == "") {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_GENDER');
            }
            // if (employee.address1 == "") {
            //     errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_ADDRESS1');
            // } else
            if (employee.address1 != "" && !Utils.validateVietnameseAddress(employee.address1)){
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_INVALID_EMPLOYEE_ADDRESS1');
            }
            // if (employee.address2 == "") {
            //     errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_ADDRESS2');
            // } else
            if (employee.address2 != "" && !Utils.validateVietnameseAddress(employee.address2)){
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_INVALID_EMPLOYEE_ADDRESS2');
            }
            // if (employee.national_id == "") {
            //     errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_NATIONAL_ID');
            // } else
            if(employee.national_id != "" && !Utils.validateCCCD(employee.national_id)){
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_INVALID_EMPLOYEE_NATIONAL_ID');
            }
            // if (employee.national_date == "") {
            //     errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_NATIONAL_DATE');
            // }
            // if (employee.national_address_provide == "") {
            //     errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_NATIONAL_ADDRESS_PROVIDE');
            // } else
             if(employee.national_address_provide != "" && !Utils.validateVietnameseAddress(employee.national_address_provide)){
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_INVALID_EMPLOYEE_NATIONAL_ADDRESS_PROVIDE');
            }
            if (employee.email == "") {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_EMAIL');
            }

            if(employee.email != "" && !Utils.validateEmail(employee.email)){
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_INVALID_EMPLOYEE_EMAIL');
            }

            if (employee.department_id == "-1" || employee.department_id == "") {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_DEPARTMENT_NAME');
            }
            if (employee.position_id == "-1" || employee.position_id == "") {
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_EMPTY_EMPLOYEE_POSITION_NAME');
            }

            if(employee.phone != "" && !Utils.isValidPhoneNumber(employee.phone)){
                errorMess += $filter('translate')('UTILITES.EMPLOYEE.ERR_INVALID_EMPLOYEE_PHONE');
            }

            employee.birthday = employee.dob == null ? null : Utils.parseDate(employee.dob);
            employee.nationalDate = employee.national_date == null ? null : Utils.parseDate(employee.national_date);

            if (errorMess == "") {
                if (!employee.id) {
                    NhanVien.create(employee).then(function (response) {
                        handleUpdateNhanVienResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                } else {
                    NhanVien.update(employee).then(function (response) {
                        handleUpdateNhanVienResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }
            } else {
                NotificationService.error(errorMess);
            }

        }

        function handleUpdateNhanVienResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchEmployee();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editEmployee = $scope.initNewEmployee();
                }
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
            } else {
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
