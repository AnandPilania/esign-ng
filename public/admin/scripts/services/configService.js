var service = angular.module('ConfigService', []);

service.factory('QuanLyKhachHang', function ($http) {
    return {
        init: function() {
            var req = $http({method:'GET',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/khoi-tao'});
            return req;
        },
        search: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/tim-kiem',data:data});
            return req;
        },
        create: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/them-moi',data:data});
            return req;
        },
        update: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/cap-nhat',data:data});
            return req;
        },
        delete: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/xoa',data:data});
            return req;
        },
        deleteMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/xoa-nhieu',data:data});
            return req;
        },
		getDetail: function(data){
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/chi-tiet-dich-vu',data:data});
			return req;
		},
		searchDocumentList: function(data){
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/danh-sach-tai-lieu',data:data});
			return req;
		},
        getDataConfigCompany: function(data){
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/lay-cau-hinh-company',data:data});
			return req;
		},
        updateConfigCompany: function(data){
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/cau-hinh-company',data:data});
			return req;
		},
		changePass: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/doi-mat-khau',data:data});
			return req;
		},
        reNewService: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-khach-hang/gia-han-goi-cuoc',data:data});
            return req;
        },
    }
});

service.factory('QuanLyMauThongBao', function ($http) {
    return {
        init: function() {
            var req = $http({method:'GET',url:'/api/v1/admin/thiet-lap/quan-ly-mau-thong-bao/khoi-tao'});
            return req;
        },
        search: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-mau-thong-bao/tim-kiem',data:data});
            return req;
        },
        create: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-mau-thong-bao/them-moi',data:data});
            return req;
        },
        update: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-mau-thong-bao/cap-nhat',data:data});
            return req;
        },
        delete: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-mau-thong-bao/xoa',data:data});
            return req;
        },
        deleteMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-mau-thong-bao/xoa-nhieu',data:data});
            return req;
        },
    }
});

service.factory('QuanLyDangNhap', function ($http) {
    return {
        init: function() {
			var req = $http({method:'GET',url:'/api/v1/admin/thiet-lap/quan-ly-dang-nhap-nguoi-dung/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dang-nhap-nguoi-dung/tim-kiem',data:data});
			return req;
		},
        create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dang-nhap-nguoi-dung/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dang-nhap-nguoi-dung/cap-nhat',data:data});
			return req;
		},
		changePass: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dang-nhap-nguoi-dung/doi-mat-khau',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dang-nhap-nguoi-dung/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dang-nhap-nguoi-dung/xoa-nhieu',data:data});
			return req;
		},
    }
});

service.factory('DaiLy',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/admin/thiet-lap/quan-ly-dai-ly/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dai-ly/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dai-ly/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dai-ly/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dai-ly/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dai-ly/xoa-nhieu',data:data});
			return req;
		},
		changeStatus: function(data){
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-dai-ly/cap-nhat-trang-thai',data:data});
			return req;
		}
	}
});

service.factory('GoiCuoc',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/admin/thiet-lap/quan-ly-goi-cuoc/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-goi-cuoc/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-goi-cuoc/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-goi-cuoc/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-goi-cuoc/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-goi-cuoc/xoa-nhieu',data:data});
			return req;
		},
		changeStatus: function(data){
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-goi-cuoc/cap-nhat-trang-thai',data:data});
			return req;
		},
		getDetail: function(data){
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-goi-cuoc/chi-tiet-goi-cuoc',data:data});
			return req;
		},
		saveDetail: function(data){
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-goi-cuoc/them-thiet-lap-cuoc',data:data});
			return req;
		},
		deleteDetail: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-goi-cuoc/xoa-thiet-lap-cuoc',data:data});
			return req;
		},
	}
});

service.factory('QuanLyTaiLieuHuongDan',function($http){
	return{
		init: function() {
			var req = $http({method:'GET',url:'/api/v1/admin/thiet-lap/quan-ly-tai-lieu-huong-dan/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-tai-lieu-huong-dan/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-tai-lieu-huong-dan/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-tai-lieu-huong-dan/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-tai-lieu-huong-dan/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-tai-lieu-huong-dan/xoa-nhieu',data:data});
			return req;
		},
		uploadFiles: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-tai-lieu-huong-dan/upload-file', data:data});
			return req;
		},
		removeFile: function(data) {
            var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-tai-lieu-huong-dan/xoa-tai-lieu',data:data});
            return req;
		},
		getDetail: function(data){
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-tai-lieu-huong-dan/chi-tiet',data:data});
			return req;
		},
		getTutorialDocument: function(data) {
            return new window.Promise((resolve, reject) => {
                var oReq = new XMLHttpRequest();
                oReq.open("POST", '/api/v1/admin/thiet-lap/quan-ly-tai-lieu-huong-dan/tai-tai-lieu');
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
			var req = $http({method:'GET',url:'/api/v1/admin/thiet-lap/quan-ly-video-huong-dan/khoi-tao'});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-video-huong-dan/tim-kiem',data:data});
			return req;
		},
		create: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-video-huong-dan/them-moi',data:data});
			return req;
		},
		update: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-video-huong-dan/cap-nhat',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-video-huong-dan/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-video-huong-dan/xoa-nhieu',data:data});
			return req;
		},
		getDetail: function(data){
			var req = $http({method:'POST',url:'/api/v1/admin/thiet-lap/quan-ly-video-huong-dan/chi-tiet',data:data});
			return req;
		},
	}
});
