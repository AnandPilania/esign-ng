var isLoading = false;
angular
	.module('app')
	.config(config)
	.directive('ckEditor', function(){
		return {
			require: '?ngModel',
			link: function(scope, elm, attr, ngModel) {
			  var ck = CKEDITOR.replace(elm[0]);

			  if (!ngModel) return;

			  ck.on('pasteState', function() {
				scope.$apply(function() {
				  ngModel.$setViewValue(ck.getData());
				});
			  });

			  ngModel.$render = function(value) {
				ck.setData(ngModel.$viewValue);
			  };
			}
		  };
	})
	.config(['$translateProvider', function ($translateProvider) {

		$translateProvider.useStaticFilesLoader({
			prefix: 'vcontract/lang/',
			suffix: '.json'
		});

		$translateProvider.preferredLanguage(sessionStorage.getItem('lang') || 'vi');
		$translateProvider.useSanitizeValueStrategy('escaped');
	}])
	.factory('TranslateService', function ($translate) {
		return {
			translate: function (lang) {
				return $translate.use(lang);
			}
		}
	})
	.factory('AuthInterceptor', function ($q, $location) {
		return {
			request: function (config) {
				if (!isLoading) {
				    if (config.data && config.data.isLoading) {
                        $('.loadingapp').removeClass('hidden');
                        isLoading = true;
                    }
                }
                config.headers = config.headers || {};
                if (config.headers.Authorization == undefined && sessionStorage.getItem('etoken')) {
                    config.headers.Authorization = 'Bearer ' + sessionStorage.getItem('etoken');
                }
                return config || $q.when(config);
            },
            response: function (response) {
                if (isLoading) {
                    $('.loadingapp').addClass('hidden');
                    isLoading = false;
                }
                // if (response.status === 401 || response.status === 403) {
                // 	$location.path('/');
                // }
                return response || $q.when(response);
            },
            responseError: function(response) {
                if (isLoading) {
                    $('.loadingapp').addClass('hidden');
                    isLoading = false;
                }

                return $q.reject(response);
            }
        };
    })
    .factory('WebcamService', WebcamService)
    .config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.useXDomain = true;
        $httpProvider.interceptors.push('AuthInterceptor');
    }])
    .run(function ($rootScope, $state, Login, $transitions, $location) {
        $rootScope.$state = $state;
    });

function _skipIfAuthenticated($q, $state, Login, $timeout) {
    // var defer = $q.defer();
    return Login.checkLoginStatus().then(function (response) {
        if (response.data.data.is_login) {
            // $timeout(function () {
            $state.go('index.dashboard');
            // });
            // 	defer.reject();
            // } else {
            // 	defer.resolve();
        }
        // return defer.promise;
    }, function () {
    })
}

function _redirectIfNotAuthenticated($q, $state, Login, $timeout) {
    // var defer = $q.defer();
    return Login.checkLoginStatus().then(function (response) {
        // defer.resolve();
        // return defer.promise;
    }, function () {
        $state.go('others.signin');
    })
}

function _getInitData(Login, $rootScope) {
    return Login.getInitData().then(function(response) {
        $rootScope.loginUser = response.data.data.user;
        $rootScope.initData = response.data.data.initData;
        $rootScope.firstLogin  = response.data.data.firstLogin;
        $rootScope.configData  = response.data.data.configData;
    })
}

