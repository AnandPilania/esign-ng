
var userSignature = null;

(function () {
    'use strict';
    angular.module("app").controller("MainCtrl", ['$scope', '$rootScope', '$http', '$window', '$timeout', '$uibModal', '$filter', '$state', 'Login', MainCtrl]);
    function MainCtrl($scope, $rootScope, $http, $window, $timeout, $uibModal, $filter, $state, Login) {

        var ctrl = this;

        $scope.initWorkSpace = function () {
            $scope.loginUser = $rootScope.loginUser;
            $scope.initData = $rootScope.initData;
            $scope.firstLogin = $rootScope.first_Login;
            // $scope.loginUser.role_id =2;
            onFirstLogin();
        }

        $scope.initWorkSpace();

        ctrl.openChangePwdModal = function () {
            $scope.changePwd = {
                old: "",
                new: "",
                confirm: "",
                isLoading: true
            }
            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/changePwd.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalChangePwdInstanceCtrl,
                scope: $scope
            });
        }

        // ctrl.openChangeUserInfoModal = function() {
        //     $scope.changeUserInfo = angular.copy($scope.loginUser);
        //     $scope.changeUserInfo.isLoading = true;
        //     $uibModal.open({
        //         animation: true,
        //         templateUrl: 'admin/views/modal/changeUserInfo.html',
        //         windowClass: "fade show modal-blur",
        //         size: 'lg modal-dialog-centered',
        //         backdrop: 'static',
        //         backdropClass: 'show',
        //         keyboard: false,
        //         controller: ModalChangePwdInstanceCtrl,
        //         scope: $scope
        //     });
        // }

        $scope.goBack = function() {
            window.history.back();
        }
        function onFirstLogin(){
            let firstLogin = $scope.firstLogin;
            if(firstLogin != 1)
            {
                const confirm = NotificationService.confirm($filter('translate')("CONFIG.USER.ASK_CHANGE_PASSWORD"), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"));
                confirm.then(function() {
                ctrl.openChangePwdModal();
                })
            }
        }

        $scope.initBadRequest = function(response) {
            NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            if (response.data.status == 401){
                $state.go('others.signin');
            }
            if (response.data.status == 403){
                $state.go("index.dashboard");
            }
        }
    }

    function ModalChangePwdInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, Login, $rootScope) {
        $scope.generateRandomPassword = function() {
            let password = Utils.generatePassword();
            $scope.changePwd.new = password;
            $scope.changePwd.confirm = password;
        }

        $scope.onChangePwd = function() {
            let pwd = $scope.changePwd;

            if (pwd.old.length == 0) {
                NotificationService.error('Mật khẩu cũ không được để trống');
                return;
            }
            else {
                if (pwd.old.trim().length > 50) {
                    NotificationService.error('Mật khẩu cũ không được vượt quá 50 ký tự');
                    return;
                }
            }
            if (pwd.new.length == 0) {
                NotificationService.error('Mật khẩu mới không được để trống');
                return;
            }
            else {
                if (pwd.new.trim().length < 8) {
                    NotificationService.error('Mật khẩu mới tối thiểu 8 ký tự');
                    return;
                }
                if (pwd.new.trim().length > 50) {
                    NotificationService.error('Mật khẩu mới không được vượt quá 50 ký tự');
                    return;
                }
                var passRegular = "^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*]).{8,}$";
                if (!pwd.new.trim().match(passRegular)) {
                    NotificationService.error('Mật khẩu phải có ít nhất 8 ký tự, và chứa ít nhất một chữ cái, một số và ký tự đặc biệt (ví dụ: ! @ # $ % ^ & *)');
                    return;
                }
            }

            if (pwd.new != pwd.confirm) {
                NotificationService.warning("Mật khẩu mới và Nhắc lại mật khẩu không trùng khớp");
                return;
            }
            if (pwd.new == pwd.old) {
                notificationService.warning("Mật khẩu mới và mật khẩu cũ không được trùng nhau");
                return;
            }
            Login.changePwd(pwd).then(function(response) {
                if (response.data.success) {
                    $uibModalInstance.close(false);
                    NotificationService.success($filter('translate')(response.data.message));
                } else {
                    NotificationService.error("Xảy ra lỗi trong quá trình thực hiện. Vui lòng thử lại sau!");
                }
            }, function(response) {
                NotificationService.error($filter('translate')(response.data.message));
            })

        }

        $scope.onChangeUserInfo = function() {
            let info = $scope.changeUserInfo;
            let errorMess = "";
            if (info.name == "") {
                errorMess += "- Họ và tên không được để trống <br/>";
            } else if(info.name != "" && !Utils.validateVietnameseCharacterWithoutNumber(info.name)){
                errorMess += "- Họ và tên không hợp lệ <br/>";
            }

            if(info.phone != "" && !Utils.isValidPhoneNumber(info.phone)){
                errorMess += "- Số điện thoại không hợp lệ <br/>";
            }
            if(info.address != "" &&  info.address != null && !Utils.validateVietnameseAddress(info.address)){
                errorMess += "- Địa chỉ không hợp lệ <br/>";
            }

            info.birthday = info.dob == "" ? "" : Utils.parseDate(info.dob);

            if (errorMess == "") {
                Login.changeUserInfo(info).then(function(response) {
                    if (response.data.success) {
                        $rootScope.loginUser = response.data.data.user;
                        sessionStorage.setItem('amslang', response.data.data.user.language);
                        $scope.initWorkSpace();
                        $uibModalInstance.close(false);
                        NotificationService.success($filter('translate')(response.data.message));
                    } else {
                        NotificationService.error("Xảy ra lỗi trong quá trình thực hiện. Vui lòng thử lại sau!");
                    }
                }, function(response) {
                    NotificationService.error($filter('translate')(response.data.message));
                })
            } else {
                NotificationService.error(errorMess);
            }

        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };

    }
})();
