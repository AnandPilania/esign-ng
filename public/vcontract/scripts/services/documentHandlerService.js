var service = angular.module('DocumentSrvc',[]);

service.factory('XuLyTaiLieuT',function($http){
    return{

        getSignDocumentInternal: function(data) {
            return new window.Promise((resolve, reject) => {
                var oReq = new XMLHttpRequest();
                oReq.open("POST", '/api/v1/noi-bo/tao-moi/tai-tai-lieu-ky');
                oReq.setRequestHeader('Authorization', 'Bearer ' + sessionStorage.getItem('etoken'));
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

        getSignDocumentCommerce: function(data) {
            return new window.Promise((resolve, reject) => {
                var oReq = new XMLHttpRequest();
                oReq.open("POST", '/api/v1/thuong-mai/tao-moi/tai-tai-lieu-ky');
                oReq.setRequestHeader('Authorization', 'Bearer ' + sessionStorage.getItem('etoken'));
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


    }
});

service.factory('DanhSachTaiLieuSapHetHan',function($http){
    return{
        init: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-sap-qua-han/khoi-tao',data:data});
            return req;
        },
        search: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-sap-qua-han/tim-kiem',data:data});
            return req;
        },

        changeGroup: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-sap-qua-han/thay-doi-nhom',data:data});
            return req;
        },

        renewMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-sap-qua-han/gia-han-nhieu',data:data});
            return req;
        },

    }
});
service.factory('DanhSachTaiLieuSapHetHieuLuc',function($http){
    return{
        init: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-sap-het-hieu-luc/khoi-tao',data:data});
            return req;
        },
        search: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-sap-het-hieu-luc/tim-kiem',data:data});
            return req;
        },

        changeGroup: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-sap-het-hieu-luc/thay-doi-nhom',data:data});
            return req;
        },

        renewMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-sap-het-hieu-luc/gia-han-nhieu',data:data});
            return req;
        },

    }
});
service.factory('DanhSachTaiLieuHetHieuLuc',function($http){
    return{
        init: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-het-hieu-luc/khoi-tao',data:data});
            return req;
        },
        search: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-het-hieu-luc/tim-kiem',data:data});
            return req;
        },

        changeGroup: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-het-hieu-luc/thay-doi-nhom',data:data});
            return req;
        },

        deleteMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-het-hieu-luc/xoa-nhieu',data:data});
            return req;
        },

    }
});

service.factory('DanhSachTaiLieuHetHan',function($http){
    return{
        init: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-qua-han/khoi-tao',data:data});
            return req;
        },
        search: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-qua-han/tim-kiem',data:data});
            return req;
        },

        changeGroup: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-qua-han/thay-doi-nhom',data:data});
            return req;
        },

        deleteMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-qua-han/xoa-nhieu',data:data});
            return req;
        },

        sendMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-qua-han/gui-lai-nhieu',data:data});
            return req;
        },

    }
});

service.factory('DanhSachTaiLieuBiTuChoi',function($http){
    return{
        init: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-tu-choi/khoi-tao',data:data});
            return req;
        },
        search: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-tu-choi/tim-kiem',data:data});
            return req;
        },

        changeGroup: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-tu-choi/thay-doi-nhom',data:data});
            return req;
        },

        deleteMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-tu-choi/xoa-nhieu',data:data});
            return req;
        },

        sendMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-tu-choi/gui-lai-nhieu',data:data});
            return req;
        },

    }
});

service.factory('DanhSachTaiLieuBiHuyBo',function($http){
    return{
        init: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-huy-bo/khoi-tao',data:data});
            return req;
        },
        search: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-huy-bo/tim-kiem',data:data});
            return req;
        },

        changeGroup: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-huy-bo/thay-doi-nhom',data:data});
            return req;
        },

        restoreMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/xu-ly-tai-lieu/tai-lieu-bi-huy-bo/khoi-phuc-nhieu',data:data});
            return req;
        },

    }
});