function WebcamService () {
    var webcam = {};
    webcam.isTurnOn = false;
    webcam.patData = null;
    var _video = null;
    var _stream = null;
    webcam.patOpts = {x: 0, y: 0, w: 25, h: 25};
    webcam.channel = {};
    webcam.webcamError = false;
    webcam.switch = false;

    webcam.onChange = function () { 
        webcam.switch = true;
        var videoSelect = document.querySelector('select#videoSource');
        if (webcam.switch = true && videoSelect.selectedIndex == 0) {
            videoSelect.selectedIndex = 1
        } else if (webcam.switch = true && videoSelect.selectedIndex == 1) {
            videoSelect.selectedIndex = 0
        }
        webcam.getStream();
    }                                                     

    var getVideoData = function getVideoData(x, y, w, h) {
        var hiddenCanvas = document.createElement('canvas');
        hiddenCanvas.width = _video.width;
        hiddenCanvas.height = _video.height;
        var ctx = hiddenCanvas.getContext('2d');
        ctx.drawImage(_video, 0, 0, _video.width, _video.height);
        return ctx.getImageData(x, y, w, h);
    };

    var sendSnapshotToServer = function sendSnapshotToServer(imgBase64) {
        webcam.snapshotData = imgBase64;
    };

    webcam.makeSnapshot = function() {
        if (_video) {
            var patCanvas = document.querySelector('#snapshot');
            if (!patCanvas) return;

            patCanvas.width = _video.width;
            patCanvas.height = _video.height;
            var ctxPat = patCanvas.getContext('2d');

            var idata = getVideoData(webcam.patOpts.x, webcam.patOpts.y, webcam.patOpts.w, webcam.patOpts.h);
            ctxPat.putImageData(idata, 0, 0);

            sendSnapshotToServer(patCanvas.toDataURL());

            webcam.patData = idata;

            webcam.success(webcam.snapshotData.substr(webcam.snapshotData.indexOf('base64,') + 'base64,'.length), 'image/png');
            webcam.turnOff();
        }
    };

    webcam.onSuccess = function () {
        _video = webcam.channel.video;
        webcam.patOpts.w = _video.width;
        webcam.patOpts.h = _video.height;
        webcam.isTurnOn = true;
    };

    webcam.onStream = function (stream) {
        activeStream = stream;
        return activeStream;
    };
    webcam.downloadSnapshot = function downloadSnapshot(dataURL) {
        window.location.href = dataURL;
    };

    webcam.onError = function (err) {
        webcam.webcamError = err;
    };

    webcam.turnOff = function () {
        webcam.isTurnOn = false;
        if (activeStream && activeStream.getVideoTracks) {
            const checker = typeof activeStream.getVideoTracks === 'function';
            if (checker) {
                return activeStream.getVideoTracks()[0].stop();
            }
            return false;
        }
        return false;
    };

    webcam.getStream = function () {
        var videoSelect = document.querySelector('select#videoSource');
        if (window.stream) {
          window.stream.getTracks().forEach(track => {
            track.stop();
          });
        }
        const videoSource = videoSelect.value;
        const constraints = {
          video: {deviceId: videoSource ? {exact: videoSource} : undefined}
        };
        return navigator.mediaDevices.getUserMedia(constraints).
        then(gotStream).catch(handleError);
        
    }
    function gotStream(stream) {
        var videoSelect = document.querySelector('select#videoSource');
        activeStream = stream; // make stream available to console
        videoSelect.selectedIndex = [...videoSelect.options].    
        findIndex(option => option.text === stream.getVideoTracks()[0].label);
        webcam.channel.video.srcObject = stream;
    }
    function handleError(error) {
        console.error('Error: ', error);
    }

    var service = {
        webcam: webcam
    };
    return service;
}

