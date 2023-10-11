var service = angular.module('ReportSrvc',[]);

service.factory('BaoCaoTaiLieuNoiBo',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/bao-cao/bao-cao-tai-lieu-noi-bo/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/bao-cao/bao-cao-tai-lieu-noi-bo/tim-kiem',data:data});
			return req;
		},
		export: function(data) {
			return new window.Promise((resolve, reject) => {
				var oReq = new XMLHttpRequest();
				oReq.open("POST", '/api/v1/bao-cao/bao-cao-tai-lieu-noi-bo/xuat-bao-cao');
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

service.factory('BaoCaoTaiLieuThuongMai',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/bao-cao/bao-cao-tai-lieu-thuong-mai/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/bao-cao/bao-cao-tai-lieu-thuong-mai/tim-kiem',data:data});
			return req;
		},
		export: function(data) {
			return new window.Promise((resolve, reject) => {
				var oReq = new XMLHttpRequest();
				oReq.open("POST", '/api/v1/bao-cao/bao-cao-tai-lieu-thuong-mai/xuat-bao-cao');
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

service.factory('BaoCaoGuiTin',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/bao-cao/bao-cao-gui-tin/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/bao-cao/bao-cao-gui-tin/tim-kiem',data:data});
			return req;
		},
		export: function(data) {
			return new window.Promise((resolve, reject) => {
				var oReq = new XMLHttpRequest();
				oReq.open("POST", '/api/v1/bao-cao/bao-cao-gui-tin/xuat-bao-cao');
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
service.factory('BaoCaoEkyc',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/bao-cao/bao-cao-ekyc/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/bao-cao/bao-cao-ekyc/tim-kiem',data:data});
			return req;
		},
		export: function(data) {
			return new window.Promise((resolve, reject) => {
				var oReq = new XMLHttpRequest();
				oReq.open("POST", '/api/v1/bao-cao/bao-cao-ekyc/xuat-bao-cao');
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
service.factory('DanhSachNguoiKy',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/bao-cao/danh-sach-nguoi-ky/khoi-tao',data:data});
			return req;
		},
		searchSignAssignee: function (data) {
			var req = $http({method:'POST',url:'/api/v1/bao-cao/danh-sach-nguoi-ky/tim-kiem',data:data});
			return req;
		},
		exportSignAssignee: function (data) {
			return new window.Promise((resolve, reject) => {
				var oReq = new XMLHttpRequest();
				oReq.open("POST", '/api/v1/bao-cao/danh-sach-nguoi-ky/xuat-danh-sach');
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
		}
	}
});
