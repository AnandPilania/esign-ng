
(function () {
    'use strict';
    angular.module("app", ['ConfigService']).controller("SendDocumentCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', '$filter', 'GuiTaiLieu', SendDocumentCtrl]);
    function SendDocumentCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, $filter, GuiTaiLieu) {

        var ctrl = this;

        $scope.init = function() {
            GuiTaiLieu.init({isLoading: true}).then(function (response) {
                ctrl.params = response.data.data.params;
                ctrl.permission = response.data.data.permission;
                ctrl.email = response.data.data.email;
                ctrl.sms = response.data.data.sms;

                $scope.configEmail = {
                    email_host: ctrl.email ? ctrl.email.email_host : $filter('translate')('CONFIG.SEND_DOCUMENT.NOT_CONFIG'),
                    updated_at: ctrl.email ? ctrl.email.updated_at : $filter('translate')('CONFIG.SEND_DOCUMENT.NOT_CONFIG'),
                    status: ctrl.email ? (ctrl.email.status == 1 ? $filter('translate')('CONFIG.SEND_DOCUMENT.EFFECT') : $filter('translate')('CONFIG.SEND_DOCUMENT.NOT_EFFECT')) : $filter('translate')('CONFIG.SEND_DOCUMENT.NOT_CONFIG'),
                }

                $scope.configSms = {
                    service_provider: ctrl.sms ? ctrl.sms.service_provider : $filter('translate')('CONFIG.SEND_DOCUMENT.NOT_CONFIG'),
                    brandname: ctrl.sms ? ctrl.sms.brandname : $filter('translate')('CONFIG.SEND_DOCUMENT.NOT_CONFIG'),
                    status: ctrl.sms ? (ctrl.sms.status == 1 ? $filter('translate')('CONFIG.SEND_DOCUMENT.EFFECT') : $filter('translate')('CONFIG.SEND_DOCUMENT.NOT_EFFECT')) : $filter('translate')('CONFIG.SEND_DOCUMENT.NOT_CONFIG'),
                }

            }, function(response) {
                $scope.initBadRequest(response);
            });
        };

        $scope.init();

        ctrl.updateSendTimeParams = function() {
            let params = ctrl.params;
            let errorMess = "";
            if (params.document_expire_day == "") {
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_TIME_EXPIRE_DOCUMENT');
            }
            if (params.near_expired_date == "") {
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_TIME_SEND_EMAIL_NEAR_EXPIRE');
            }
            if (params.near_doc_expire_date == "") {
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_TIME_SEND_EMAIL_NEAR_DOC_EXPIRE');
            }
            params.isLoading = true;
            if (errorMess == "") {
                GuiTaiLieu.updateTime(params).then(function (response) {
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        ctrl.openUpdateEmailModal = function () {
            $scope.editEmail = ctrl.email ? angular.copy(ctrl.email) : {
                email_host: "",
                email_protocol: "0",
                email_address: "",
                email_password: "",
                email_name: "",
                port: "",
                is_relay: false,
                is_use_ssl: false,
                status: 1
            }
            if($scope.editEmail.is_use_ssl == 1){
                $scope.editEmail.is_use_ssl = true
            }
            if($scope.editEmail.is_relay == 1){
                $scope.editEmail.is_relay = true
            }
            $scope.editEmail.status = $scope.editEmail.status == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/updateEmailAccount.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openUpdateSmsModal = function () {
            $scope.editSms = ctrl.sms ? angular.copy(ctrl.sms) : {
                service_provider: "",
                service_url: "",
                brandname: "",
                sms_account: "",
                sms_password: "",
                status: 1
            }
            $scope.editSms.status = $scope.editSms.status == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/updateSmsProvider.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }
        ctrl.openChangePassEmail = function(){
            $scope.editEmail = angular.copy(ctrl.email);
            $scope.editEmail.email_password = "";
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/changePasswordEmail.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }
        ctrl.openChangePassSms = function(){
            $scope.editSms = angular.copy(ctrl.sms);
            $scope.editSms.sms_password = "";
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/changePasswordSms.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, GuiTaiLieu) {
        $scope.updateEmailAccount = function () {
            let email = $scope.editEmail;
            let errorMess = "";
            if(email.email_host == ""){
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_EMAIL_HOST');
            }

            if(email.port == ""){
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_EMAIL_PORT');
            }

            if(email.email_password == "" && !email.is_relay){
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_EMAIL_PASSWORD');
            }

            if(email.email_address == ""){
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_EMAIL_ADDRESS');
            }

            email.isLoading = true;

            if (errorMess == "") {
                GuiTaiLieu.updateEmail(email).then(function (response) {
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    $scope.init();
                    $uibModalInstance.close(false);
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }
        $scope.updateSmsProvider = function(data){
            let sms = $scope.editSms;
            let errorMess = "";
            if(sms.service_provider == ""){
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_SMS_PROVIDER');
            }

            if(sms.service_url == ""){
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_SMS_SERVICE');
            }

            if(sms.sms_account == ""){
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_SMS_ACCOUNT');
            }

            if(sms.sms_password == ""){
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_SMS_PASSWORD');
            }

            sms.isLoading = true;

            if (errorMess == "") {
                GuiTaiLieu.updateSms(sms).then(function (response) {
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    $scope.init();
                    $uibModalInstance.close(false);
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.onChangePasswordEmail = function(data){
            let errorMess = "";

            if(data.sms_password == ""){
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_SMS_PASSWORD');
            }

            data.isLoading = true;

            if (errorMess == "") {
                GuiTaiLieu.updateEmail(data).then(function (response) {
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    $scope.init();
                    $uibModalInstance.close(false);
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.onChangePasswordSms = function(data){
            let errorMess = "";

            if(data.email_password == ""){
                errorMess += $filter('translate')('CONFIG.SEND_DOCUMENT.ERR_EMPTY_EMAIL_PASSWORD');
            }

            data.isLoading = true;

            if (errorMess == "") {
                GuiTaiLieu.updateSms(data).then(function (response) {
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    $scope.init();
                    $uibModalInstance.close(false);
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
