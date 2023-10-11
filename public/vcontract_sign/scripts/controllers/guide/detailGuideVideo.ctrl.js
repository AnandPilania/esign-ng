(function () {
    'use strict';
    var app = angular.module("app", ['GuideService'])
    app.config(function($sceDelegateProvider) {
        $sceDelegateProvider.resourceUrlWhitelist([
          'self',
          'https://drive.google.com/**'
        ]);
      });
    app.controller("DetailGuideVideoCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$stateParams', '$window', '$timeout', '$filter', 'QuanLyVideoHuongDan', DetailGuideVideoCtrl]);
    
    function DetailGuideVideoCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $stateParams, $window, $timeout, $filter, QuanLyVideoHuongDan) {
        var ctrl = this;

        ctrl.getGuideVideo = function(videoId) {
            QuanLyVideoHuongDan.getDetail({id: videoId}).then(function(response) {
                if (response.data.success) {
                    ctrl.guideVideo = response.data.data.guideVideo;
                    $scope.guideVideo = ctrl.guideVideo.link;
                }
            });
        }
          
        $scope.getIframeSrc = function(src) {
            return  src;
        };
        ctrl.init = function () {
            let id = $stateParams.id;
            ctrl.getGuideVideo(id);
        }();
        
    }    
})();        