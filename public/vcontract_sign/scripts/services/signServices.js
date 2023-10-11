var service = angular.module('SignSrvc',[]);

service.factory('LoginAssignee',function($http) {
    return {
        auth:function(credentials){
            var authUser = $http({method:'POST',url:'/api/v1/assignee/login',data:credentials});
            return authUser;
        },
        destroy:function(data){
            var logoutUser = $http({method:'POST', url: '/api/v1/assignee/logout', data: data});
            return logoutUser;
        },
        checkLoginStatus:function() {
            let data = {
                code: Utils.getURLParameter("code").split("#")[0]
            }
            var check = $http({method: 'POST', url: '/api/v1/assignee/auth-check', data: data});
            return check;
        },
        getConfigData:function() {
            let data = {
                code: Utils.getURLParameter("code").split("#")[0]
            }
            var check = $http({method: 'POST', url: '/api/v1/assignee/getConfigData', data: data});
            return check;
        },
    }
});

service.factory('XuLyTaiLieu',function($http) {
    return {
        initViewDoc: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/init',data:data});
            return req;
        },
        getSignDocument: function(data) {
            return new window.Promise((resolve, reject) => {
                var oReq = new XMLHttpRequest();
                oReq.open("POST", '/api/v1/assignee/getSignDocument');
                oReq.setRequestHeader('Authorization', 'Bearer ' + sessionStorage.getItem('sign-etoken'));
                oReq.responseType = "arraybuffer";
                oReq.onload = function (oEvent) {
                    var arrayBuffer = oReq.response;
                    if (arrayBuffer) {
                        var byteArray = new Uint8Array(arrayBuffer);
                        resolve(byteArray);
                    }
                };
                oReq.onError = function (err) {
                    reject(err);
                },
                    oReq.send(data);
            });
        },
        updateUserSignature: function(data) {
            var req = $http({method: 'POST', url: '/api/v1/assignee/updateSignature', data: data});
            return req;
        },
        saveSignatureLocation: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/saveSignatureLocation',data:data});
            return req;
        },
        approveDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/approval',data:data});
            return req;
        },
        addApprovalAssignee : function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/addApprovalAssignee',data:data});
            return req;
        },
        signDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/signToken',data:data});
            return req;
        },
        denyDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/deny',data:data});
            return req;
        },
        sendOtp: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/sendOtp',data:data});
            return req;
        },
        signOtpDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/signOtp',data:data});
            return req;
        },
        transferDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/transferDocument',data:data});
            return req;
        },
        verifyOcr: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/verifyOcr',data:data});
            return req;
        },
        signKycDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/signKyc',data:data});
            return req;
        },
        getHashDoc: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/encodeDocument',data:data});
            return req;
        },
        getListCts: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/getListCts',data:data});
            return req;
        },
        signMySign: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/signMySign',data:data});
            return req;
        },
        registerCTS: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/dang-ky-cts-ica',data:data});
            return req;
        },
        signICA: function(data) {
            var req = $http({method:'POST',url:'/api/v1/assignee/ky-ica',data:data});
            return req;
        }
    }
});
