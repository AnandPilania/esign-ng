var service = angular.module('ConfigService', []);

service.factory('ThongTinThueBao', function ($http) {
    return {
        init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/thong-tin-thue-bao/khoi-tao', data: data});
			return req;
		},
        update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/thong-tin-thue-bao/cap-nhat',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/thong-tin-thue-bao/tim-kiem-nguoi-giao-ket',data:data});
			return req;
		},
        createCompanyConsignee: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/thong-tin-thue-bao/them-moi-nguoi-giao-ket',data:data});
			return req;
		},
		updateCompanyConsignee: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/thong-tin-thue-bao/cap-nhat-nguoi-giao-ket',data:data});
			return req;
		},
        updateCompanyRemoteSign: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/thong-tin-thue-bao/cap-nhat-remote-signing',data:data});
			return req;
		},
		deleteCompanyConsignee: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/thong-tin-thue-bao/xoa-nguoi-giao-ket',data:data});
			return req;
		},
		updateCompanySignature: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/thong-tin-thue-bao/cap-nhat-chu-ky',data:data});
			return req;
		},
    }
});

service.factory('GuiTaiLieu',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/tham-so/khoi-tao', data:data});
			return req;
		},
		updateTime: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/tham-so/cap-nhat-thoi-gian',data:data});
			return req;
		},
		updateEmail: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/tham-so/cap-nhat-email',data:data});
			return req;
		},
        updateSms: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/tham-so/cap-nhat-sms',data:data});
			return req;
		},
	}
});

service.factory('PhanQuyen',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/thiet-lap/phan-quyen/khoi-tao'});
			return req;
		},
        initDetail: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/phan-quyen/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/phan-quyen/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/phan-quyen/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/phan-quyen/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/phan-quyen/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/phan-quyen/xoa-nhieu',data:data});
			return req;
		},
	}
});

service.factory('QuanLyDangNhap', function ($http) {
    return {
        init: function() {
			var req = $http({method:'GET',url:'/api/v1/thiet-lap/quan-ly-dang-nhap-nguoi-dung/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-dang-nhap-nguoi-dung/tim-kiem',data:data});
			return req;
		},
        create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-dang-nhap-nguoi-dung/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-dang-nhap-nguoi-dung/cap-nhat',data:data});
			return req;
		},
		changePass: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-dang-nhap-nguoi-dung/doi-mat-khau',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-dang-nhap-nguoi-dung/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-dang-nhap-nguoi-dung/xoa-nhieu',data:data});
			return req;
		},
    }
});

service.factory('QuanLyMauThongBao', function ($http) {
    return {
        init: function() {
			var req = $http({method:'GET',url:'/api/v1/thiet-lap/quan-ly-mau-thong-bao/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-mau-thong-bao/tim-kiem',data:data});
			return req;
		},
        create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-mau-thong-bao/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-mau-thong-bao/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-mau-thong-bao/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thiet-lap/quan-ly-mau-thong-bao/xoa-nhieu',data:data});
			return req;
		},
    }
});

