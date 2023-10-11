
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'ConfigService']).controller("CustomerCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'QuanLyKhachHang', CustomerCtrl]);

    function CustomerCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, QuanLyKhachHang) {

        var ctrl = this;

        ctrl.searchCustomer = {
            status: "-1",
            keyword: "",
            agency_id: $scope.loginUser.role_id == 2 ? "" + $scope.loginUser.agency_id : "-1",
            service_id: "-1"
        }

        ctrl.searchDocumentList = {
            keyword: "",
            company_id: "-1",
            state: "-1",
            start_date: moment().startOf('month').format('DD/MM/YYYY'),
            end_date: moment().endOf('month').format('DD/MM/YYYY')
        }

        ctrl.lstSource = [
            { 'id': 0 , 'description' : $filter('translate')('CONFIG.CUSTOMER.CHOOSE_UNIT_AUTH.CORE')},
            { 'id': 1 , 'description' : $filter('translate')('CONFIG.CUSTOMER.CHOOSE_UNIT_AUTH.MOBIFONE')},
            { 'id': 2 , 'description' : $filter('translate')('CONFIG.CUSTOMER.CHOOSE_UNIT_AUTH.VIETTEL')},
        ];

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
            QuanLyKhachHang.init().then(function (response) {
                ctrl.lstAgency = response.data.data.lstAgency;
                ctrl.lstService = response.data.data.lstService;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="customerCtrl.selectAll" ng-change="customerCtrl.toggleAll(customerCtrl.selectAll, customerCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
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
            QuanLyKhachHang.search(params).then(function (response) {
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
                $scope.initBadRequest(response);
            })
        }
        function getData2(sSource, aoData, fnCallback, oSettings) {
            ctrl.searchDocumentList.startDate = Utils.parseDate(ctrl.searchDocumentList.start_date);
            ctrl.searchDocumentList.endDate = Utils.parseDateEnd(ctrl.searchDocumentList.end_date);
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
            searchData: ctrl.searchDocumentList,
            isLoading: true
            }
            QuanLyKhachHang.searchDocumentList(params).then(function (response) {
                ctrl.totalDocument = response.data.data.recordsTotal;
                fnCallback(response.data.data);
            }, function (response) {
                var records = {
                    'draw': draw,
                    'recordsTotal': 0,
                    'recordsFiltered': 0,
                    'data': []
                };
                fnCallback(records);
                $scope.initBadRequest(response);
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="customerCtrl.selected[' + data.id + ']" ng-change="customerCtrl.toggleOne(customerCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('CONFIG.CUSTOMER.CUSTOMER_NAME')),
            DTColumnBuilder.newColumn('agency_name').withTitle($filter('translate')('CONFIG.CUSTOMER.AGENCY_NAME')),
            DTColumnBuilder.newColumn('tax_number').withTitle($filter('translate')('CONFIG.CUSTOMER.TAX')),
            DTColumnBuilder.newColumn('phone').withTitle($filter('translate')('CONFIG.CUSTOMER.PHONE')),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('CONFIG.CUSTOMER.CUSTOMER_EFFECT')).withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {

                return Utils.getDataTableStatusColumn(data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                // return Utils.renderDataTableAction(meta, "customerCtrl.openEditCustomerModal", false, false, false, false, false, false, false, "customerCtrl.onDeleteCustomer", false);
                var html = '<div class="action-button-group">';
                // edit
                if (data.expired_date && new Date(data.expired_date) < new Date()) {
                    html += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="customerCtrl.reNewService(${meta.row});" data-toggle="tooltip" data-placement="left" title="Gia hạn gói cước"><svg width="18" height="18" viewBox="0 0 24 24" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\" xmlns="http://www.w3.org/2000/svg"><path d="M17.738 6C16.208 4.764 14.097 4 12 4a8 8 0 1 0 8 8 1 1 0 0 1 2 0c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2c2.549 0 5.107.916 7 2.449V3a1 1 0 0 1 2 0v4a1 1 0 0 1-1 1h-4a1 1 0 0 1 0-2h1.738z" fill="#000" fill-rule="nonzero"/></svg></button>`;
                }

                html += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="customerCtrl.openEditCustomerModal(${meta.row});" data-toggle="tooltip" data-placement="left" title="Chi tiết"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><circle cx=\"12\" cy=\"12\" r=\"2\" /><path d=\"M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7\" /></svg></button>`;
                // config
                html += `&nbsp;<button style="padding:0; height:36px;" type="button" class="btn btn-outline-primary btn-icon action" href="" ng-click="customerCtrl.onOpenConfigModal(${meta.row});" data-toggle="tooltip" data-placement="left" title="Cấu hình"> <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path
                    d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z">
                </path>
                <circle cx="12" cy="12" r="3"></circle>
                </svg></button>`;
                //change pass
                html += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="customerCtrl.onChangePass(${meta.row});" data-toggle="tooltip" data-placement="left" title="Đổi mật khẩu"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3\" /><circle cx=\"12\" cy=\"11\" r=\"1\" /><line x1=\"12\" y1=\"12\" x2=\"12\" y2=\"14.5\" /></svg></button>`;
                // view detail
                html += `&nbsp;<button style="padding:0; height:36px;" type="button" class="btn btn-outline-primary btn-icon action" href="" ng-click="customerCtrl.onViewDetailCustomer(${meta.row});" data-toggle="tooltip" data-placement="left" title="Chi tiết dịch vụ"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <rect x="0" y="0" width="24" height="24"/>
                    <path d="M10.5,5 L19.5,5 C20.3284271,5 21,5.67157288 21,6.5 C21,7.32842712 20.3284271,8 19.5,8 L10.5,8 C9.67157288,8 9,7.32842712 9,6.5 C9,5.67157288 9.67157288,5 10.5,5 Z M10.5,10 L19.5,10 C20.3284271,10 21,10.6715729 21,11.5 C21,12.3284271 20.3284271,13 19.5,13 L10.5,13 C9.67157288,13 9,12.3284271 9,11.5 C9,10.6715729 9.67157288,10 10.5,10 Z M10.5,15 L19.5,15 C20.3284271,15 21,15.6715729 21,16.5 C21,17.3284271 20.3284271,18 19.5,18 L10.5,18 C9.67157288,18 9,17.3284271 9,16.5 C9,15.6715729 9.67157288,15 10.5,15 Z" fill="#000000"/>
                    <path d="M5.5,8 C4.67157288,8 4,7.32842712 4,6.5 C4,5.67157288 4.67157288,5 5.5,5 C6.32842712,5 7,5.67157288 7,6.5 C7,7.32842712 6.32842712,8 5.5,8 Z M5.5,13 C4.67157288,13 4,12.3284271 4,11.5 C4,10.6715729 4.67157288,10 5.5,10 C6.32842712,10 7,10.6715729 7,11.5 C7,12.3284271 6.32842712,13 5.5,13 Z M5.5,18 C4.67157288,18 4,17.3284271 4,16.5 C4,15.6715729 4.67157288,15 5.5,15 C6.32842712,15 7,15.6715729 7,16.5 C7,17.3284271 6.32842712,18 5.5,18 Z" fill="#000000" opacity="0.3"/>
                </g></button>`;
                // delete
                html += `&nbsp;<button type="button" class="btn btn-outline-danger btn-icon action" ng-click="customerCtrl.onDeleteCustomer(${meta.row});" data-toggle="tooltip" data-placement="left" title="Xóa"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><line x1=\"4\" y1=\"7\" x2=\"20\" y2=\"7\" /><line x1=\"10\" y1=\"11\" x2=\"10\" y2=\"17\" /><line x1=\"14\" y1=\"11\" x2=\"14\" y2=\"17\" /><path d=\"M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12\" /><path d=\"M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3\" /></svg></button>`;

                html += '</div>';
                return html;
            }),
        ];
        ctrl.dtOptions2 = DTOptionsBuilder.newOptions()
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
            .withFnServerData(getData2)
            .withOption('createdRow', function (row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            })
            .withOption('headerCallback', function (header) {
                ctrl.selectAll = false;
                $compile(angular.element(header).contents())($scope);
            })
        ctrl.dtColumns2 = [
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn('full_name').withTitle($filter('translate')('COMMERCE.DOCUMENT_LIST.LIST_CREATOR')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('full_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('sent_date').withTitle($filter('translate')('COMMERCE.DOCUMENT_LIST.DOCUMENT_LIST_SENT_DATE')),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('COMMERCE.DOCUMENT_LIST.DOCUMENT_LIST_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('finished_date').withTitle($filter('translate')('COMMERCE.DOCUMENT_LIST.DOCUMENT_LIST_IS_COMPLETED')),
        ];

        $scope.onSearchCustomer = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.initNewCustomer = function() {
            return {
                name: "",
                agency_id: "",
                source_method: "-1",
                tax_number: "",
                fax_number: "",
                address: "",
                phone: "",
                email: "",
                fax: "",
                representative: "",
                service_id: "-1",
                password: "",
                password_confirm: "",
                status: true,
                option: false
            }
        };

        ctrl.openAddCustomerModal = function () {
            $scope.lstService = ctrl.lstService
            $scope.editCustomer = $scope.initNewCustomer();
            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/addUpdateCustomer.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openEditCustomerModal = function (row) {
            $scope.lstService = ctrl.lstService
            $scope.isEdit = true;
            $scope.editCustomer = angular.copy(ctrl.lstCustomer[row]);
            $scope.editCustomer.agency_id = "" + $scope.editCustomer.agency_id;
            $scope.editCustomer.service_id ="" + $scope.editCustomer.service_id;
            $scope.editCustomer.source_method ="" + $scope.editCustomer.source_method;
            $scope.editCustomer.status = $scope.editCustomer.status == 1;
            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/addUpdateCustomer.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.onViewDetailCustomer = function(row){
            $scope.row = row;
            $scope.editCustomer = angular.copy(ctrl.lstCustomer[row]);
            ctrl.searchDocumentList.company_id = $scope.editCustomer.id;
            $scope.onViewDetailCustomer = ctrl.onViewDetailCustomer;
            $scope.onOpenDocumentList = ctrl.onOpenDocumentList;
            QuanLyKhachHang.getDetail($scope.editCustomer).then(function(response){
                if (response.data.success) {
                    $scope.editCustomer.totalDocument = response.data.data.totalDocument;
                    $scope.editCustomer.totalDocumentCompleted = response.data.data.totalDocumentCompleted;
                    $scope.editCustomer.service = response.data.data.service;
                    if($scope.editCustomer.expired_date != null){
                        $scope.editCustomer.expired_time = Utils.formatDateNoTime(new Date($scope.editCustomer.expired_date))
                    }
                    else{
                        $scope.editCustomer.expired_time = null
                    }
                    $uibModal.open({
                        animation: true,
                        templateUrl: 'admin/views/modal/customerServiceDetail.html',
                        windowClass: "fade show modal-blur",
                        size: 'lg modal-dialog-centered',
                        backdrop: 'static',
                        backdropClass: 'show',
                        keyboard: false,
                        controller: ModalInstanceCtrl,
                        scope: $scope
                    });
                }
            }, function(response){
                $scope.initBadRequest(response);
            })
        }
        ctrl.reNewService = function(row){
            $scope.row = row;
            $scope.editCustomer = angular.copy(ctrl.lstCustomer[row]);
            QuanLyKhachHang.reNewService($scope.editCustomer).then(function(response){
                if (response.data.success) {
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    $scope.onSearchCustomer();
                }
            }, function(response){
                $scope.initBadRequest(response);
            })
        }

        ctrl.onChangePass = function(row){
            $scope.editCustomer = angular.copy(ctrl.lstCustomer[row]);
            $scope.editCustomer.password = "";
            $scope.editCustomer.password_confirm = "";
            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/changePasswordCustomer.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.preViewConfigCompany = {
            company_id : "",
            logo_dashboard : "",
            logo_login : "",
            logo_sign : "",
            logo_background : "",
            fa_icon : "",
            theme_header_color : "",
            theme_footer_color : "",
            file_size_upload : "5",
            step_color : "",
            text_color : "",
            name_app : "",
            loading : "",
        }

        ctrl.uploadFile = [];

        ctrl.onOpenConfigModal = function(row) {
            $scope.row = row;
            $scope.preViewConfigCompany = ctrl.preViewConfigCompany;
            $scope.tab = 1;
            $scope.uploadFile = ctrl.uploadFile;
            $scope.editCustomer = angular.copy(ctrl.lstCustomer[row]);
            ctrl.searchDocumentList.company_id = $scope.editCustomer.id;
            QuanLyKhachHang.getDataConfigCompany($scope.editCustomer).then(function(response){
                if (response.data.success) {
                    $scope.dataConfig = response.data.data.configCompany;
                    $scope.preViewConfigCompany = {
                        company_id : $scope.dataConfig.company_id,
                        logo_dashboard : $scope.dataConfig.logo_dashboard,
                        logo_login : $scope.dataConfig.logo_login,
                        logo_sign : $scope.dataConfig.logo_sign,
                        logo_background : $scope.dataConfig.logo_background,
                        fa_icon : $scope.dataConfig.fa_icon,
                        theme_header_color : $scope.dataConfig.theme_header_color,
                        theme_footer_color : $scope.dataConfig.theme_footer_color,
                        file_size_upload : $scope.dataConfig.file_size_upload,
                        step_color : $scope.dataConfig.step_color,
                        text_color : $scope.dataConfig.text_color,
                        name_app : $scope.dataConfig.name_app,
                        loading : $scope.dataConfig.loading,
                    }
                    $uibModal.open({
                        animation: true,
                        templateUrl: 'admin/views/modal/addUpdateCompanyConfig.html',
                        windowClass: "fade show modal-blur",
                        size: 'fullscreen modal-dialog-centered',
                        backdrop: 'static',
                        backdropClass: 'show',
                        keyboard: false,
                        controller: ModalInstanceCtrl,
                        scope: $scope
                    });
                }
            }, function(response){
                $scope.initBadRequest(response);
            })
        }

        ctrl.onOpenDocumentList = function() {
            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/documentList.html',
                windowClass: "fade show modal-blur",
                size: 'xl modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.onDeleteCustomer = function (row) {
            let customer = ctrl.lstCustomer[row];
            const confirm = NotificationService.confirm($filter('translate')('CONFIG.CUSTOMER.DELETE_CONFIRM', {'customerName': customer.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                QuanLyKhachHang.delete(customer).then(function (response) {
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
            let lstCustomers = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstCustomers.push(id);
                    }
                }
            }
            if (lstCustomers.length == 0) {
                NotificationService.error($filter('translate')('CONFIG.CUSTOMER.ERR_NEED_CHOOSE_CUSTOMER'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('CONFIG.CUSTOMER.DELETE_MULTI_CONFIRM', { 'customerLength': lstCustomers.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    QuanLyKhachHang.deleteMulti({'lst': lstCustomers}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchCustomer();
                            lstCustomers.forEach(e => delete ctrl.selected[e]);
                            NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                        } else {
                            NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        }
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }).catch(function (err) {
                });
            }
        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, QuanLyKhachHang) {
        $scope.onCreateUpdateCustomer = function (type) {
            let customer = $scope.editCustomer;
            let errorMess = "";
            // if (!customer.company_code) {
            //     errorMess += $filter('translate')('CONFIG.CUSTOMER.ERR_EMPTY_CUSTOMER_CODE');
            // }
            if (!customer.name) {
                errorMess += $filter('translate')('CONFIG.CUSTOMER.ERR_EMPTY_CUSTOMER_NAME');
            }
            if (!customer.tax_number) {
                errorMess += $filter('translate')('CONFIG.CUSTOMER.ERR_EMPTY_TAX');
            }
            if (!customer.address) {
                errorMess += $filter('translate')('CONFIG.CUSTOMER.ERR_EMPTY_ADDRESS');
            }
            if (customer.doc_num && customer.doc_num < -1) {
                errorMess += $filter('translate')('CONFIG.CUSTOMER.INVALID_NUMBER');
            }
            if (!customer.email) {
                errorMess += $filter('translate')('CONFIG.CUSTOMER.ERR_EMPTY_EMAIL');
            } else {
                if(!Utils.validateEmail(customer.email)){
                    errorMess += $filter('translate')('CONFIG.CUSTOMER.ERR_INVALID_EMAIL');
                }
            }
            if (!customer.phone) {
                errorMess += $filter('translate')('CONFIG.CUSTOMER.ERR_EMPTY_PHONE');
            } else {
                if(!Utils.isValidPhoneNumber(customer.phone)){
                    errorMess += $filter('translate')('CONFIG.CUSTOMER.ERR_INVALID_PHONE');
                }
            }
            if (customer.service_id == "undefined" || customer.service_id == -1) {
                errorMess += $filter('translate')("CONFIG.CUSTOMER.ERR_INVALID_SERVICE");
            }
            if (customer.source_method == "undefined" || customer.source_method == -1) {
                errorMess += $filter('translate')("CONFIG.CUSTOMER.ERR_INVALID_SOURCE_METHOD");
            }
            if(!customer.id){
                if (customer.password == ""){
                    errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_PASSWORD');
                }

                if(customer.password != ""){
                    if(!Utils.validatePassword(customer.password)){
                        errorMess += $filter('translate')('CONFIG.USER.ERR_INVALID_USER_PASSWORD');
                    } else if(customer.password_confirm != "" && customer.password != customer.password_confirm){
                        errorMess += $filter('translate')('CONFIG.USER.ERR_INVALID_USER_PASSWORD_CONFIRM');
                    }
                }

                if (customer.password_confirm == ""){
                    errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_PASSWORD_CONFIRM');
                }
            }
            if (errorMess == "") {
                if (!customer.id) {
                    QuanLyKhachHang.create(customer).then(function (response) {
                        handleUpdateQuanLyKhachHangResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                } else {
                    QuanLyKhachHang.update(customer).then(function (response) {
                        handleUpdateQuanLyKhachHangResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.changeTab = function(tab) {
            $scope.tab = tab;
        }

        $scope.setFile = function(element,type) {
            var currentFile = element.files[0];
            var reader = new FileReader();

            reader.onload = function(event) {

                switch(type) {
                    case 0:
                        $scope.preViewConfigCompany.logo_login = event.target.result;
                        $scope.uploadFile['logo_login'] = currentFile;
                        break;
                    case 1:
                        $scope.preViewConfigCompany.logo_background = event.target.result;
                        $scope.uploadFile['logo_background'] = currentFile;
                        break;
                    case 2:
                        $scope.preViewConfigCompany.fa_icon = event.target.result;
                        $scope.uploadFile['fa_icon'] = currentFile;
                        break;
                    case 3:
                        $scope.preViewConfigCompany.loading = event.target.result;
                        $scope.uploadFile['loading'] = currentFile;
                        break;
                    case 4:
                        $scope.preViewConfigCompany.logo_dashboard = event.target.result;
                        $scope.uploadFile['logo_dashboard'] = currentFile;
                        break;
                }
              $scope.$apply()

            }
            // when the file is read it triggers the onload event above.
            reader.readAsDataURL(element.files[0]);
        }

        $scope.onUpdateConfigCompany = function() {
            QuanLyKhachHang.updateConfigCompany($scope.preViewConfigCompany).then(function (response) {
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
            }, function (response) {
                $scope.initBadRequest(response);
            });
        }


        $scope.openDocumentList = function () {
            $uibModalInstance.close(false);
            $scope.onOpenDocumentList();
        }

        $scope.cancelDocument = function() {
            $uibModalInstance.close(false);
            $scope.isDocumentList = false;
            $scope.onViewDetailCustomer($scope.row);
        }

        function handleUpdateQuanLyKhachHangResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchCustomer();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editCustomer = $scope.initNewCustomer();
                }
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
            } else {
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.generateRandomUserPassword = function(user) {
            let password = Utils.generatePassword();
            user.password = password;
            user.password_confirm = password;
        }

        $scope.onChangePasswordCustomer = function(data){
            let errorMess = "";
            if (data.password == ""){
                errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_PASSWORD');
            }

            if(data.password != ""){
                if(!Utils.validatePassword(data.password)){
                    errorMess += $filter('translate')('CONFIG.USER.ERR_INVALID_USER_PASSWORD');
                } else if(data.password_confirm != "" && data.password != data.password_confirm){
                    errorMess += $filter('translate')('CONFIG.USER.ERR_INVALID_USER_PASSWORD_CONFIRM');
                }
            }

            if (data.password_confirm == ""){
                errorMess += $filter('translate')('CONFIG.USER.ERR_EMPTY_USER_PASSWORD_CONFIRM');
            }

            if (errorMess == "") {
                QuanLyKhachHang.changePass(data).then(function (response) {
                    $scope.onSearchCustomer();
                    $uibModalInstance.close(false);
                    NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };

    }
})();
