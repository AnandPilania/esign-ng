(function () {
    'use strict';
    angular.module("app").controller("AuthenticationCtrl", ['$scope', '$uibModal', 'Login', '$state', '$filter', AuthenticationCtrl]);
    function AuthenticationCtrl($scope, $uibModal, Login, $state, $filter) {
        var ctrl = this;
        ctrl.userInfo = {
            // code: "",
            email: "",
            password: "",
            isLoading: true
        }

        ctrl.login = function () {
            $scope.errorMess = "";
            // if(ctrl.userInfo.code == ""){
            //     $scope.errorMess += "Mã doanh nghiệp không được để trống </br>";
            // }
            if(ctrl.userInfo.email == ""){
                $scope.errorMess += $filter('translate')('LOGIN.ERR_EMPTY_EMAIL');
            }
            if(ctrl.userInfo.password == ""){
                $scope.errorMess += $filter('translate')('LOGIN.ERR_EMPTY_PASSWORD');
            }
            if($scope.errorMess == ""){
                var auth = Login.auth(ctrl.userInfo);
                auth.then(function (response) {
                    if (response.data.success) {
                        sessionStorage.setItem('lang', response.data.data.language);
                        sessionStorage.setItem('etoken', response.data.data.token);
                        $state.go("index.dashboard");
                    }
                },function (response) {
                    NotificationService.error($filter('translate')(response.data.message));
                });
            } else {
                NotificationService.error($scope.errorMess);
            }

        }

        ctrl.logout = function() {
            Login.destroy().then(function(response) {
                sessionStorage.removeItem('etoken');
                $state.go('others.signin');
            }, function() {

            })
        }
    }
})();
