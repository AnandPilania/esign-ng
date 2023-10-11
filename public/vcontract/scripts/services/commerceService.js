var service = angular.module('CommerceSrvc',[]);

service.factory('XuLyTaiLieuThuongMai',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/tao-moi/khoi-tao',data:data});
			return req;
		},
		getCodeById: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/tao-moi/lay-so-tai-lieu',data:data});
			return req;
		},
        getDetailDocumentSampleById: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/tao-moi/chi-tiet-tai-lieu-mau',data:data});
            return req;
        },
		uploadFiles: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/tao-moi/upload-file', data:data});
			return req;
		},
		getSignDocument: function(data) {
			return new window.Promise((resolve, reject) => {
				var oReq = new XMLHttpRequest();
				oReq.open("POST", '/api/v1/thuong-mai/tao-moi/tai-tai-lieu-ky');
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
		goChooseAssignee: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/tao-moi/hoan-thanh-buoc-1', data:data});
			return req;
		},
		goStep3: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/tao-moi/hoan-thanh-buoc-2', data:data});
			return req;
		},
		finishDrafting: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/tao-moi/hoan-thanh-soan-thao', data:data});
			return req;
		},
        createDocumentFromTemplate: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/tao-moi/tao-moi-tai-lieu', data:data});
            return req;
        },
		getFatherDoc: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/tao-moi/chi-tiet-tai-lieu-cha',data:data});
            return req;
        },
		initViewDoc: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/chi-tiet/khoi-tao',data:data});
			return req;
		},
		searchAddendum: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/chi-tiet/tim-kiem',data:data});
			return req;
		},
		approveDocument: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/phe-duyet',data:data});
			return req;
		},
		signDocument: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/ky-so',data:data});
			return req;
		},
		denyDocument: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/tu-choi',data:data});
			return req;
		},
        editDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/chinh-sua',data:data});
            return req;
        },
        selectSignature: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/chon-chu-ky',data:data});
            return req;
        },
        saveSignatureLocation: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/cap-nhat-chu-ky',data:data});
            return req;
        },
        sendOtp: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/gui-otp',data:data});
            return req;
        },
        signOtpDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/ky-so-otp',data:data});
            return req;
		},
        verifyOcr: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/xac-minh-ocr',data:data});
            return req;
        },
        signKycDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/ky-so-kyc',data:data});
            return req;
        },
		removeFile: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/xoa-tai-lieu',data:data});
            return req;
		},
        getHashDoc: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/ma-hoa-tai-lieu',data:data});
            return req;
        },
        getListCts: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/lay-list-chung-thu-so',data:data});
            return req;
        },
        signMySign: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/ky-my-sign',data:data});
            return req;
        },
        registerCTS: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/dang-ky-cts-ica',data:data});
            return req;
        },
        signICA: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/ky-ica',data:data});
            return req;
        }
	}
});
service.factory('DanhSachTaiLieuThuongMai',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/danh-sach-tai-lieu/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/danh-sach-tai-lieu/tim-kiem',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/danh-sach-tai-lieu/xoa-nhieu',data:data});
			return req;
		},
		viewHistory: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-tai-lieu',data:data});
			return req;
		},
	}
});


service.factory('KySoTaiLieuThuongMai',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/ky-so-tai-lieu/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/ky-so-tai-lieu/tim-kiem',data:data});
			return req;
		},
        signDocument: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/ky-so-tai-lieu/ky-so',data:data});
            return req;
        },
        signMulti: function(data) {
            var req = $http({method:'POST',url:'/api/v1/thuong-mai/ky-so-tai-lieu/ky-so-nhieu',data:data});
            return req;
        }

	}
});
service.factory('PheDuyetTaiLieuThuongMai',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/phe-duyet-tai-lieu/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/phe-duyet-tai-lieu/tim-kiem',data:data});
			return req;
		},
		approve: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/phe-duyet-tai-lieu/phe-duyet',data:data});
			return req;
		},
		approveMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/phe-duyet-tai-lieu/phe-duyet-nhieu',data:data});
			return req;
		}
	}
});
service.factory('QuanLyGuiEmailThuongMai',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-email/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			console.log(data)
			const req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-email/tim-kiem',data:data});
			console.log(req)
			return req;
		},
		send: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-email/gui',data:data});
			return req;
		},
		sendMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-email/gui-nhieu',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-email/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-email/xoa-nhieu',data:data});
			return req;
		},
	}
});
service.factory('QuanLyGuiSmsThuongMai',function($http){
	return{
		init: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-sms/khoi-tao',data:data});
			return req;
		},
		search: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-sms/tim-kiem',data:data});
			return req;
		},
		send: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-sms/gui',data:data});
			return req;
		},
		sendMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-sms/gui-nhieu',data:data});
			return req;
		},
		delete: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-sms/xoa',data:data});
			return req;
		},
		deleteMulti: function(data) {
			var req = $http({method:'POST',url:'/api/v1/thuong-mai/lich-su-gui-sms/xoa-nhieu',data:data});
			return req;
		},
	}
});
