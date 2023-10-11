
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'UtilitiesSrvc']).controller("PositionCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'ChucVu', PositionCtrl]);
    function PositionCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, ChucVu) {

        var ctrl = this;

        ctrl.searchPosition = {
            status: "-1",
            keyword: ""
        }

        ctrl.selectAll = false;
        ctrl.selected = {};
        ctrl.toggleAll = function (selectAll, selectedItems) {
            for (var id in selectedItems) {
                if (selectedItems.hasOwnProperty(id)) {
                    selectedItems[id] = selectAll;
                }
            }
        }
        ctrl.toggleOne = function (selectedItems) {
            for (var id in selectedItems) {
                if (selectedItems.hasOwnProperty(id)) {
                    if (!selectedItems[id]) {
                        ctrl.selectAll = false;
                        return;
                    }
                }
            }
            ctrl.selectAll = true;
        }

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="positionCtrl.selectAll" ng-change="positionCtrl.toggleAll(positionCtrl.selectAll, positionCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchPosition() }, 300);
                return;
            }
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
                searchData: ctrl.searchPosition,
                isLoading: true
            }
            ChucVu.search(params).then(function (response) {
                ctrl.lstPosition = response.data.data.data;
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
            .withOption('order', [[2, 'asc']])
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
            DTColumnBuilder.newColumn(null).withTitle(titleHtml).notSortable().renderWith(function (data, type, full, meta) {
                ctrl.selected[full.id] = false;
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="positionCtrl.selected[' + data.id + ']" ng-change="positionCtrl.toggleOne(positionCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('position_code').withTitle($filter('translate')('UTILITES.POSITION.POSITION_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('position_code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('UTILITES.POSITION.POSITION_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('note').withTitle($filter('translate')('COMMON.NOTE')).notSortable().renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('note', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('COMMON.USING')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.getDataTableStatusColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "positionCtrl.openEditPositionModal", false, false, false, false, false, false, false, "positionCtrl.onDeletePosition", false);
            }),
        ];

        ctrl.init = function () {
            ChucVu.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
            }, function (response) {
                $scope.initBadRequest(response);
            });
        }();

        $scope.onSearchPosition = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.initNewPosition = function () {
            return {
                name: "",
                position_code: "",
                note: "",
                status: true
            }
        };

        ctrl.openAddPositionModal = function () {
            $scope.editPosition = $scope.initNewPosition();
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdatePosition.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openEditPositionModal = function (row) {
            $scope.editPosition = angular.copy(ctrl.lstPosition[row]);
            $scope.editPosition.status = $scope.editPosition.status == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdatePosition.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.onDeletePosition = function (row) {
            let position = ctrl.lstPosition[row];
            const confirm = NotificationService.confirm($filter('translate')('UTILITES.POSITION.DELETE_CONFIRM', { 'positionName': position.name }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                ChucVu.delete(position).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchPosition();
                        delete ctrl.selected[position.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiPositions = function () {
            let lstPosition = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstPosition.push(id);
                    }
                }
            }
            if (lstPosition.length == 0) {
                NotificationService.error($filter('translate')('UTILITES.POSITION.ERR_NEED_CHOOSE_POSITION'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('UTILITES.POSITION.DELETE_MULTI_CONFIRM', { 'positionLength': lstPosition.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    ChucVu.deleteMulti({ 'lst': lstPosition }).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchPosition();
                            lstPosition.forEach(e => {
                                delete ctrl.selected[e]
                            })
                            NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        } else {
                            NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        }
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }).catch(function (err) {
                });
            }

        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, ChucVu) {
        $scope.onCreateUpdatePosition = function (type) {
            let position = $scope.editPosition;
            let errorMess = "";
            if (position.position_code == "") {
                errorMess += $filter('translate')('UTILITES.POSITION.ERR_EMPTY_POSITION_CODE');
            } else if(position.position_code != "" && !Utils.validateCode(position.position_code)){
                errorMess += $filter('translate')('UTILITES.POSITION.ERR_INVALID_POSITION_CODE');
            }
            if (position.name == "") {
                errorMess += $filter('translate')('UTILITES.POSITION.ERR_EMPTY_POSITION_NAME');
            } else if(position.name != "" && !Utils.validateName(position.name)){
                errorMess += $filter('translate')('UTILITES.POSITION.ERR_INVALID_POSITION_NAME');
            }
            if (errorMess == "") {
                if (!position.id) {
                    ChucVu.create(position).then(function (response) {
                        handleUpdateChucVuResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                } else {
                    ChucVu.update(position).then(function (response) {
                        handleUpdateChucVuResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdateChucVuResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchPosition();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editPosition = $scope.initNewPosition();
                }
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
            } else {
                NotificationService.error($filter('translate')("COMMON.ERR_COMMON_ERROR"), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
