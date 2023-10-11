var login = angular.module('AuthSrvc',[]);

login.factory('Login',function($http){
	return{
		auth:function(credentials){
			var authUser = $http({method:'POST',url:'/api/v1/admin/login',data:credentials});
			return authUser;
		},
		destroy:function(){
			var logoutUser = $http.get('/api/v1/admin/logout');
			return logoutUser;
		},
		checkLoginStatus:function() {
			var check = $http.get('/api/v1/admin/auth-check');
			return check;
		},
		forgetPassword: function(data) {
			var req = $http({method: 'POST', url: '/api/v1/admin/forgetPassword', data: data});
            return req;
		},
		changeUserInfo: function(data) {
			var req = $http({method: 'POST', url: '/api/v1/admin/changeUserInfo', data: data});
            return req;
		},
		changePwd: function(data) {
			var req = $http({method: 'POST', url: '/api/v1/admin/changePwd', data: data});
            return req;
		},
		getInitData: function() {
			var initData = $http({method: 'GET', url: '/api/v1/admin/init'});
            return initData;
		},
		getInitDashboardData: function(data) {
			var initData = $http({method: 'POST', url: '/api/v1/admin/initDashboard', data:data});
            return initData;
		},
	}
});
