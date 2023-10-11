(function () {
    'use strict';
    angular.module("app").controller("AuthenticationCtrl", ['$scope', '$uibModal', 'Login', '$state', '$filter', AuthenticationCtrl]);
    function AuthenticationCtrl($scope, $uibModal, Login, $state, $filter) {
        var ctrl = this;
        ctrl.userInfo = {
            email: "",
            password: "",
            isLoading: true
        }
        $scope.showPass = false;
        ctrl.getPassword = function () {
            var getPassword = Login.getPassword(ctrl.userInfo);
            getPassword.then(function (response) {
                if (response.data.success) {
                    if (response.data.message) {
                        NotificationService.success(response.data.message);
                    }
                    $scope.showPass = true;                    
                }
            },function (response) {
                NotificationService.error($filter('translate')(response.data.message));
            });
        }

        ctrl.login = function () {
            var auth = Login.auth(ctrl.userInfo);   
            auth.then(function (response) {
                if (response.data.success) {
                    sessionStorage.setItem('etoken', response.data.data.token)
                    $state.go("index.documentList");
                }
            },function (response) {
                NotificationService.error($filter('translate')(response.data.message));
            });
        }

        ctrl.forgetPassword = function () {
            var forgetPassword = Login.forgetPassword(ctrl.userInfo);
            forgetPassword.then(function (response) {
                if (response.data.success) {
                    if (response.data.message) {
                        NotificationService.success(response.data.message);
                    }                  
                }
            },function (response) {
                NotificationService.error($filter('translate')(response.data.message));
            });
        }
        
        ctrl.logout = function() {
            Login.destroy({isLoading: true}).then(function(response) {
                sessionStorage.removeItem('etoken');
                $state.go('others.signin');
            }, function() {

            })
        }

        ctrl.goBack = function () {
            ctrl.userInfo.password = "";
            $scope.showPass = false; 
        }
    }
})();
