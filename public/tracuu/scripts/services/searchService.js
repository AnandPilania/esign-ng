var login = angular.module('SearchSrvc',[]);

login.factory('Login',function($http){
	return{
		auth:function(credentials){
			var authUser = $http({method:'POST',url:'/api/v1/search/login',data:credentials});
			return authUser;
		},
		destroy:function(){
			var logoutUser = $http.get('/api/v1/search/logout');
			return logoutUser;
		},
		checkLoginStatus:function() {
			var check = $http.get('/api/v1/search/auth-check');
			return check;
		},
		forgetPassword: function(data) {
			var req = $http({method: 'POST', url: '/api/v1/search/forgetPassword', data: data});
            return req;
		},
		changePwd: function(data) {
			var req = $http({method: 'POST', url: '/api/v1/search/changePwd', data: data});
            return req;
		},
		getInitData: function() {
			var initData = $http({method: 'GET', url: '/api/v1/search/init'});
            return initData;
		},
		getPassword: function (data) {
			var req = $http({method: 'POST', url: '/api/v1/search/getPassword', data: data});
            return req;
		}
	}
});

login.factory('DanhSachTaiLieu',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/search/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/search/tim-kiem',data:data});
			return req;
		},
		viewHistory: function(data) {
			var req = $http({method:'POST',url:'/api/v1/search/lich-su-tai-lieu',data:data});
			return req;
		},
	}
});

login.factory('XuLyTaiLieu',function($http){
	return{
		getSignDocument: function(data) {
			return new window.Promise((resolve, reject) => {
				var oReq = new XMLHttpRequest();
				oReq.open("POST", '/api/v1/search/chi-tiet/tai-tai-lieu-ky');
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
		initViewDoc: function(data) {
			var req = $http({method:'POST',url:'/api/v1/search/chi-tiet/khoi-tao',data:data});
			return req;
		},
	}
});