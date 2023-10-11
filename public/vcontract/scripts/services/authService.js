var login = angular.module('AuthSrvc',[]);

login.factory('Login',function($http){
	return{
		auth:function(credentials){
			var authUser = $http({method:'POST',url:'/api/v1/login',data:credentials});
			return authUser;
		},
		destroy:function(){
			var logoutUser = $http.get('/api/v1/logout');
			return logoutUser;
		},
		checkLoginStatus:function() {
			var check = $http.get('/api/v1/auth-check');
			return check;
		},
		forgetPassword: function(data) {
			var req = $http({method: 'POST', url: '/api/v1/forgetPassword', data: data});
            return req;
		},
		changeUserInfo: function(data) {
			var req = $http({method: 'POST', url: '/api/v1/changeUserInfo', data: data});
            return req;
		},
		changePwd: function(data) {
			var req = $http({method: 'POST', url: '/api/v1/changePwd', data: data});
            return req;
		},
		getInitData: function() {
			var initData = $http({method: 'GET', url: '/api/v1/init'});
            return initData;
		},
		getInitDashboardData: function(data) {
			var initData = $http({method: 'POST', url: '/api/v1/initDashboard', data:data});
            return initData;
		},
        updateUserSignature: function(data) {
            var req = $http({method: 'POST', url: '/api/v1/updateUserSignature', data: data});
            return req;
        },
        deleteUserSignature: function(data) {
            var req = $http({method: 'POST', url: '/api/v1/deleteUserSignature', data: data});
            return req;
        }
	}
});
