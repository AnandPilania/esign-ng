
var userSignature = null;

(function () {
    'use strict';
    angular.module("app").controller("MainCtrl", ['$scope', '$rootScope', '$http', '$window', '$timeout', '$uibModal', '$filter', '$state', 'Login', MainCtrl]);
    function MainCtrl($scope, $rootScope, $http, $window, $timeout, $uibModal, $filter, $state, Login) {

        var ctrl = this;

        $scope.initWorkSpace = function () {
            $scope.loginUser = $rootScope.loginUser;
            $scope.firstLogin = $rootScope.firstLogin ;
            askChangePassword();
        }

        $scope.initWorkSpace();
        function askChangePassword(){
            let firstLogin = $scope.firstLogin
            if(firstLogin  == true){
                const confirm = NotificationService.confirm($filter('translate')("CONFIG.USER.ASK_CHANGE_PASSWORD"), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), );
                confirm.then( function (){
                        ctrl.openChangePwdModal();
                }
                )
            }
        }
        ctrl.openChangePwdModal = function () {
            $scope.changePwd = {
                old: "",
                new: "",
                confirm: "",
                isLoading: true
            }
            $uibModal.open({
                animation: true,
                templateUrl: 'tracuu/views/modal/changePwd.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalChangePwdInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openChangeUserInfoModal = function() {
            $scope.changeUserInfo = angular.copy($scope.loginUser);
            $scope.changeUserInfo.isLoading = true;
            $uibModal.open({
                animation: true,
                templateUrl: 'tracuu/views/modal/changeUserInfo.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalChangePwdInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openChangePersonalSignature = function() {
            userSignature = $scope.loginUser.signature;
            $scope.userSignature = {
                image_signature: $scope.loginUser.signature ? $scope.loginUser.signature : Utils.defaultUploadImage()
            }

            $uibModal.open({
                animation: true,
                templateUrl: 'tracuu/views/modal/addPersonalSignature.html',
                windowClass: "fade show modal-blur",
                size: 'md modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalChangePwdInstanceCtrl,
                scope: $scope
            });
        }

        $scope.goBack = function() {
            window.history.back();
        }

        $scope.showDocumentHistory = function(docId) {
            $http({
                method: 'POST',
                url: '/api/v1/search/lich-su-tai-lieu',
                data: {docId: docId, isLoading: true}
            }).then(function (response) {
                $scope.lstDocumentHistory = response.data.data;
                $scope.historyData = [['Người thực hiện', 'Địa chỉ email', 'Hành động', 'Nội dung', 'Thời gian']];
                $scope.lstDocumentHistory.forEach(history => {
                    var tmp = [];
                    history.created_at = Utils.formatDate(new Date(history.created_at));
                    tmp.push(history.action_by)
                    tmp.push(history.action_by_email);
                    tmp.push($filter('translate')('DOCUMENT.HISTORY.ACTION_' + history.action));
                    tmp.push(history.content);
                    tmp.push(history.created_at);
                    $scope.historyData.push(tmp);
                })
                $uibModal.open({
                    animation: true,
                    templateUrl: 'tracuu/views/modal/documentHistory.html',
                    windowClass: "fade show modal-blur",
                    size: 'xl modal-dialog-centered',
                    backdrop: 'static',
                    backdropClass: 'show',
                    keyboard: false,
                    controller: ModalChangePwdInstanceCtrl,
                    scope: $scope
                });
            }, function () {
                $scope.lstDocumentHistory = [];
            })
        }

        $scope.showTransactionDocumentHistory = function(docId, docCode, docName) {
            $http({
                method: 'POST',
                url: '/api/v1/search/lich-su-xac-thuc',
                data: {docId: docId, isLoading: true}
            }).then(function (response) {
                $scope.historyTransactionData = [['Thời gian', 'Mã tài liệu', 'Tên tài liệu', 'Mã thông điệp', 'Mã giao dịch', 'Mã hợp đồng', 'Trạng thái', 'Người gửi', 'Người nhận']];
                $scope.lstDocumentTransactionHistory = response.data.data;
                $scope.lstDocumentTransactionHistory.forEach(history => {
                    var tmp = [];
                    let content = JSON.parse(history.content);
                    history.docCode = docCode;
                    history.docName = docName;
                    history.info = content.info ? content.info : content.Info;
                    history.verificationCode = content.content && content.content.data ? content.content.data.verificationCode : "";
                    history.created_at = history.info.sendDate ? Utils.formatDate(new Date(history.info.sendDate)) : Utils.formatDate(new Date(history.info.SendDate))
                    tmp.push(history.created_at);
                    tmp.push(history.docCode);
                    tmp.push(history.docName);
                    tmp.push(history.info.messageId ? history.info.messageId : history.info.MessageId);
                    tmp.push(history.transaction_id);
                    tmp.push(history.verificationCode ?  history.verificationCode + "": "");
                    tmp.push(history.info.responseMessage ? history.info.responseMessage: history.info.ResponseMessage);
                    tmp.push(history.info.senderId ? history.info.senderId : history.info.SenderId);
                    tmp.push(history.info.receiverId ? history.info.receiverId: history.info.ReceiverId);
                    $scope.historyTransactionData.push(tmp);
                })
                $uibModal.open({
                    animation: true,
                    templateUrl: 'tracuu/views/modal/documentTransactionHistory.html',
                    windowClass: "fade show modal-blur",
                    size: 'xl modal-dialog-centered',
                    backdrop: 'static',
                    backdropClass: 'show',
                    keyboard: false,
                    controller: ModalChangePwdInstanceCtrl,
                    scope: $scope
                });
            }, function () {
                $scope.lstDocumentHistory = [];
            })
        }
    }

    function ModalChangePwdInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, $state, Login, $rootScope) {
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

            if(pwd.confirm.length == 0){
                NotificationService.error('Nhắc lại mật khẩu không được để trống');
                return;
            } else {
                if (pwd.new != pwd.confirm) {
                    NotificationService.warning("Mật khẩu mới và Nhắc lại mật khẩu không trùng khớp");
                    return;
                }
            }
            if (pwd.new == pwd.old) {
                NotificationService.warning("Mật khẩu mới và mật khẩu cũ không được trùng nhau");
                return;
            }
            Login.changePwd(pwd).then(function(response) {
                if (response.data.success) {
                    $uibModalInstance.close(false);
                    NotificationService.success($filter('translate')(response.data.message));
                    Login.destroy().then(function(response) {
                        sessionStorage.removeItem('etoken');
                        $state.go('others.signin');
                    }, function() {

                    })
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
            if (info.phone == "") {
                errorMess += "- Số điện thoại không được để trống <br/>";}
            else if(info.phone != "" && !Utils.isValidPhoneNumber(info.phone)){
                errorMess += "- Số điện thoại không hợp lệ <br/>";
            }
            if(info.address != "" && !Utils.validateVietnameseAddress(info.address)){
                errorMess += "- Địa chỉ không hợp lệ <br/>";
            }

            info.birthday = info.dob == null ? null : info.dob;
            console.log(info);
            if (errorMess == "") {
                Login.changeUserInfo(info).then(function(response) {
                    if (response.data.success) {
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

        $scope.printHistory = function() {
            var wb = XLSX.utils.book_new();
            // var tbl = document.getElementById('historyTable');

            var worksheet = XLSX.utils.aoa_to_sheet($scope.historyData);
            wb.SheetNames.push('Sheet1');
            wb.Sheets['Sheet1'] = worksheet;

            var wbout = XLSX.write(wb, { bookType: 'xlsx', bookSST: true, type: 'binary' });
            saveAs(new Blob([s2ab(wbout)], { type: "application/octet-stream" }), 'Lịch sử tài liệu.xlsx');
            // var divToPrint = document.getElementById('frmLichSu');
            // var newWin = window.open('', 'Print-Window');
            // newWin.document.open();
            // newWin.document.write('<html><title>Lịch sử tài liệu</title><header>');
            // newWin.document.write('<link rel="stylesheet" href="bower_components/datatables/media/css/jquery.dataTables.css">');
            // newWin.document.write('<link rel="stylesheet" href="tracuu/assets/styles/tabler.css">');
            // newWin.document.write('<link rel="stylesheet" href="tracuu/assets/styles/CssCustomize.css">');
            // newWin.document.write('</header><body style="background-color: #ffffff !important;" onload="window.print()">' + divToPrint.innerHTML + '</body></html>');
            // newWin.document.close();
            // setTimeout(function () { newWin.close(); }, 200);
        }

        $scope.printTransactionHistory = function() {
            var wb = XLSX.utils.book_new();
            var worksheet = XLSX.utils.aoa_to_sheet($scope.historyTransactionData);
            wb.SheetNames.push('Sheet1');
            wb.Sheets['Sheet1'] = worksheet;
            var wbout = XLSX.write(wb, { bookType: 'xlsx', bookSST: true, type: 'binary' });
            saveAs(new Blob([s2ab(wbout)], { type: "application/octet-stream" }), 'Lịch sử xác thực.xlsx');
        }

        $scope.updatePersonalSignatureManual = function(data){
            if(data.isEmpty()){
                NotificationService.error($filter('translate')("CONFIG.ACCOUNT.ERR_DRAW_SIGNATURE"));
            } else {
                let editSignature = angular.copy($scope.userSignature);
                editSignature.image_signature = data.toDataURL();
                const confirm = NotificationService.confirm($filter('translate')("CONFIG.ACCOUNT.CONFIRM_SAVE_NEW_SIGNATURE"));
                confirm.then(function () {
                    editSignature.isLoading = true;
                    Login.updateUserSignature(editSignature).then(function (response) {
                        if (response.data.success) {
                            $scope.loginUser.signature = data.toDataURL();
                            NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                            $uibModalInstance.close(false);
                        } else {
                            NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        }
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                }).catch(function (err) {
                });
            }
        }

        $scope.updatePersonalSignatureUpload = function(data){
            var file = angular.element(document.querySelector('input[type=file]')['files'][0]);
            if(file && file[0]){
                var reader = new window.FileReader();
                reader.onload = function(){
                    var b64 = reader.result;
                    let editSignature = angular.copy($scope.userSignature);
                    if(editSignature.image_signature != b64){
                        editSignature.image_signature = b64;
                        const confirm = NotificationService.confirm($filter('translate')("CONFIG.ACCOUNT.CONFIRM_SAVE_NEW_SIGNATURE"));
                        confirm.then(function () {
                            editSignature.isLoading = true;
                            Login.updateUserSignature(editSignature).then(function (response) {
                                if (response.data.success) {
                                    $scope.loginUser.signature = b64;
                                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                                    $uibModalInstance.close(false);
                                } else {
                                    NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                                }
                            }, function (response) {
                                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                            });
                        }).catch(function (err) {
                        });
                    } else {
                        NotificationService.success($filter('translate')("CONFIG.ACCOUNT.UPDATE_SUCCESSFULLY"));
                        $uibModalInstance.close(false);
                    }
                }
                reader.readAsDataURL(file[0]);
            } else {
                NotificationService.success($filter('translate')("CONFIG.ACCOUNT.UPDATE_SUCCESSFULLY"));
                $uibModalInstance.close(false);
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
