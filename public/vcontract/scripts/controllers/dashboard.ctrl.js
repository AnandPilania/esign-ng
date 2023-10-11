
(function() {
	'use strict';
	angular.module("app", ['AuthSrvc']).controller("DashboardCtrl", ['$scope', '$rootScope', '$http', '$filter', 'Login', '$timeout',DashboardCtrl]);
	function DashboardCtrl($scope, $rootScope, $http, $filter, Login, $timeout) {
		
		var ctrl = this;

		$rootScope.lineCtx_label = [
			$filter('translate')("DASHBOARD.JANUARY"),
			$filter('translate')("DASHBOARD.FEBRUARY"),
			$filter('translate')("DASHBOARD.MARCH"),
			$filter('translate')("DASHBOARD.APRIL"),
			$filter('translate')("DASHBOARD.MAY"),
			$filter('translate')("DASHBOARD.JUNE"),
			$filter('translate')("DASHBOARD.JULY"),
			$filter('translate')("DASHBOARD.AUGUST"),
			$filter('translate')("DASHBOARD.SEPTEMBER"),
			$filter('translate')("DASHBOARD.OCTOBER"),
			$filter('translate')("DASHBOARD.NOVEMBER"),
			$filter('translate')("DASHBOARD.DECEMBER"),
		]

		$rootScope.document_type_label = [
			$filter('translate')("HEADER.INTERNAL"),
			$filter('translate')("HEADER.COMMERCE"),
		];

		ctrl.dashboardYear = new Date().getFullYear();
		
		ctrl.init = function(){
			Login.getInitDashboardData({ isLoading: true }).then(function (response) {
				ctrl.lstDocument = response.data.data.initData.lstDocument;
				ctrl.totalCompletedDocument = Utils.getTotal(ctrl.lstDocument.filter(e => e.document_state == 8));
				ctrl.totalWaitingApproveDocument = Utils.getTotal(ctrl.lstDocument.filter(e => e.document_state == 2));
				ctrl.totalWaitingSignDocument = Utils.getTotal(ctrl.lstDocument.filter(e => e.document_state == 3));
				ctrl.totalCancelledDocument = Utils.getTotal(ctrl.lstDocument.filter(e => e.document_state == 6));
				ctrl.company = response.data.data.initData.company[0];
				$rootScope.internalData = [0,0,0,0,0,0,0,0,0,0,0,0];
				$rootScope.commerceData = [0,0,0,0,0,0,0,0,0,0,0,0];
				$rootScope.pieData = [0, 0];
				ctrl.lstDocument.forEach(element => {
					if(element.document_type == 1){
						$rootScope.internalData[element.month - 1] += element.total;
						$rootScope.pieData[0] += element.total;
					} else if(element.document_type == 2){
						$rootScope.commerceData[element.month - 1] += element.total;
						$rootScope.pieData[1] += element.total;
					}
				});
				$timeout(function () {
					angular.element("#renderDashboardChart").click();
				}, 500);
			})
		}
		
		ctrl.init();

		
	}
})();
