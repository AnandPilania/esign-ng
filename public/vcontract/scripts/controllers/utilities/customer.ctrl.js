
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'UtilitiesSrvc']).controller("CustomerCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'KhachHangDoiTac', CustomerCtrl]);
    function CustomerCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, KhachHangDoiTac) {

        var ctrl = this;

        ctrl.searchCustomer = {
            status: "-1",
            // customer_type: "-1",
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
            KhachHangDoiTac.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="customerCtrl.selectAll" ng-change="customerCtrl.toggleAll(customerCtrl.selectAll, customerCtrl.selected)">';
        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchCustomer() }, 300);
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
                searchData: ctrl.searchCustomer,
                isLoading: true
            }
            KhachHangDoiTac.search(params).then(function (response) {
                ctrl.lstCustomer = response.data.data.data;
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
            .withOption('order', [[3, 'asc']])
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="customerCtrl.selected[' + data.id + ']" ng-change="customerCtrl.toggleOne(customerCtrl.selected)"/>';
            }),
            // DTColumnBuilder.newColumn(null).withTitle('').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
            //     return Utils.getDataTableCustomerTypeColumn(data);
            // }),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_NAME')).withOption('width', '30%').renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('tax_number').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_TAX_NUMBER')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('tax_number', meta.row, data);
            }),
            DTColumnBuilder.newColumn('phone').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_PHONE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('phone', meta.row, data);
            }),
            DTColumnBuilder.newColumn('email').withTitle($filter('translate')('UTILITES.CUSTOMER.CUSTOMER_EMAIL')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('email', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('COMMON.USING')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.getDataTableStatusColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "customerCtrl.openEditCustomerModal", false, false, false, false, false, false, false, "customerCtrl.onDeleteCustomer", false);
            }),
        ];

        $scope.onSearchCustomer = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.initNewCustomer = function() {
            return {
                name: "",
                code: "",
                note: "",
                status: true,
                // customer_type: true,
                phone: "",
                email: "",
                tax_number: "",
                address: "",
                bank_info: "",
                bank_number: "",
                // bank_account: "",
                // representative: "",
                // representative_position: "",
                // contact_phone: "",
                // contact_name: "",
            }
        };

        ctrl.openAddCustomerModal = function () {
            $scope.editCustomer = $scope.initNewCustomer();
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateCustomer.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered modal-dialog-scrollable',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openEditCustomerModal = function (row) {
            $scope.editCustomer = angular.copy(ctrl.lstCustomer[row]);
            $scope.editCustomer.status = $scope.editCustomer.status == 1;
            $scope.editCustomer.customer_type = $scope.editCustomer.customer_type == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateCustomer.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered modal-dialog-scrollable',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.onDeleteCustomer = function (row) {
            let customer = ctrl.lstCustomer[row];
            const confirm = NotificationService.confirm($filter('translate')('UTILITES.CUSTOMER.DELETE_CONFIRM', {'customerName': customer.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                KhachHangDoiTac.delete(customer).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchCustomer();
                        delete ctrl.selected[customer.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiCustomers = function () {
            let lstCustomer = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstCustomer.push(id);
                    }
                }
            }
            if (lstCustomer.length == 0) {
                NotificationService.error($filter('translate')('UTILITES.CUSTOMER.ERR_NEED_CHOOSE_CUSTOMER'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('UTILITES.CUSTOMER.DELETE_MULTI_CONFIRM', { 'customerLength': lstCustomer.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    KhachHangDoiTac.deleteMulti({'lst': lstCustomer}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchCustomer();
                            lstCustomer.forEach(e => delete ctrl.selected[e]);
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

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, KhachHangDoiTac) {
        $scope.onCreateUpdateCustomer = function (type) {
            let customer = $scope.editCustomer;
            let errorMess = "";
            if (customer.code == "") {
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_CODE');
            } else if(customer.code != "" && !Utils.validateCode(customer.code)){
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_INVALID_CUSTOMER_CODE');
            }
            // if (customer.customer_type && customer.tax_number == "") {
            //     errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_TAX_NUMBER');
            // }
            if (customer.name == "") {
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_NAME');
            } else if(customer.name != "" && !Utils.validateVietnameseCharacterWithoutNumber(customer.name)){
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_INVALID_CUSTOMER_NAME');
            }
            if (customer.address == "") {
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_ADDRESS');
            } else if(customer.address != "" && !Utils.validateVietnameseAddress(customer.address)){
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_INVALID_CUSTOMER_ADDRESS');
            }
            if (customer.email == "") {
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_EMAIL');
            }

            if(customer.email != "" && !Utils.validateEmail(customer.email)){
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_INVALID_CUSTOMER_EMAIL');
            }

            if(customer.phone != "" && !Utils.isValidPhoneNumber(customer.phone)){
                errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_INVALID_CUSTOMER_PHONE');
            }
            // if (customer.customer_type && customer.representative == "") {
            //     errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_REPRESENTATIVE');
            // }
            // if (customer.customer_type && customer.representative_position == "") {
            //     errorMess += $filter('translate')('UTILITES.CUSTOMER.ERR_EMPTY_CUSTOMER_REPRESENTATIVE_POSITION');
            // }


            if (errorMess == "") {
                if (!customer.id) {
                    KhachHangDoiTac.create(customer).then(function (response) {
                        handleUpdateKhachHangDoiTacResponse(response, type);
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                } else {
                    KhachHangDoiTac.update(customer).then(function (response) {
                        handleUpdateKhachHangDoiTacResponse(response, type);
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdateKhachHangDoiTacResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchCustomer();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editCustomer = $scope.initNewCustomer();
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
