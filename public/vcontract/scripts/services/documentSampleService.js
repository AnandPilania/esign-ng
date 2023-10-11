var service = angular.module('DocumentSampleService', []);

service.factory('QuanLyTaiLieuMau',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/quan-ly-tai-lieu-mau/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/quan-ly-tai-lieu-mau/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/quan-ly-tai-lieu-mau/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/quan-ly-tai-lieu-mau/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/quan-ly-tai-lieu-mau/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/quan-ly-tai-lieu-mau/xoa-nhieu',data:data});
			return req;
		},
		uploadFiles: function(data) {
			var req = $http({method:'POST',url:'/api/v1/quan-ly-tai-lieu-mau/upload-file', data:data});
			return req;
		},
		getFiles: function(data){
			var req = $http({method:'POST',url:'/api/v1/quan-ly-tai-lieu-mau/lay-file',data:data});
			return req;
		},
		removeFile: function(data) {
            var req = $http({method:'POST',url:'/api/v1/quan-ly-tai-lieu-mau/xoa-tai-lieu',data:data});
            return req;
		},
		getDetail: function(data){
			var req = $http({method:'POST',url:'/api/v1/quan-ly-tai-lieu-mau/chi-tiet',data:data});
			return req;
		},
        saveDetailSampleDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/quan-ly-tai-lieu-mau/luu-chi-tiet',data:data});
            return req;
        },
        getSampleDocument: function(data) {
            return new window.Promise((resolve, reject) => {
                var oReq = new XMLHttpRequest();
                oReq.open("POST", '/api/v1/quan-ly-tai-lieu-mau/tai-tai-lieu');
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
        }
	}
});
