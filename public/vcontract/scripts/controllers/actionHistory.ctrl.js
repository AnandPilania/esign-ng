
(function() {
	'use strict';
	angular.module("app", ['HistorySrvc']).controller("ActionHistoryCtrl", ['$scope', '$compile','$uibModal', '$http', '$filter', 'Login', '$timeout', '$state', 'DTOptionsBuilder', 'DTColumnBuilder', 'LichSuTacDong', ActionHistoryCtrl]);
	function ActionHistoryCtrl($scope, $compile, $uibModal, $http, $filter, Login, $timeout, $state, DTOptionsBuilder, DTColumnBuilder, LichSuTacDong) {


		var ctrl = this;

		ctrl.searchActionHistory = {
			keyword: "",
            user: "-1",
            action_group: "1",
			action: "-1",
			start_date: moment().startOf("month").format("DD/MM/YYYY"),
            end_date: moment().endOf("month").format("DD/MM/YYYY"),
		};

        ctrl.init = function() {
            LichSuTacDong.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

		ctrl.dtInstance = {}

		var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="documentListCtrl.selectAll" ng-change="documentListCtrl.toggleAll(documentListCtrl.selectAll, documentListCtrl.selected)">';
        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchActionHistory() }, 300);
                return;
            }
            ctrl.searchActionHistory.startDate = Utils.parseDate(ctrl.searchActionHistory.start_date);
            ctrl.searchActionHistory.endDate = Utils.parseDateEnd(ctrl.searchActionHistory.end_date);
            var draw = aoData[0].value;
            var order = aoData[2].value[0];
            order.columnName = aoData[1].value[order.column].data;
            var start = aoData[3].value;
            var length = aoData[4].value;
            var params = {
                draw: draw,
                order: order,
                start: start,
                limit: length,
                searchData: ctrl.searchActionHistory,
                isLoading: true
            }
            LichSuTacDong.search(params).then(function (response) {
                ctrl.lstActionHistory = response.data.data.data;
                ctrl.lstAction = response.data.data.lstAction;
                ctrl.lstUsers = response.data.data.lstUsers;
                ctrl.lstActionHistory.forEach(e => {
                    e.created_at = e.created_at != null ? moment(e.created_at).format("DD/MM/YYYY HH:mm:ss") : '';
                });
                fnCallback(response.data.data);
            }, function (response) {
                var records = {
                    'draw': draw,
                    'recordsTotal': 0,
                    'recordsFiltered': 0,
                    'data': []
                };
                fnCallback(records);
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            })
        }

        function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // $compile(nRow)($scope);
        }

        ctrl.dtOptions = DTOptionsBuilder.newOptions()
            .withPaginationType('full_numbers')
            .withDisplayLength(20)
            .withOption('order', [[4, 'desc']])
            .withOption('searching', false)
            .withLanguage(Utils.getDataTableLanguage($scope.loginUser.language))
            .withDOM(Utils.getDataTableDom())
            .withOption('lengthMenu', [20, 50, 100])
            .withOption('responsive', true)
            .withOption('processing', true)
            .withOption('serverSide', true)
            .withOption('paging', true)
            .withFnServerData(getData)
            .withOption('createdRow', function (row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            })
            .withOption('headerCallback', function (header) {
                ctrl.selectAll = false;
                $compile(angular.element(header).contents())($scope);
            })
        ctrl.dtColumns = [
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('ACTION_HISTORY.CREATED_BY')).withOption('width', '20%'),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('ACTION_HISTORY.ACTION.PLACE')).withOption('width', '15%').notSortable().renderWith(function (data, type, full, meta) {
                if (data.action_type == 0) {
                    return $filter('translate')('Web');
                }
                if (data.action_type == 1) {
                    return $filter('translate')('Admin');
                }
                if (data.action_type == 2) {
                    return $filter('translate')('Remote');
                }
                if (data.action_type == 3) {
                    return $filter('translate')('Api');
                }
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('ACTION_HISTORY.ACTION.DEFAULT')).renderWith(function (data, type, full, meta){
                var html = "";
                if(data.note){
                    if(data.note.includes(" ")){
                        html = $scope.avoidXSSRender('note', meta.row, data.note);
                    }else{
                        html = $filter('translate')('LOG.' + data.action,{'content': data.note});
                    }
                }
                return html;
                // return $scope.avoidXSSRender('note', meta.row, data);
            }),
            DTColumnBuilder.newColumn('created_at').withTitle($filter('translate')('ACTION_HISTORY.CREATED_AT')),
            DTColumnBuilder.newColumn(null).withTitle('').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.renderDataTableAction(meta, "actionHistoryCtrl.openViewHistory", false, false, false, false, false, false, false, false, false);
            }),
        ];

		$scope.onSearchActionHistory = function () {
            ctrl.dtInstance.rerender();
        }

        ctrl.openViewHistory = function (row) {
            let actionHistory = ctrl.lstActionHistory[row];
            $scope.action = actionHistory.action;
            let name = actionHistory.name;
            let email = actionHistory.email;
            let time = actionHistory.created_at;
            let rawData = JSON.parse(actionHistory.raw_log);
            let html = "<div><table class='table'>";
            Object.keys(rawData).forEach(function(key) {
                let data;
                if(typeof rawData[key] == 'object') {
                    data = JSON.stringify(rawData[key], null, '<br>');
                }else{
                    data = rawData[key];
                }
                html += `<tr><td class="col-4">${key}</td><td>: ${data}</td></tr>`;
            });
            html += "</table></div>";
            $scope.viewDetailHistory = html;

            $scope.detailHistory = $filter('translate')('ACTION_HISTORY.DETAIL_HISTORY', {'name': name,'email': email, 'time': time});
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/viewHistoryAction.html',
                windowClass: "fade show modal-blur",
                size: 'md modal-dialog-scrollable ',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }


        function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, LichSuTacDong) {

            $scope.cancel = function () {
                $uibModalInstance.close(false);
            };
        }

	}
})();