function config($stateProvider, $urlRouterProvider, $ocLazyLoadProvider) {
    var version = '?v=' + version_file;
    $urlRouterProvider.otherwise("/vcontract/dashboard");

    $stateProvider
        .state('index', {
            abstract: true,
            url: "/vcontract",
            templateUrl: "vcontract/views/common/layout.html" + version,
            resolve: {
                redirectIfNotAuthenticated: _redirectIfNotAuthenticated,
                initData: _getInitData,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        files: [
                            'bower_components/moment/min/moment.min.js',
                            'vcontract/scripts/controllers/authentication.ctrl.js' + version,
                            'vcontract/scripts/controllers/main.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        // Dashboard
        .state('index.dashboard', {
            url: "/dashboard",
            templateUrl: 'vcontract/views/dashboard.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'bower_components/chart.js/dist/Chart.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-datalabels.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-doughnutlabel.js',
                            'bower_components/moment/min/moment.min.js',
                            //Dashboard Contoller
                            'vcontract/scripts/services/authService.js' + version,
                            'vcontract/scripts/controllers/dashboard.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.guide', {
            abstract: true,
            url: "/guide",
            template: '<div ui-view></div>'
        })
        .state('index.guide.document_tutorial', {
            url: "/tai-lieu-huong-dan",
            templateUrl: 'vcontract/views/guide/document_tutorial.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/guideService.js' + version,
                            'vcontract/scripts/controllers/guide/documentGuide.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.guide.detail_document_tutorial', {
            url: "/chi-tiet-tai-lieu-huong-dan/:id",
            templateUrl: 'vcontract/views/guide/detailDocumentTutorial.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signature.js',

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/scripts/services/guideService.js' + version,
                            'vcontract/scripts/controllers/guide/detailDocumentTutorial.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.guide.guide_video', {
            url: "/video-huong-dan",
            templateUrl: 'vcontract/views/guide/guide_video.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/guideService.js' + version,
                            'vcontract/scripts/controllers/guide/guideVideo.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.guide.detail_guide_video', {
            url: "/chi-tiet-video-huong-dan/:id",
            templateUrl: 'vcontract/views/guide/detailGuideVideo.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',


                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/scripts/services/guideService.js' + version,
                            'vcontract/scripts/controllers/guide/detailGuideVideo.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config', {
            abstract: true,
            url: "/config",
            template: '<div ui-view></div>'
        })
        .state('index.config.account', {
            url: "/thong-tin-thue-bao",
            templateUrl: 'vcontract/views/config/account.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/config/account.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.sendDoc', {
            url: "/send-document",
            templateUrl: 'vcontract/views/config/sendDocument.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/config/sendDocument.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.template', {
            url: "/quan-ly-mau-thong-bao",
            templateUrl: 'vcontract/views/config/template.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'vcontract/assets/javascript/libs/ckeditor/ckeditor.js',
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/config/template.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.permission', {
            url: "/permission",
            templateUrl: 'vcontract/views/config/permission.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/config/permission.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.addPermission', {
            url: "/add-permission",
            templateUrl: 'vcontract/views/config/detailPermission.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/config/detailPermission.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.editPermission', {
            url: "/permission/:roleId",
            templateUrl: 'vcontract/views/config/detailPermission.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/config/detailPermission.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.user', {
            url: "/quan-ly-dang-nhap-nguoi-dung",
            templateUrl: 'vcontract/views/config/user.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/config/user.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })


        .state('index.internal', {
            abstract: true,
            url: "/internal",
            template: '<div ui-view></div>'
        })
        .state('index.internal.documentList', {
            url: "/danh-sach-tai-lieu",
            templateUrl: 'vcontract/views/internal/documentList.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/lib/FileSaver.min.js',
                            'vcontract/assets/lib/xlsx.core.min.js',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',
                            'vcontract/assets/javascript/libs/download/download.js',

                            'vcontract/scripts/services/internalService.js' + version,
                            'vcontract/scripts/controllers/internal/documentList.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.internal.approvalManage', {
            url: "/phe-duyet-tai-lieu",
            templateUrl: 'vcontract/views/internal/approvalManage.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'vcontract/assets/styles/fa.css',
                            'vcontract/assets/styles/font-awesome.min.css',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/internalService.js' + version,
                            'vcontract/scripts/controllers/internal/approvalManage.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.internal.signManage', {
            url: "/ky-so-tai-lieu",
            templateUrl: 'vcontract/views/internal/signManage.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'vcontract/assets/styles/fa.css',
                            'vcontract/assets/styles/font-awesome.min.css',
                            'vcontract/assets/javascript/libs/webcam/webcam.min.js',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/internalService.js' + version,
                            'vcontract/scripts/controllers/internal/signManage.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.internal.sendEmail', {
            url: "/lich-su-gui-email",
            templateUrl: 'vcontract/views/internal/sendEmail.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/internalService.js' + version,
                            'vcontract/scripts/controllers/internal/sendEmail.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.internal.sendSms', {
            url: "/lich-su-gui-sms",
            templateUrl: 'vcontract/views/internal/sendSms.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/internalService.js' + version,
                            'vcontract/scripts/controllers/internal/sendSms.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('createInternal', {
            url: "/internal/create",
            templateUrl: 'vcontract/views/internal/createNew.html' + version,
            resolve: {
                initData: _getInitData,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            //selectize
                            'vcontract/assets/styles/font-awesome.min.css',
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'bower_components/moment/min/moment.min.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/signature.js',

                            'vcontract/scripts/services/internalService.js' + version,
                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/internal/createNew.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('editInternal', {
            url: "/internal/edit/:docId",
            templateUrl: 'vcontract/views/internal/createNew.html' + version,
            resolve: {
                initData: _getInitData,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/styles/font-awesome.min.css',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'bower_components/moment/min/moment.min.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/signature.js',

                            'vcontract/scripts/services/internalService.js' + version,
                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/internal/createNew.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('editAddendumInternal', {
            url: "/addendum/edit/:parentId/:docId",
            templateUrl: 'vcontract/views/internal/createNew.html',
            resolve: {
                initData: _getInitData,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/styles/font-awesome.min.css',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'bower_components/moment/min/moment.min.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/signature.js',

                            'vcontract/scripts/services/internalService.js' + version,
                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/internal/createNew.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('supplementInternal', {
            url: "/addendum/:type/:parentId/:docId",
            templateUrl: 'vcontract/views/internal/createNew.html',
            resolve: {
                initData: _getInitData,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/styles/font-awesome.min.css',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'bower_components/moment/min/moment.min.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/signature.js',

                            'vcontract/scripts/services/internalService.js' + version,
                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/internal/createNew.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })

        .state('index.internal.viewDocument', {
            url: "/chi-tiet/:docId",
            templateUrl: 'vcontract/views/internal/viewDocument.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'bower_components/moment/min/moment.min.js',
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',
                            'vcontract/assets/javascript/libs/printjs/print.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/signature.js',

                            'vcontract/assets/javascript/libs/webcam/webcam.min.js',

                            'vcontract/scripts/services/internalService.js' + version,
                            'vcontract/scripts/controllers/internal/viewDocument.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })

        .state('index.document', {
            abstract: true,
            url: "/document",
            template: '<div ui-view></div>'
        })
        .state('index.document.nearExpireDocumentList', {
            url: "/danh-sach-tai-lieu-sap-het-han",
            templateUrl: 'vcontract/views/documentHandler/nearExpireDocumentList.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/documentHandlerService.js' + version,
                            'vcontract/scripts/controllers/documentHandler/nearExpireDocumentList.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.document.nearDocExpireDocumentList', {
            url: "/danh-sach-tai-lieu-sap-het-hieu-luc",
            templateUrl: 'vcontract/views/documentHandler/nearDocExpireDocumentList.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/documentHandlerService.js',
                            'vcontract/scripts/controllers/documentHandler/nearDocExpireDocumentList.ctrl.js'
                        ]
                    }]);
                }]
            }
        })
        .state('index.document.docExpireDocumentList', {
            url: "/danh-sach-tai-lieu-het-hieu-luc",
            templateUrl: 'vcontract/views/documentHandler/docExpireDocumentList.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/documentHandlerService.js',
                            'vcontract/scripts/controllers/documentHandler/docExpireDocumentList.ctrl.js'
                        ]
                    }]);
                }]
            }
        })
        .state('index.document.expiredDocumentList', {
            url: "/danh-sach-tai-lieu-het-han",
            templateUrl: 'vcontract/views/documentHandler/expiredDocumentList.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/documentHandlerService.js' + version,
                            'vcontract/scripts/controllers/documentHandler/expiredDocumentList.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.document.denyDocumentList', {
            url: "/danh-sach-tai-lieu-bi-tu-choi",
            templateUrl: 'vcontract/views/documentHandler/denyDocumentList.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/documentHandlerService.js' + version,
                            'vcontract/scripts/controllers/documentHandler/denyDocumentList.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.document.deleteDocumentList', {
            url: "/danh-sach-tai-lieu-bi-huy-bo",
            templateUrl: 'vcontract/views/documentHandler/deleteDocumentList.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/documentHandlerService.js' + version,
                            'vcontract/scripts/controllers/documentHandler/deleteDocumentList.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })

        .state('index.commerce', {
            abstract: true,
            url: "/commerce",
            template: '<div ui-view></div>'
        })
        .state('index.commerce.documentList', {
            url: "/danh-sach-tai-lieu",
            templateUrl: 'vcontract/views/commerce/documentList.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/lib/FileSaver.min.js',
                            'vcontract/assets/lib/xlsx.core.min.js',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',
                            'vcontract/assets/javascript/libs/download/download.js',

                            'vcontract/scripts/services/commerceService.js' + version,
                            'vcontract/scripts/controllers/commerce/documentList.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.commerce.approvalManage', {
            url: "/phe-duyet-tai-lieu",
            templateUrl: 'vcontract/views/commerce/approvalManage.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'vcontract/assets/styles/fa.css',
                            'vcontract/assets/styles/font-awesome.min.css',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/commerceService.js' + version,
                            'vcontract/scripts/controllers/commerce/approvalManage.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.commerce.signManage', {
            url: "/ky-so-tai-lieu",
            templateUrl: 'vcontract/views/commerce/signManage.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'vcontract/assets/styles/fa.css',
                            'vcontract/assets/styles/font-awesome.min.css',
                            'vcontract/assets/javascript/libs/webcam/webcam.min.js',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/commerceService.js' + version,
                            'vcontract/scripts/controllers/commerce/signManage.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.commerce.sendEmail', {
            url: "/lich-su-gui-email",
            templateUrl: 'vcontract/views/commerce/sendEmail.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/commerceService.js' + version,
                            'vcontract/scripts/controllers/commerce/sendEmail.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.commerce.sendSms', {
            url: "/lich-su-gui-sms",
            templateUrl: 'vcontract/views/commerce/sendSms.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/commerceService.js' + version,
                            'vcontract/scripts/controllers/commerce/sendSms.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('createCommerce', {
            url: "/commerce/create",
            templateUrl: 'vcontract/views/commerce/createNew.html' + version,
            resolve: {
                initData: _getInitData,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/styles/font-awesome.min.css',
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'bower_components/moment/min/moment.min.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/signature.js',

                            'vcontract/scripts/services/commerceService.js' + version,
                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/commerce/createNew.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('supplementCommerce', {
            url: "/addendum/:type/:parentId/:docId",
            templateUrl: 'vcontract/views/commerce/createNew.html',
            resolve: {
                initData: _getInitData,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/styles/font-awesome.min.css',
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'bower_components/moment/min/moment.min.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/signature.js',

                            'vcontract/scripts/services/commerceService.js' + version,
                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/commerce/createNew.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('editCommerce', {
            url: "/commerce/edit/:docId",
            templateUrl: 'vcontract/views/commerce/createNew.html' + version,
            resolve: {
                initData: _getInitData,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/styles/font-awesome.min.css',
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'bower_components/moment/min/moment.min.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/signature.js',

                            'vcontract/scripts/services/commerceService.js' + version,
                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/commerce/createNew.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('editAddendumCommerce', {
            url: "/addendum/edit/:parentId/:docId",
            templateUrl: 'vcontract/views/commerce/createNew.html',
            resolve: {
                initData: _getInitData,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/styles/font-awesome.min.css',
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'bower_components/moment/min/moment.min.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/signature.js',

                            'vcontract/scripts/services/commerceService.js' + version,
                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/services/configService.js' + version,
                            'vcontract/scripts/controllers/commerce/createNew.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })

        .state('index.commerce.viewDocument', {
            url: "/chi-tiet/:docId",
            templateUrl: 'vcontract/views/commerce/viewDocument.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'bower_components/moment/min/moment.min.js',
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',
                            'vcontract/assets/javascript/libs/printjs/print.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/signature.js',

                            'vcontract/assets/javascript/libs/webcam/webcam.min.js',

                            'vcontract/scripts/services/commerceService.js' + version,
                            'vcontract/scripts/controllers/commerce/viewDocument.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })

        .state('index.utilities', {
            abstract: true,
            url: "/tien-ich",
            template: '<div ui-view></div>'
        })
        .state('index.utilities.branch', {
            url: "/chi-nhanh",
            templateUrl: 'vcontract/views/utilities/branch.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/controllers/utilities/branch.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.utilities.position', {
            url: "/chuc-vu",
            templateUrl: 'vcontract/views/utilities/position.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/controllers/utilities/position.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.utilities.employees', {
            url: "/nhan-vien",
            templateUrl: 'vcontract/views/utilities/employees.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/controllers/utilities/employees.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })

        .state('index.utilities.department', {
            url: "/phong-ban",
            templateUrl: 'vcontract/views/utilities/department.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/controllers/utilities/department.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.utilities.document_type', {
            url: "/danh-muc-tai-lieu",
            templateUrl: 'vcontract/views/utilities/document_type.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/controllers/utilities/document_type.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.utilities.document_sample', {
            url: "/quan-ly-tai-lieu-mau",
            templateUrl: 'vcontract/views/utilities/document_sample.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/documentSampleService.js' + version,
                            'vcontract/scripts/controllers/utilities/documentSample.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.utilities.detail_document_sample', {
            url: "/chi-tiet-tai-lieu-mau/:id",
            templateUrl: 'vcontract/views/utilities/detailDocumentSample.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                            'vcontract/assets/lib/pdfLoader.js',
                            'vcontract/assets/lib/signdoc.js',
                            'vcontract/assets/lib/sample_signature.js',

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract/scripts/services/documentSampleService.js' + version,
                            'vcontract/scripts/controllers/utilities/detailDocumentSample.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.utilities.customer', {
            url: "/danh-muc-khach-hang-doi-tac",
            templateUrl: 'vcontract/views/utilities/customer.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'vcontract/scripts/services/utilitiesService.js' + version,
                            'vcontract/scripts/controllers/utilities/customer.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.report', {
            abstract: true,
            url: "/bao-cao",
            template: '<div ui-view></div>'
        })
        .state('index.report.internalDocument', {
            url: "/bao-cao/bao-cao-tai-lieu-noi-bo",
            templateUrl: 'vcontract/views/report/internalDocument.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'bower_components/chart.js/dist/Chart.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-datalabels.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-doughnutlabel.js',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',
                            'vcontract/assets/javascript/libs/download/download.js',

                            'vcontract/scripts/services/reportService.js' + version,
                            'vcontract/scripts/controllers/report/internalDocument.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.report.commerceDocument', {
            url: "/bao-cao/bao-cao-tai-lieu-thuong-mai",
            templateUrl: 'vcontract/views/report/commerceDocument.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'bower_components/chart.js/dist/Chart.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-datalabels.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-doughnutlabel.js',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',
                            'vcontract/assets/javascript/libs/download/download.js',

                            'vcontract/scripts/services/reportService.js' + version,
                            'vcontract/scripts/controllers/report/commerceDocument.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.report.sendMessage', {
            url: "/bao-cao/bao-cao-gui-tin",
            templateUrl: 'vcontract/views/report/sendMessage.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'bower_components/chart.js/dist/Chart.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-datalabels.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-doughnutlabel.js',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',
                            'vcontract/assets/javascript/libs/download/download.js',

                            'vcontract/scripts/services/reportService.js' + version,
                            'vcontract/scripts/controllers/report/sendMessage.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.report.signEkyc', {
            url: "/bao-cao/bao-cao-ekyc",
            templateUrl: 'vcontract/views/report/signEkyc.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'bower_components/chart.js/dist/Chart.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-datalabels.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-doughnutlabel.js',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',
                            'vcontract/assets/javascript/libs/download/download.js',

                            'vcontract/scripts/services/reportService.js' + version,
                            'vcontract/scripts/controllers/report/signEkyc.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.report.signAssignee', {
            url: "/bao-cao/danh-sach-nguoi-ky",
            templateUrl: 'vcontract/views/report/signAssignee.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'bower_components/chart.js/dist/Chart.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-datalabels.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-doughnutlabel.js',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',
                            'vcontract/assets/javascript/libs/download/download.js',

                            'vcontract/scripts/services/reportService.js' + version,
                            'vcontract/scripts/controllers/report/signAssignee.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.actionHistory', {
            url: "/lich-su-tac-dong",
            templateUrl: 'vcontract/views/action_history.html' + version,
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'bower_components/moment/min/moment.min.js',

                            'vcontract/scripts/services/actionHistoryService.js' + version,
                            'vcontract/scripts/controllers/actionHistory.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        //Authentication
        .state('others', {
            abstract: true,
            url: "/vcontract-access",
            templateUrl: "vcontract/views/common/others-view.html"
        })
        .state('others.signin', {
            url: "/sign-in",
            templateUrl: 'vcontract/views/extras/signin.html' + version,
            resolve: {
                skipIfAuthenticated: _skipIfAuthenticated,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        files: [
                            'vcontract/scripts/controllers/authentication.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('others.404', {
            url: "/404",
            templateUrl: 'vcontract/views/extras/404.html'
        })
        .state('others.500', {
            url: "/500",
            templateUrl: 'vcontract/views/extras/500.html'
        })
}
