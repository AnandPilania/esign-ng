var login = angular.module('HistorySrvc',[]);

login.factory('LichSuTacDong',function($http){
	return{
		init: function(data) {
			var req = $http({method:'GET',url:'/api/v1/lich-su-tac-dong/khoi-tao', data: data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/lich-su-tac-dong/tim-kiem',data:data});
			return req;
		},
	}
});
