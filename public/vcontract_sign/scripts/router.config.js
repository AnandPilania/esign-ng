var isLoading = false;
angular
	.module('app')
	.config(config)
	.config(['$translateProvider', function ($translateProvider) {

		$translateProvider.useStaticFilesLoader({
			prefix: 'vcontract_sign/lang/',
			suffix: '.json'
		});

		$translateProvider.preferredLanguage(sessionStorage.getItem('sign-lang') || 'vi');
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
				if (config.headers.Authorization == undefined && sessionStorage.getItem('sign-etoken')) {
					config.headers.Authorization = 'Bearer ' + sessionStorage.getItem('sign-etoken');
				}
				return config || $q.when(config);
			},
			response: function (response) {
			    if (isLoading) {
                    $('.loadingapp').addClass('hidden');
                    isLoading = false;
                }
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
	.run(function ($rootScope, $state, $location) {
		$rootScope.$state = $state;
	});

function _skipIfAuthenticated($q, $state, LoginAssignee, $timeout) {
    // var defer = $q.defer();
    return LoginAssignee.checkLoginStatus().then(function (response) {
        if (response.data.data.is_login) {
            // $timeout(function () {
            $state.go('index');
            // });
            // 	defer.reject();
            // } else {
            // 	defer.resolve();
        }
        // return defer.promise;
    }, function () {
    })
}

function _redirectIfNotAuthenticated($q, $state, LoginAssignee, $timeout) {
    // var defer = $q.defer();
    return LoginAssignee.checkLoginStatus().then(function (response) {
        // defer.resolve();
        // return defer.promise;
    }, function () {
        $state.go('signin');
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
    $urlRouterProvider.otherwise("/signin");
	$stateProvider
		.state('index', {
            url: "/home",
            templateUrl: "vcontract_sign/views/index.html",
            resolve: {
                redirectIfNotAuthenticated: _redirectIfNotAuthenticated,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        files: [
                            'vcontract/assets/styles/fa.css',
                            'vcontract/assets/styles/font-awesome.min.css',
                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract_sign/scripts/controllers/mainController.ctrl.js' + version,
                            'vcontract_sign/scripts/controllers/authentication.ctrl.js' + version,
                            'vcontract/assets/javascript/libs/webcam/webcam.min.js',
                        ]
                    }]);
                }]
            }
        })
		.state('signin', {
			url: "/signin",
			templateUrl: '/vcontract_sign/views/login.html',
            resolve: {
                skipIfAuthenticated: _skipIfAuthenticated,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        files: [
                            'vcontract_sign/scripts/controllers/authentication.ctrl.js' + version,
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
        .state('guide', {
            abstract: true,
            url: "/guide",
            template: '<div ui-view></div>'
        })
        .state('guide.document_tutorial', {
            url: "/tai-lieu-huong-dan",
            templateUrl: 'vcontract_sign/views/guide/document_tutorial.html',
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

                            'vcontract_sign/scripts/services/guideService.js' + version,
                            'vcontract_sign/scripts/controllers/guide/documentGuide.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('guide.detail_document_tutorial', {
            url: "/chi-tiet-tai-lieu-huong-dan/:id",
            templateUrl: 'vcontract_sign/views/guide/detailDocumentTutorial.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            // 'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            // 'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            // 'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            // 'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            // 'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            // 'vcontract/assets/javascript/libs/fabric/fabric.min.js',

                             'vcontract_sign/assets/lib/pdfLoader.js',
                            //'vcontract_sign/assets/lib/signature.js',

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract_sign/scripts/services/guideService.js' + version,
                            'vcontract_sign/scripts/controllers/guide/detailDocumentTutorial.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('guide.guide_video', {
            url: "/video-huong-dan",
            templateUrl: 'vcontract_sign/views/guide/guide_video.html',
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

                            'vcontract_sign/scripts/services/guideService.js' + version,
                            'vcontract_sign/scripts/controllers/guide/guideVideo.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('guide.detail_guide_video', {
            url: "/chi-tiet-video-huong-dan/:id",
            templateUrl: 'vcontract_sign/views/guide/detailGuideVideo.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            // 'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            // 'vcontract/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            // 'vcontract/assets/javascript/libs/pdf/pdf-edittext.js',
                            // 'vcontract/assets/javascript/libs/pdfjs/pdf.min.js',
                            // 'vcontract/assets/javascript/libs/pdfjs/pdf.worker.js',
                            // 'vcontract/assets/javascript/libs/fabric/fabric.min.js',


                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'vcontract_sign/scripts/services/guideService.js' + version,
                            'vcontract_sign/scripts/controllers/guide/detailGuideVideo.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
}
