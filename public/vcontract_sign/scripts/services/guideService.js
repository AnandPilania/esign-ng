var service = angular.module('GuideService', []);
service.factory('TaiLieuHuongDan',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/assignee/tai-lieu-huong-dan/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/assignee/tai-lieu-huong-dan/tim-kiem',data:data});
			return req;
		},
        getDetail: function(data) {
			var req = $http({method:'POST',url:'/api/v1/assignee/tai-lieu-huong-dan/chi-tiet',data:data});
			return req;
		},
		getTutorialDocument: function(data) {
            return new window.Promise((resolve, reject) => {
                var oReq = new XMLHttpRequest();
                oReq.open("POST", '/api/v1/assignee/tai-lieu-huong-dan/tai-tai-lieu');
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

service.factory('QuanLyVideoHuongDan',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/assignee/video-huong-dan/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/assignee/video-huong-dan/tim-kiem',data:data});
			return req;
		},
        getDetail: function(data) {
			var req = $http({method:'POST',url:'/api/v1/assignee/video-huong-dan/chi-tiet',data:data});
			return req;
		},
	}
});