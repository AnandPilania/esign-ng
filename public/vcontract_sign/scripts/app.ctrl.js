
(function () {
	'use strict';
	angular.module("app").controller("AppCtrl", ['$scope', '$http', '$window', '$timeout', '$filter','LoginAssignee', AppCtrl]);
	function AppCtrl($scope, $http, $window, $timeout, $filter, LoginAssignee) {

		var ctrl = this;

        ctrl.getConfigData = function(){
            LoginAssignee.getConfigData().then(function (response) {
                if (response.data.success) {
                    $scope.configData = response.data.data.configData;
                }
            },function (response) {
                NotificationService.error($filter('translate')(response.data.message));
            });
        }

        ctrl.getConfigData();

        $scope.lstDocumentState = [
            { 'id': '-1', 'description': 'COMMON.STATUS' },
            { 'id': '1', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_SAVE_DRAFT' },
            { 'id': '2', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_WAIT_APPROVE' },
            { 'id': '3', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_WAIT_SIGN' },
            { 'id': '4', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_DENIED' },
            { 'id': '5', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_EXPIRED' },
            { 'id': '6', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_CANCELED' },
            { 'id': '7', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_NOT_VERIFY' },
            { 'id': '8', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_COMPLETED' },
            { 'id': '9', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_VERIFYING' },
            { 'id': '10', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_VERIFYING_FAIL' },
        ]

        $scope.lstAssigneeRole = [
            { 'id': '1', 'description': 'DOCUMENT.APPROVE_ROLE' },
            { 'id': '2', 'description': 'DOCUMENT.SIGN_ROLE' },
            { 'id': '3', 'description': 'DOCUMENT.STORAGE_ROLE' },
        ]

        $scope.lstScale = [
            { 'value': '1.5', 'description': '150%' },
            { 'value': '1', 'description': '100%' },
            { 'value': '0.9', 'description': '90%' },
            { 'value': '0.8', 'description': '80%' },
            { 'value': '0.75', 'description': '75%' },
            { 'value': '0.67', 'description': '67%' },
            { 'value': '0.5', 'description': '50%' },
            { 'value': '0.33', 'description': '33%' },
        ]

        $scope.themeConfig = {
            "footerUrl": footerUrl,
        }

	}
})();
