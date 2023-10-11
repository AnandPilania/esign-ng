
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'UtilitiesSrvc']).controller("BranchCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'ChiNhanh', BranchCtrl]);
    function BranchCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, ChiNhanh) {

        var ctrl = this;

        ctrl.searchBranch = {
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

        ctrl.init = function() {
            ChiNhanh.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="branchCtrl.selectAll" ng-change="branchCtrl.toggleAll(branchCtrl.selectAll, branchCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
                if (!ctrl.permission) {
                    setTimeout(() => { $scope.onSearchBranch() }, 300);
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
                searchData: ctrl.searchBranch,
                isLoading: true
            }
            ChiNhanh.search(params).then(function (response) {
                ctrl.lstBranch = response.data.data.data;
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="branchCtrl.selected[' + data.id + ']" ng-change="branchCtrl.toggleOne(branchCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('UTILITES.BRANCH.BRANCH_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('tax_number').withTitle($filter('translate')('UTILITES.BRANCH.BRANCH_TAX_NUMBER')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('tax_number', meta.row, data);
            }),
            DTColumnBuilder.newColumn('address').withTitle($filter('translate')('UTILITES.BRANCH.BRANCH_ADDRESS')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('address', meta.row, data);
            }),
            DTColumnBuilder.newColumn('phone').withTitle($filter('translate')('UTILITES.BRANCH.BRANCH_PHONE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('phone', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('UTILITES.BRANCH.STATUS.DEFAULT')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.getDataTableStatusColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "branchCtrl.openEditBranchModal", false, false, false, false, false, false, false, "branchCtrl.onDeleteBranch", false);
            }),
        ];

        $scope.onSearchBranch = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.initNewBranch = function() {
            return {
                name: "",
                status: true
            }
        };

        ctrl.openAddBranchModal = function () {
            $scope.editBranch = $scope.initNewBranch();
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateBranch.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openEditBranchModal = function (row) {
            $scope.editBranch = angular.copy(ctrl.lstBranch[row]);
            $scope.editBranch.status = $scope.editBranch.status == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateBranch.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.onDeleteBranch = function (row) {
            let branch = ctrl.lstBranch[row];
            branch.isLoading = true;
            const confirm = NotificationService.confirm($filter('translate')('UTILITES.BRANCH.DELETE_CONFIRM', {'branchName': branch.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                ChiNhanh.delete(branch).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchBranch();
                        delete ctrl.selected[branch.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiBranchs = function () {
            let lstBranch = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstBranch.push(id);
                    }
                }
            }
            if (lstBranch.length == 0) {
                NotificationService.error($filter('translate')('UTILITES.BRANCH.ERR_NEED_CHOOSE_BRANCH'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('UTILITES.BRANCH.DELETE_MULTI_CONFIRM', { 'branchLength': lstBranch.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    ChiNhanh.deleteMulti({'lst': lstBranch, isLoading: true}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchBranch();
                            lstBranch.forEach(e => delete ctrl.selected[e]);
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
    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, ChiNhanh) {
        $scope.onCreateUpdateBranch = function (type) {
            let branch = $scope.editBranch;
            let errorMess = "";
            if (!branch.name) {
                errorMess += $filter('translate')('UTILITES.BRANCH.ERR_EMPTY_BRANCH_NAME');
            }
            if (branch.email && !Utils.validateEmail(branch.email)) {
                errorMess += $filter('translate')('UTILITES.BRANCH.ERR_INVALID_BRANCH_EMAIL');
            }
            if (branch.phone && !Utils.isValidPhoneNumber(branch.phone)) {
                errorMess += $filter('translate')('UTILITES.BRANCH.ERR_INVALID_BRANCH_PHONE');
            }
            if (!branch.address) {
                errorMess += $filter('translate')('UTILITES.BRANCH.ERR_EMPTY_BRANCH_ADDRESS');
            }
            branch.isLoading = true;

            if (errorMess == "") {
                if (!branch.id) {
                    ChiNhanh.create(branch).then(function (response) {
                        handleUpdateChiNhanhResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                } else {
                    ChiNhanh.update(branch).then(function (response) {
                        handleUpdateChiNhanhResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdateChiNhanhResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchBranch();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editBranch = $scope.initNewBranch();
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
