(function () {
    'use strict';
    angular.module("app").controller("AuthenticationCtrl", ['$scope', '$uibModal', 'LoginAssignee', '$state', '$filter', AuthenticationCtrl]);
    function AuthenticationCtrl($scope, $uibModal, LoginAssignee, $state, $filter) {
        var ctrl = this;


        ctrl.userInfo = {
            email: "",
            password: "",
            url_code: Utils.getURLParameter("code").split("#")[0],
            isLoading: true
        }


        ctrl.onSignin = function () {
            var auth = LoginAssignee.auth(ctrl.userInfo);
            auth.then(function (response) {
                if (response.data.success) {
                    sessionStorage.setItem('sign-etoken', response.data.data.token)
                    $state.go("index");
                }
            },function (response) {
                NotificationService.error($filter('translate')(response.data.message));
            });
        }

        ctrl.logout = function() {
            LoginAssignee.destroy({isLoading: true}).then(function(response) {
                sessionStorage.removeItem('sign-etoken');
                $state.go('signin');
            }, function() {

            })
        }
    }
})();
