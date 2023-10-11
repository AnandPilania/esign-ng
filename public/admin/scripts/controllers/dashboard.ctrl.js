
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
        $rootScope.barLabels =[];
        $rootScope.lineLabels = $rootScope.barLabels["total_price"] = $filter('translate')("DASHBOARD.TURN_OVER");
		$rootScope.barLabels["total_doc_year"] = $filter('translate')("DASHBOARD.TOTAL_DOC");
		$rootScope.barLabels["company"] = $filter('translate')("DASHBOARD.COMPANY");

        ctrl.currentYear = new Date().getFullYear();
		ctrl.dashboardYear = ctrl.currentYear;
        ctrl.barChartLoad = "total_price";
        ctrl.totalDocument = 0;
        ctrl.totalPrice =0;
        ctrl.reRender = 0;

		ctrl.init = function(){
			Login.getInitDashboardData({dashboardYear: ctrl.dashboardYear,isLoading: true}).then(function(response){
				$rootScope.lineData = response.data.data.initData.turnOver;
				$rootScope.isAgency = response.data.data.initData.isAgency;
                $rootScope.agencies = response.data.data.initData.agencies;
                $rootScope.companies = response.data.data.initData.companies;
                ctrl.totalTurnOver =0;
                ctrl.totalAgency = 0;
                ctrl.totalCompany = 0;

                ctrl.totalDocument = response.data.data.initData.totalDocument;
                $rootScope.lineData.forEach(l => {
                    ctrl.totalTurnOver += l;
                })
                $rootScope.agencies.forEach(a => {
                    if (a.status == 1) {
                        ctrl.totalAgency++;
                    }
                })
                $rootScope.companies.forEach(c => {
                    if (c.delete_flag == 0) {
                        ctrl.totalCompany++;
                    }
                })
                ctrl.totalTurnOver = new Intl.NumberFormat().format(ctrl.totalTurnOver);
                ctrl.loadChart(0);
			})
		}
        ctrl.clickArrow = function (state) {
            if(state == 0){
                if(ctrl.dashboardYear != ctrl.currentYear) {
                    ctrl.dashboardYear++;
                    ctrl.init();
                }
            } else if(state == 1) {
                ctrl.dashboardYear--;
                ctrl.init();
            }
        }
        ctrl.loadChart = function (type) {
            $scope.keyAgencies = $filter('orderBy')($rootScope.agencies, '-' + ctrl.barChartLoad);
            $scope.keyCompanies = $filter('orderBy')($rootScope.companies, '-' + ctrl.barChartLoad);
            $rootScope.barLabel = $rootScope.barLabels[ctrl.barChartLoad];
            $rootScope.barData = [];
            $rootScope.barName = [];
            $rootScope.barStatus = [1,1,1,1,1];
            for (var j = 0 ; j < 5; j++) {
                $rootScope.barData[j] = [];
                $rootScope.barName[j] = [];
                $rootScope.barName[j] = "";
                $rootScope.barData[j] = 0;
            }
            var i = 0;
            if($rootScope.isAgency == false){
                $scope.keyAgencies.forEach(function(a) {
                    if( i < 5){
                        $rootScope.barName[i] = a.agency_name;
                        if (a.status == 0) {
                            $rootScope.barStatus[i] = 0;
                        }
                        if(ctrl.barChartLoad == "total_price") {
                            $rootScope.barData[i] = a.total_price;
                        } else if(ctrl.barChartLoad == "total_doc_year"){
                            $rootScope.barData[i] = a.total_doc_year;
                        } else if (ctrl.barChartLoad == "company") {
                            $rootScope.barData[i] = a.company;
                        }
                        i++;
                    }
                })
            } else {
                $scope.keyCompanies.forEach(function(c) {
                    if( i < 5){
                        $rootScope.barName[i] = c.name;
                        if (c.delete_flag == 1) {
                            $rootScope.barStatus[i] = 0;
                        }
                        if(ctrl.barChartLoad == "total_price") {
                            $rootScope.barData[i] = c.total_price;
                        } else if(ctrl.barChartLoad == "total_doc_year"){
                            $rootScope.barData[i] = c.total_doc_year;
                        }
                        i++;
                    }
                })
            }
            if(ctrl.reRender == 0) {
                $timeout(function () {
                    angular.element("#renderDashboardChart").click();
                }, 500);
                ctrl.reRender++;
            } else {
                if (type == 0) {
                    angular.element("#updateDashboardLineChart").click();
                }
                angular.element("#updateDashboardBarChart").click();
            }
        }

        ctrl.init();


	}
})();
