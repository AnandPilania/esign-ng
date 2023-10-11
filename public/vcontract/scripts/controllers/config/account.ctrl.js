
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'ConfigService']).controller("AccountCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'ThongTinThueBao', AccountCtrl]);
    function AccountCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, ThongTinThueBao) {

        var ctrl = this;

        function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // $compile(nRow)($scope);
        }

        $scope.init = function() {
            ThongTinThueBao.init({isLoading: true}).then(function (response) {
                ctrl.account = response.data.data.account;
                ctrl.account.sign_type = '' + ctrl.account.sign_type;
                ctrl.permission = response.data.data.permission;
                // $scope.lstCompanyConsignee = response.data.data.lstCompanyConsignee;
                $scope.companyRemoteSign = response.data.data.remoteSign;
                // $scope.companySignature = response.data.data.companySignature;
                // $scope.companySignature.image_signature = $scope.companySignature.image_signature ?? Utils.defaultUploadImage();
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }

        $scope.init();

        ctrl.onUpdateAccount = function () {

            let account = ctrl.account;
            let errorMess = "";
            if (account.tax_number == "") {
                errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_EMPTY_ACCOUNT_TAX_NUMBER');
            }
            if (account.name == "") {
                errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_EMPTY_ACCOUNT_NAME');
            }
            if (account.address == "") {
                errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_EMPTY_ACCOUNT_ADDRESS');
            }

            if(account.email && !Utils.validateEmail(account.email)){
                errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_INVALID_ACCOUNT_EMAIL');
            }

            if(account.contact_email && !Utils.validateEmail(account.contact_email)){
                errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_INVALID_ACCOUNT_CONTACT_EMAIL');
            }

            account.isLoading = true;

            if (errorMess == "") {
                ThongTinThueBao.update(account).then(function (response) {
                    $scope.init();
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                }, function (response) {
                    NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        // $scope.initNewCompanyConsignee = function() {
        //     return {
        //         name: "",
        //         email: "",
        //         phone: "",
        //         role: "-1",
        //         status: true,
        //     }
        // };

        // ctrl.addCompanyConsigneeCreateAndUpdate = function(){
        //     $scope.editCompanyConsignee = $scope.initNewCompanyConsignee();
        //     $uibModal.open({
        //         animation: true,
        //         templateUrl: 'vcontract/views/modal/addUpdateCompanyConsignee.html',
        //         windowClass: "fade show modal-blur",
        //         size: 'lg modal-dialog-centered modal-dialog-scrollable',
        //         backdrop: 'static',
        //         backdropClass: 'show',
        //         keyboard: false,
        //         controller: ModalInstanceCtrl,
        //         scope: $scope
        //     });
        // }

        // ctrl.editCompanyConsigneeCreateAndUpdate = function(row){
        //     $scope.editCompanyConsignee = angular.copy($scope.lstCompanyConsignee[row]);
        //     $scope.editCompanyConsignee.role = '' + $scope.editCompanyConsignee.role;
        //     $scope.editCompanyConsignee.status = $scope.editCompanyConsignee.status == 1;
        //     $uibModal.open({
        //         animation: true,
        //         templateUrl: 'vcontract/views/modal/addUpdateCompanyConsignee.html',
        //         windowClass: "fade show modal-blur",
        //         size: 'lg modal-dialog-centered modal-dialog-scrollable',
        //         backdrop: 'static',
        //         backdropClass: 'show',
        //         keyboard: false,
        //         controller: ModalInstanceCtrl,
        //         scope: $scope
        //     });
        // }

        // ctrl.onDeleteCompanyConsignee = function (row) {
        //     let editCompanyConsignee = $scope.lstCompanyConsignee[row];
        //     editCompanyConsignee.isLoading = true;
        //     const confirm = NotificationService.confirm($filter('translate')('CONFIG.ACCOUNT.DELETE_CONFIRM', {'consigneeName': editCompanyConsignee.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
        //     confirm.then(function () {
        //         ThongTinThueBao.deleteCompanyConsignee(editCompanyConsignee).then(function (response) {
        //             if (response.data.success) {
        //                 $scope.lstCompanyConsignee.splice(row, 1);
        //                 NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
        //             } else {
        //                 NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //             }
        //         }, function (response) {
        //             NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //         });
        //     }).catch(function (err) {
        //     });
        // }

        // ctrl.createSignature = function(){

        //     $uibModal.open({
        //         animation: true,
        //         templateUrl: 'vcontract/views/modal/addCompanySignature.html',
        //         windowClass: "fade show modal-blur",
        //         size: 'md modal-dialog-centered',
        //         backdrop: 'static',
        //         backdropClass: 'show',
        //         keyboard: false,
        //         controller: ModalInstanceCtrl,
        //         scope: $scope
        //     });
        // }

        ctrl.updateRemoteSigning = function(){
            $scope.companyRemoteSign.status = $scope.companyRemoteSign.status == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateRemoteSign.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered modal-dialog-scrollable',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, ThongTinThueBao) {
        $scope.onSaveRemoteSign = function(){
            let errorMess ="";
            let remote_sign = $scope.companyRemoteSign;
            if (remote_sign.provider == "") {
                errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_EMPTY_REMOTE_SIGN_PROVIDER');
            }

            if (remote_sign.service_signing == "") {
                errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_EMPTY_REMOTE_SIGN_SERVICE_SIGNING');
            }

            if (remote_sign.login == "") {
                errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_EMPTY_REMOTE_SIGN_LOGIN');
            }

            if (remote_sign.password == "") {
                errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_EMPTY_REMOTE_SIGN_PASSWORD');
            }

            if (errorMess == "") {
                remote_sign.isLoading = true;
                ThongTinThueBao.updateCompanyRemoteSign(remote_sign).then(function (response) {
                    $scope.init();
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    $uibModalInstance.close(false);
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        // $scope.companyConsigneeCreateAndUpdate = function (type) {
        //     let editCompanyConsignee = $scope.editCompanyConsignee;
        //     let errorMess = "";
        //     if (editCompanyConsignee.role == "-1" || editCompanyConsignee.role == "") {
        //         errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_EMPTY_CONSIGNEE_ROLE');
        //     }

        //     if (editCompanyConsignee.name == "") {
        //         errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_EMPTY_CONSIGNEE_NAME');
        //     }

        //     if (editCompanyConsignee.email == "") {
        //         errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_EMPTY_CONSIGNEE_EMAIL');
        //     }

        //     if(editCompanyConsignee.email != "" && !Utils.validateEmail(editCompanyConsignee.email)){
        //         errorMess += $filter('translate')('CONFIG.ACCOUNT.ERR_INVALID_CONSIGNEE_EMAIL');
        //     }

        //     if (errorMess == "") {
        //         if (!editCompanyConsignee.id) {
        //             editCompanyConsignee.isLoading = true;
        //             ThongTinThueBao.createCompanyConsignee(editCompanyConsignee).then(function (response) {
        //                 handleUpdateThongTinThueBaoResponse(response, type);
        //             }, function (response) {
        //                 NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //             });
        //         } else {
        //             editCompanyConsignee.isLoading = true;
        //             ThongTinThueBao.updateCompanyConsignee(editCompanyConsignee).then(function (response) {
        //                 handleUpdateThongTinThueBaoResponse(response, type);
        //             }, function (response) {
        //                 NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //             });
        //         }
        //     } else {
        //         NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //     }
        // }

        function handleUpdateThongTinThueBaoResponse(response, type) {
            if (response.data.success) {
                $scope.init();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editCompanyConsignee = $scope.initNewCompanyConsignee();
                }
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
            } else {
                NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        // $scope.updateCompanySignature = function(data){
        //     if(data.isEmpty()){
        //         NotificationService.error($filter('translate')("CONFIG.ACCOUNT.ERR_DRAW_SIGNATURE"));
        //     } else {
        //         let editCompanySignature = angular.copy($scope.companySignature);
        //         editCompanySignature.image_signature = data.toDataURL();
        //         const confirm = NotificationService.confirm($filter('translate')("CONFIG.ACCOUNT.CONFIRM_SAVE_NEW_SIGNATURE"));
        //         confirm.then(function () {
        //             editCompanySignature.isLoading = true;
        //             ThongTinThueBao.updateCompanySignature(editCompanySignature).then(function (response) {
        //                 if (response.data.success) {
        //                     $scope.init();
        //                     NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
        //                     $uibModalInstance.close(false);
        //                 } else {
        //                     NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //                 }
        //             }, function (response) {
        //                 NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //             });
        //         }).catch(function (err) {
        //         });
        //     }
        // }

        // $scope.updateCompanySignatureManual = function(data){
        //     if(data.isEmpty()){
        //         NotificationService.error($filter('translate')("CONFIG.ACCOUNT.ERR_DRAW_SIGNATURE"));
        //     } else {
        //         let editCompanySignature = angular.copy($scope.companySignature);
        //         editCompanySignature.image_signature = data.toDataURL();
        //         const confirm = NotificationService.confirm($filter('translate')("CONFIG.ACCOUNT.CONFIRM_SAVE_NEW_SIGNATURE"));
        //         confirm.then(function () {
        //             editCompanySignature.isLoading = true;
        //             ThongTinThueBao.updateCompanySignature(editCompanySignature).then(function (response) {
        //                 if (response.data.success) {
        //                     $scope.init();
        //                     NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
        //                     $uibModalInstance.close(false);
        //                 } else {
        //                     NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //                 }
        //             }, function (response) {
        //                 NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //             });
        //         }).catch(function (err) {
        //         });
        //     }
        // }

        // $scope.updateCompanySignatureUpload = function(data){
        //     var file = angular.element(document.querySelector('input[type=file]')['files'][0]);
        //     if(file && file[0]){
        //         var reader = new window.FileReader();
        //         reader.onload = function(){
        //             var b64 = reader.result;
        //             let editCompanySignature = angular.copy($scope.companySignature);
        //             if(editCompanySignature.image_signature != b64){
        //                 editCompanySignature.image_signature = b64;
        //                 const confirm = NotificationService.confirm($filter('translate')("CONFIG.ACCOUNT.CONFIRM_SAVE_NEW_SIGNATURE"));
        //                 confirm.then(function () {
        //                     editCompanySignature.isLoading = true;
        //                     ThongTinThueBao.updateCompanySignature(editCompanySignature).then(function (response) {
        //                         if (response.data.success) {
        //                             $scope.init();
        //                             NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
        //                             $uibModalInstance.close(false);
        //                         } else {
        //                             NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //                         }
        //                     }, function (response) {
        //                         NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
        //                     });
        //                 }).catch(function (err) {
        //                 });
        //             } else {
        //                 $scope.init();
        //                 NotificationService.success($filter('translate')("CONFIG.ACCOUNT.UPDATE_SUCCESSFULLY"));
        //                 $uibModalInstance.close(false);
        //             }
        //         }
        //         reader.readAsDataURL(file[0]);
        //     } else {
        //         $scope.init();
        //         NotificationService.success($filter('translate')("CONFIG.ACCOUNT.UPDATE_SUCCESSFULLY"));
        //         $uibModalInstance.close(false);
        //     }
        // }


        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };

    }

})();
