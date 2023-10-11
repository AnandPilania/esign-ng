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
			prefix: 'tracuu/lang/',
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
        console.log(response);
        if (response.data.data.is_login) {
            // $timeout(function () {
            $state.go('index.documentList');
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
        $rootScope.firstLogin  = response.data.data.firstLogin ;
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

    var service = {
        webcam: webcam
    };
    return service;
}

function config($stateProvider, $urlRouterProvider, $ocLazyLoadProvider) {
    var version = '?v=' + version_file;
    $urlRouterProvider.otherwise("/tracuu/tim-kiem");

    $stateProvider
        .state('index', {
            abstract: true,
            url: "/tracuu",
            templateUrl: "tracuu/views/common/layout.html" + version,
            resolve: {
                redirectIfNotAuthenticated: _redirectIfNotAuthenticated,
                initData: _getInitData,         
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        files: [
                            'bower_components/moment/min/moment.min.js',
                            'tracuu/scripts/controllers/authentication.ctrl.js' + version,
                            'tracuu/scripts/controllers/main.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.documentList', {
            url: "/tim-kiem",
            templateUrl: 'tracuu/views/documentList.html' + version,
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
                            'tracuu/assets/javascript/libs/download/download.js',

                            'tracuu/scripts/services/searchService.js' + version,
                            'tracuu/scripts/controllers/documentList.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.viewDocument', {
            url: "/chi-tiet/:docId",
            templateUrl: 'tracuu/views/viewDocument.html' + version,
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

                            'tracuu/assets/javascript/libs/pdfjs/pdf.min.js',
                            'tracuu/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'tracuu/assets/javascript/libs/fabric/fabric.min.js',
                            'tracuu/assets/javascript/libs/printjs/print.min.js',

                            'tracuu/assets/lib/pdfLoader.js',
                            'tracuu/assets/lib/signdoc.js',
                            'tracuu/assets/lib/signature.js',

                            'vcontract/assets/javascript/libs/webcam/webcam.min.js',

                            'tracuu/scripts/services/searchService.js' + version,
                            'tracuu/scripts/controllers/viewDocument.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('others', {
            abstract: true,
            url: "/tracuu-access",
            templateUrl: "tracuu/views/common/others-view.html"
        })
        .state('others.signin', {
            url: "/sign-in",
            templateUrl: 'tracuu/views/extras/signin.html' + version,
            resolve: {
                skipIfAuthenticated: _skipIfAuthenticated,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        files: [
                            'tracuu/scripts/controllers/authentication.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('others.404', {
            url: "/404",
            templateUrl: 'tracuu/views/extras/404.html'
        })
        .state('others.500', {
            url: "/500",
            templateUrl: 'tracuu/views/extras/500.html'
        })
}    