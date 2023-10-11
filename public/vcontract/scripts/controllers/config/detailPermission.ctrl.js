
(function () {
    'use strict';
    angular.module("app", ['ConfigService']).controller("DetailPermissionCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$stateParams', '$timeout', '$filter', 'PhanQuyen', DetailPermissionCtrl]);
    function DetailPermissionCtrl($scope, $compile, $uibModal, $http, $state, $stateParams, $timeout, $filter, PhanQuyen) {

        var ctrl = this;

        ctrl.selectAll = false;
        ctrl.selected = {};

        ctrl.toggleAll = function (selectAll, selectedItems) {
            for (var permission in selectedItems) {
                if (selectedItems.hasOwnProperty(permission)) {
                    selectedItems[permission] = selectAll;
                }
            }
            for (let i = 0; i < ctrl.lstPermission.length; i++) {
                const permission = ctrl.lstPermission[i];
                for (let j = 0; j < permission.lstChildPermission.length; j++) {
                    const child = permission.lstChildPermission[j];
                    ctrl.editPermission.lstPermission[child.permission].is_view = child.is_view == 0 ? null : selectAll;
                    ctrl.editPermission.lstPermission[child.permission].is_write = child.is_write == 0 ? null : selectAll;
                    ctrl.editPermission.lstPermission[child.permission].is_approval = child.is_approval == 0 ? null : selectAll;
                    ctrl.editPermission.lstPermission[child.permission].is_decision = child.is_decision == 0 ? null : selectAll;
                }
            }
        }
        ctrl.toggleOne = function (selectedItems, togglePermission) {
            for (let i = 0; i < ctrl.lstPermission.length; i++) {
                const permission = ctrl.lstPermission[i];
                if (permission.permission == togglePermission) {
                    for (let j = 0; j < permission.lstChildPermission.length; j++) {
                        const child = permission.lstChildPermission[j];
                        ctrl.editPermission.lstPermission[child.permission].is_view = child.is_view == 0 ? null : ctrl.selected[togglePermission];
                        ctrl.editPermission.lstPermission[child.permission].is_write = child.is_write == 0 ? null : ctrl.selected[togglePermission];
                        ctrl.editPermission.lstPermission[child.permission].is_approval = child.is_approval == 0 ? null : ctrl.selected[togglePermission];
                        ctrl.editPermission.lstPermission[child.permission].is_decision = child.is_decision == 0 ? null : ctrl.selected[togglePermission];
                    }
                    break;
                }

            }
            for (var permission in selectedItems) {
                if (selectedItems.hasOwnProperty(permission)) {
                    if (!selectedItems[permission]) {
                        ctrl.selectAll = false;
                        return;
                    }
                }
            }
            ctrl.selectAll = true;
        }

        ctrl.init = function () {
            let id = $stateParams.roleId;
            PhanQuyen.initDetail({ role: id, isLoading: true }).then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.lstPermission = [];
                ctrl.lstTempPermission = angular.copy(response.data.data.lstPermission);
                response.data.data.lstPermission.forEach(permission => {
                    if (!permission.parent_permission) {
                        permission.lstChildPermission = [];
                        ctrl.selected[permission.permission] = false
                        ctrl.lstPermission.push(permission);
                    } else {
                        for (let i = 0; i < ctrl.lstPermission.length; i++) {
                            const element = ctrl.lstPermission[i];
                            if (element.permission == permission.parent_permission) {
                                element.lstChildPermission.push(permission);
                                break;
                            }
                        }
                    }
                });
                let role = response.data.data.role;
                if (role) {
                    ctrl.editPermission = {
                        'id': role.id,
                        'role_name': role.role_name,
                        'note': role.note,
                        'status': role.status == 1,
                        'lstPermission': []
                    }
                    response.data.data.lstPermission.forEach(permission => {
                        if (permission.parent_permission) {
                            let isExisted = false;
                            for (let i = 0; i < role.permission.length; i++) {
                                const per = role.permission[i];
                                if (per.permission_id == permission.id) {
                                    isExisted = true;
                                    ctrl.editPermission.lstPermission[permission.permission] = {
                                        role_id: role.id,
                                        permission_id: per.permission_id,
                                        is_view: permission.is_view == 0 ? null : per.is_view == 1,
                                        is_write: permission.is_write == 0 ? null : per.is_write == 1,
                                        is_approval: permission.is_approval == 0 ? null : per.is_approval == 1,
                                        is_decision: permission.is_decision == 0 ? null : per.is_decision == 1,
                                    }
                                    break;
                                }
                            }
                            if (!isExisted) {
                                ctrl.editPermission.lstPermission[permission.permission] = {
                                    role_id: role.id,
                                    permission_id: permission.id,
                                    is_view: permission.is_view == 0 ? null : false,
                                    is_write: permission.is_write == 0 ? null : false,
                                    is_approval: permission.is_approval == 0 ? null : false,
                                    is_decision: permission.is_decision == 0 ? null : false,
                                }
                            }
                        }
                    });
                } else {
                    ctrl.initNewPermission();
                }
            }, function (response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.initNewPermission = function() {
            ctrl.selectAll = false;
            ctrl.editPermission = {
                'role_name': "",
                'note': "",
                'status': true,
                'lstPermission': []
            }
            ctrl.lstTempPermission.forEach(permission => {
                if (!permission.parent_permission) {
                    ctrl.selected[permission.permission] = false
                } else {
                    ctrl.editPermission.lstPermission[permission.permission] = {
                        role_id: "",
                        permission_id: permission.id,
                        is_view: permission.is_view == 0 ? null : false,
                        is_write: permission.is_write == 0 ? null : false,
                        is_approval: permission.is_approval == 0 ? null : false,
                        is_decision: permission.is_decision == 0 ? null : false,
                    }
                }
            });
        }

        ctrl.goBackPermission = function () {
            $state.go("index.config.permission");
        }

        ctrl.onSavePermission = function (type) {
            let role = {
                'id': ctrl.editPermission.id,
                'role_name': ctrl.editPermission.role_name,
                'note': ctrl.editPermission.note,
                'status': ctrl.editPermission.status,
                'lstPermission': [],
                isLoading: true
            };

            for(let permission in ctrl.editPermission.lstPermission) {
                role.lstPermission.push(ctrl.editPermission.lstPermission[permission]);
            }

            let errorMess = "";
            if (role.role_name == "") {
                errorMess += $filter('translate')('CONFIG.PERMISSION.ERR_EMPTY_PERMISSION_NAME');
            } else if(role.role_name != "" && !Utils.validateVietnameseCharacterWithNumber(role.role_name)){
                errorMess += $filter('translate')('CONFIG.PERMISSION.ERR_INVALID_PERMISSION_NAME');
            }
            if (errorMess == "") {
                if(!role.id) {
                    PhanQuyen.create(role).then(function (response) {
                        handleUpdatePhanQuyenResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                } else {
                    PhanQuyen.update(role).then(function (response) {
                        handleUpdatePhanQuyenResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }

        }

        function handleUpdatePhanQuyenResponse(response, type) {
            if (response.data.success) {
                if (type == 0) {
                    ctrl.goBackPermission();
                } else {
                    ctrl.initNewPermission();
                }
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
            } else {
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }
    }
})();
