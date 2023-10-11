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
			prefix: 'admin/lang/',
			suffix: '.json'
		});

		$translateProvider.preferredLanguage(sessionStorage.getItem('ams-lang') || 'vi');
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
                if (config.headers.Authorization == undefined && sessionStorage.getItem('ams-etoken')) {
                    config.headers.Authorization = 'Bearer ' + sessionStorage.getItem('ams-etoken');
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
        $rootScope.first_Login= response.data.data.first_Login;
    })
}

function config($stateProvider, $urlRouterProvider, $ocLazyLoadProvider) {
    var version = '?v=' + version_file;
    $urlRouterProvider.otherwise("/admin/dashboard");

    $stateProvider
        .state('index', {
            abstract: true,
            url: "/admin",
            templateUrl: "admin/views/common/layout.html?v=20210517",
            resolve: {
                redirectIfNotAuthenticated: _redirectIfNotAuthenticated,
                initData: _getInitData,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        files: [
                            'bower_components/moment/min/moment.min.js',
                            'admin/scripts/controllers/authentication.ctrl.js' + version,
                            'admin/scripts/controllers/main.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        // Dashboard
        .state('index.dashboard', {
            url: "/dashboard",
            templateUrl: 'admin/views/dashboard.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'bower_components/chart.js/dist/Chart.min.js',
                            'bower_components/chart.js/dist/chartjs-plugin-datalabels.min.js',
                            'bower_components/moment/min/moment.min.js',
                            //Dashboard Contoller
                            'admin/scripts/services/authService.js' + version,
                            'admin/scripts/controllers/dashboard.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.customer', {
            url: "/quan-ly-khach-hang",
            templateUrl: 'admin/views/config/customer.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            "admin/assets/javascript/libs/pickr/coloris.css",
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',
                            "admin/assets/javascript/libs/pickr/coloris.js",

                            'admin/scripts/services/configService.js' + version,
                            'admin/scripts/controllers/config/customer.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.agency', {
            url: "/dai-ly",
            templateUrl: 'admin/views/config/agency.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'admin/assets/styles/fa.css',
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'admin/scripts/services/configService.js' + version,
                            'admin/scripts/controllers/config/agency.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.serviceConfig', {
            url: "/goi-cuoc",
            templateUrl: 'admin/views/config/serviceConfig.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'bower_components/moment/min/moment.min.js',
                            'admin/assets/styles/fa.css',
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'admin/scripts/services/configService.js' + version,
                            'admin/scripts/controllers/config/serviceConfig.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config', {
            abstract: true,
            url: "/thiet-lap",
            template: '<div ui-view></div>'
        })
        .state('index.config.template', {
            url: "/quan-ly-mau-thong-bao",
            templateUrl: 'admin/views/config/template.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [

                            'admin/assets/javascript/libs/ckeditor/ckeditor.js',
                            //datatable
                            'bower_components/datatables/media/css/jquery.dataTables.css',
                            'bower_components/datatables/media/js/jquery.dataTables.js',
                            'bower_components/angular-datatables/dist/angular-datatables.js',
                            'bower_components/datatables/media/js/dataTables.bootstrap4.js',
                            'bower_components/angular-datatables/dist/plugins/select/angular-datatables.select.js',

                            'admin/scripts/services/configService.js' + version,
                            'admin/scripts/controllers/config/template.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.user', {
            url: "/quan-ly-dang-nhap-nguoi-dung",
            templateUrl: 'admin/views/config/user.html',
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

                            'admin/scripts/services/configService.js' + version,
                            'admin/scripts/controllers/config/user.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.document_tutorial', {
            url: "/quan-ly-tai-lieu-huong-dan",
            templateUrl: 'admin/views/config/document_tutorial.html',
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

                            'admin/scripts/services/configService.js' + version,
                            'admin/scripts/controllers/config/documentTutorial.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.detail_document_tutorial', {
            url: "/chi-tiet-tai-lieu-huong-dan/:id",
            templateUrl: 'admin/views/config/detailDocumentTutorial.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'admin/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'admin/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'admin/assets/javascript/libs/pdf/pdf-edittext.js',
                            'admin/assets/javascript/libs/pdfjs/pdf.min.js',
                            'admin/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'admin/assets/javascript/libs/fabric/fabric.min.js',

                            'admin/assets/lib/pdfLoader.js',
                            'admin/assets/lib/signature.js',

                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'admin/scripts/services/configService.js' + version,
                            'admin/scripts/controllers/config/detailDocumentTutorial.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.guide_video', {
            url: "/quan-ly-video-huong-dan",
            templateUrl: 'admin/views/config/guide_video.html',
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

                            'admin/scripts/services/configService.js' + version,
                            'admin/scripts/controllers/config/guideVideo.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.config.detail_guide_video', {
            url: "/chi-tiet-video-huong-dan/:id",
            templateUrl: 'admin/views/config/detailGuideVideo.html',
            resolve: {
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        insertBefore: '#lazyload-holder',
                        files: [
                            'admin/assets/javascript/libs/jquery-ui/jquery-ui.min.js',
                            'admin/assets/javascript/libs/jquery-ui/jquery-ui.min.css',

                            'admin/assets/javascript/libs/pdf/pdf-edittext.js',
                            'admin/assets/javascript/libs/pdfjs/pdf.min.js',
                            'admin/assets/javascript/libs/pdfjs/pdf.worker.js',
                            'admin/assets/javascript/libs/fabric/fabric.min.js',


                            //selectize
                            'bower_components/selectize/dist/css/selectize.default.css',
                            'bower_components/selectize/dist/js/standalone/selectize.min.js',
                            'bower_components/angular-selectize/dist/angular-selectize.js',

                            'admin/scripts/services/configService.js' + version,
                            'admin/scripts/controllers/config/detailGuideVideo.ctrl.js' + version
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
        .state('index.report.turn_over', {
            url: "/turn-over",
            templateUrl: 'admin/views/report/turnOverReport.html' + version,
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

                            'admin/scripts/services/reportService.js' + version,
                            'admin/scripts/controllers/report/turnOverReport.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.report.customer', {
            url: "/customer",
            templateUrl: 'admin/views/report/customerReport.html' + version,
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

                            'admin/scripts/services/reportService.js' + version,
                            'admin/scripts/controllers/report/customerReport.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('index.report.document', {
            url: "/document",
            templateUrl: 'admin/views/report/documentReport.html' + version,
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

                            'admin/scripts/services/reportService.js' + version,
                            'admin/scripts/controllers/report/documentReport.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })

        //Authentication
        .state('others', {
            abstract: true,
            url: "/admin-access",
            templateUrl: "admin/views/common/others-view.html"
        })
        .state('others.signin', {
            url: "/sign-in",
            templateUrl: 'admin/views/extras/signin.html?v=20210517',
            resolve: {
                skipIfAuthenticated: _skipIfAuthenticated,
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([{
                        serie: true,
                        files: [
                            'admin/scripts/controllers/authentication.ctrl.js' + version
                        ]
                    }]);
                }]
            }
        })
        .state('others.404', {
            url: "/404",
            templateUrl: 'admin/views/extras/404.html'
        })
        .state('others.500', {
            url: "/500",
            templateUrl: 'admin/views/extras/500.html'
        })
}
