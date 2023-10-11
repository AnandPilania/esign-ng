var service = angular.module('UtilitiesSrvc',[]);

service.factory('ChiNhanh',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/tien-ich/chi-nhanh/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chi-nhanh/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chi-nhanh/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chi-nhanh/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chi-nhanh/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chi-nhanh/xoa-nhieu',data:data});
			return req;
		},
		changeStatus: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chi-nhanh/cap-nhat-trang-thai',data:data});
			return req;
		}
	}
});

service.factory('ChucVu',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/tien-ich/chuc-vu/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chuc-vu/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chuc-vu/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chuc-vu/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chuc-vu/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/chuc-vu/xoa-nhieu',data:data});
			return req;
		},
	}
});

service.factory('PhongBan',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/tien-ich/phong-ban/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/phong-ban/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/phong-ban/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/phong-ban/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/phong-ban/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/phong-ban/xoa-nhieu',data:data});
			return req;
		},
	}
});

service.factory('NhanVien',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/tien-ich/nhan-vien/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/nhan-vien/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/nhan-vien/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/nhan-vien/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/nhan-vien/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/nhan-vien/xoa-nhieu',data:data});
			return req;
		},
		checkExist: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/nhan-vien/kiem-tra-ton-tai',data:data});
			return req;
		}
	}
});

service.factory('KhachHangDoiTac',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/tien-ich/danh-muc-khach-hang-doi-tac/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/danh-muc-khach-hang-doi-tac/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/danh-muc-khach-hang-doi-tac/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/danh-muc-khach-hang-doi-tac/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/danh-muc-khach-hang-doi-tac/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/danh-muc-khach-hang-doi-tac/xoa-nhieu',data:data});
			return req;
		},
		checkExist: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/danh-muc-khach-hang-doi-tac/kiem-tra-ton-tai',data:data});
			return req;
		}
	}
});

service.factory('PhanLoaiTaiLieu',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/tien-ich/phan-loai-tai-lieu/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/phan-loai-tai-lieu/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/phan-loai-tai-lieu/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/phan-loai-tai-lieu/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/phan-loai-tai-lieu/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/tien-ich/phan-loai-tai-lieu/xoa-nhieu',data:data});
			return req;
		},
	}
});