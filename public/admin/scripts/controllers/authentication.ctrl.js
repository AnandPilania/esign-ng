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

        ctrl.login = function () {
            var auth = Login.auth(ctrl.userInfo);
            auth.then(function (response) {
                if (response.data.success) {
                    sessionStorage.setItem('ams-lang', response.data.data.language);
                    sessionStorage.setItem('ams-etoken', response.data.data.token);

                    $state.go("index.dashboard");
                }
            },function (response) {
                NotificationService.error($filter('translate')(response.data.message));
            });
        }

        ctrl.logout = function() {
            Login.destroy().then(function(response) {
                sessionStorage.removeItem('ams-etoken');
                $state.go('others.signin');
            }, function() {

            })
        }
    }
})();
