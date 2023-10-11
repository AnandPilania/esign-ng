(function () {
    'use strict';
    angular.module("app", ['ConfigService']).controller("DetailDocumentTutorialCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$stateParams', '$window', '$timeout', '$filter', 'QuanLyTaiLieuHuongDan', DetailDocumentTutorialCtrl]);

    function DetailDocumentTutorialCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $stateParams, $window, $timeout, $filter, TaiLieuHuongDan) {
        var ctrl = this;

        ctrl.getTutorialDocumentFile = function (docId,scale) {
            var formData = new FormData();
            formData.append("id", docId);
            TaiLieuHuongDan.getTutorialDocument(formData).then(function (response) {
                ctrl.docFile = response;
                signature.init(response, scale, true);
            },function (response){
                $scope.initBadRequest(response);
            })
        }
        ctrl.getTutorialDocument = function(docId,scale) {
            TaiLieuHuongDan.getDetail({id: docId}).then(function(response) {
                if (response.data.success) {
                    ctrl.tutorialDocument = response.data.data.tutorial;
                    ctrl.getTutorialDocumentFile(docId,scale);
                }
            },function (response) {
                $scope.initBadRequest(response);
            });
        }

        $scope.init = function () {
            if(!$scope.scale){
                    $scope.scale = "" + 1.5
                }
            let id = $stateParams.id;
            ctrl.getTutorialDocument(id, $scope.scale);
        }
        $scope.init();

        ctrl.onChangeScale = function(){
            $scope.init();
        }
    }
})();
