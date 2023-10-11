var service = angular.module('ReportSrvc',[]);

service.factory('BaoCao',function($http){
	return{
		init: function(data) {
			var req = $http({method:'get',url:'api/v1/admin/bao-cao/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/bao-cao/tim-kiem',data:data});
			return req;
		},
		export: function(data) {
			return new window.Promise((resolve, reject) => {
				var oReq = new XMLHttpRequest();
				oReq.open("POST", '/api/v1/admin/bao-cao/xuat-bao-cao');
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
					console.log(err);
				},
				oReq.send(data);
			});
		},
	}
});
