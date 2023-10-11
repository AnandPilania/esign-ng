
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'ConfigService']).controller("UserCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'QuanLyDangNhap', UserCtrl]);
    function UserCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, QuanLyDangNhap) {

        var ctrl = this;

        ctrl.searchUser = {
            status: "-1",
            keyword: "",
            role_id: "-1",
            branch_id: "-1"
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
            QuanLyDangNhap.init().then(function (response) {
                ctrl.lstRole = response.data.data.lstRole;
                ctrl.permission = response.data.data.permission;
                ctrl.lstBranch = response.data.data.lstBranch;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="userCtrl.selectAll" ng-change="userCtrl.toggleAll(userCtrl.selectAll, userCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchUser() }, 300);
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
                searchData: ctrl.searchUser,
                isLoading: true
            }
            QuanLyDangNhap.search(params).then(function (response) {
                ctrl.lstUser = response.data.data.data;
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="userCtrl.selected[' + data.id + ']" ng-change="userCtrl.toggleOne(userCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('CONFIG.USER.USER_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('email').withTitle($filter('translate')('CONFIG.USER.USER_EMAIL')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('email', meta.row, data);
            }),
            DTColumnBuilder.newColumn('role_name').withTitle($filter('translate')('CONFIG.USER.USER_ROLE_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('role_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('branch_name').withTitle($filter('translate')('CONFIG.USER.USER_BRANCH')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('branch_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('CONFIG.USER.USER_EFFECT')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.getDataTableStatusColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "userCtrl.openEditUserModal", false, false, false, false, "userCtrl.onChangePass", false, false, "userCtrl.onDeleteUser", false);
            }),
        ];

        $scope.onSearchUser = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.initNewUser = function() {
            return {
                name: "",
                company_id: "-1",
                email: "",
                password: "",
                password_confirm: "",
                role_id: "-1",
                branch_id: "-1",
                note: "",
                is_personal: true,
                status: true,
                language: 'vi',
            }
        };

        ctrl.openAddUserModal = function () {
            $scope.editUser = $scope.initNewUser();
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateUser.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openEditUserModal = function (row) {
            $scope.editUser = angular.copy(ctrl.lstUser[row]);
            $scope.editUser.role_id = "" + $scope.editUser.role_id;
            $scope.editUser.branch_id = "" + $scope.editUser.branch_id;
            $scope.editUser.company_id = "" + $scope.editUser.company_id;
            $scope.editUser.status = $scope.editUser.status == 1;
            $scope.editUser.is_personal = $scope.editUser.is_personal == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateUser.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.onChangePass = function(row){
            $scope.editUser = angular.copy(ctrl.lstUser[row]);
            $scope.editUser.password = "";
            $scope.editUser.password_confirm = "";
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/changePasswordUser.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.onDeleteUser = function (row) {
            let user = ctrl.lstUser[row];
            const confirm = NotificationService.confirm($filter('translate')('CONFIG.USER.DELETE_CONFIRM', {'userName': user.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                QuanLyDangNhap.delete(user).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchUser();
                        delete ctrl.selected[user.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiUsers = function () {
            let lstUser = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstUser.push(id);
                    }
                }
            }
            if (lstUser.length == 0) {
                NotificationService.error($filter('translate')('CONFIG.USER.ERR_NEED_CHOOSE_USER'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('CONFIG.USER.DELETE_MULTI_CONFIRM', { 'userLength': lstUser.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    QuanLyDangNhap.deleteMulti({'lst': lstUser}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchUser();
                            lstUser.forEach(e => delete ctrl.selected[e]);
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

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, QuanLyDangNhap) {
        $scope.onCreateUpdateUser = function (type) {
            let user = $scope.editUser;
            let errorMess = "";

            if(user.role_id == "-1" || user.role_id == ""){
                errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_ROLE_ID');
            }
            if (user.name == "") {
                errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_NAME');
            } else if(user.name != "" && !Utils.validateVietnameseCharacterWithoutNumber(user.name)){
                errorMess += $filter('translate')('CONFIG.USER.ERR_INVALID_USER_NAME');
            }
            if(!user.id){
                if (user.password == ""){
                    errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_PASSWORD');
                }

                if(user.password != ""){
                    if(!Utils.validatePassword(user.password)){
                        errorMess += $filter('translate')('CONFIG.USER.ERR_INVALID_USER_PASSWORD');
                    } else if(user.password_confirm != "" && user.password != user.password_confirm){
                        errorMess += $filter('translate')('CONFIG.USER.ERR_INVALID_USER_PASSWORD_CONFIRM');
                    }
                }

                if (user.password_confirm == ""){
                    errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_PASSWORD_CONFIRM');
                }
            }

            if (user.email == "") {
                errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_EMAIL');
            }

            if(user.email != "" && !Utils.validateEmail(user.email)){
                errorMess += $filter('translate')('CONFIG.USER.ERR_INVALID_USER_EMAIL');
            }
            if (errorMess == "") {
                if (!user.id) {
                    QuanLyDangNhap.create(user).then(function (response) {
                        handleUpdateQuanLyDangNhapResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                } else {
                    QuanLyDangNhap.update(user).then(function (response) {
                        handleUpdateQuanLyDangNhapResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.generateRandomUserPassword = function(user) {
            let password = Utils.generatePassword();
            user.password = password;
            user.password_confirm = password;
        }

        $scope.onChangePasswordUser = function(data){
            let errorMess = "";

            if (data.password == ""){
                errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_PASSWORD');
            }

            if(data.password != ""){
                if(!Utils.validatePassword(data.password)){
                    errorMess += $filter('translate')('CONFIG.USER.ERR_INVALID_USER_PASSWORD');
                } else if(data.password_confirm != "" && data.password != data.password_confirm){
                    errorMess += $filter('translate')('CONFIG.USER.ERR_INVALID_USER_PASSWORD_CONFIRM');
                }
            }

            if (data.password_confirm == ""){
                errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_PASSWORD_CONFIRM');
            }

            if (errorMess == "") {
                QuanLyDangNhap.changePass(data).then(function (response) {
                    $scope.onSearchUser();
                    $uibModalInstance.close(false);
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdateQuanLyDangNhapResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchUser();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editUser = $scope.initNewUser();
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
