(function () {
    "use strict";
    angular
        .module("app", ["datatables", "datatables.select"])
        .controller("SignAssigneeCtrl", [
            "$scope", "$rootScope", "$compile", "$uibModal", "$http", "$state", "$window", "$timeout", "DTOptionsBuilder", "DTColumnBuilder", "$filter", "DanhSachNguoiKy", SignAssigneeCtrl,
        ]);
    function SignAssigneeCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, DanhSachNguoiKy) {
        var ctrl = this;


        ctrl.searchSignAssignee = {
            source: "-1",
            document_group_id: "1",
            type: "0",
            start_date: moment().startOf("month").format("DD/MM/YYYY"),
            end_date: moment().endOf("month").format("DD/MM/YYYY"),
        };

        ctrl.lstSource = [
            { id: "0", description: "Web" },
            { id: "1", description: "Api" },
        ];

        ctrl.lstAssigneeType = [
			{ id: '0', description: "INTERNAL.SIGN_MANAGE.CONSIGNEE_TYPE_MY_ORGANIZATION" },
			{ id: '1', description: "INTERNAL.SIGN_MANAGE.CONSIGNEE_TYPE_PATNERS" },
		]

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

        ctrl.init = (function () {
            DanhSachNguoiKy.init().then(
                function (response) {
                    ctrl.permission = response.data.data.permission;
                    ctrl.lstDocumentType = response.data.data.lstDocumentType;
                    ctrl.lstDocumentGroup = response.data.data.lstDocumentGroup;
                },
                function (response) {
                    $scope.initBadRequest(response);
                }
            );
        })();

        ctrl.dtInstance = {};

        function getSignAssigneeData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchSignAssignee() }, 300);
                return;
            }
            ctrl.searchSignAssignee.startDate = Utils.parseDate(ctrl.searchSignAssignee.start_date);
            ctrl.searchSignAssignee.endDate = Utils.parseDateEnd(ctrl.searchSignAssignee.end_date);
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
                searchData: ctrl.searchSignAssignee,
                isLoading: true
            }
            DanhSachNguoiKy.searchSignAssignee(params).then(function (response) {
                ctrl.lstSignAssignee = response.data.data.data;
                ctrl.lstSignAssignee.forEach(e => {
                    e.submit_time = moment(e.submit_time).format("DD/MM/YYYY");
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
            .withOption('order', [[2, 'desc']])
            .withOption('searching', false)
            .withLanguage(Utils.getDataTableLanguage($scope.loginUser.language))
            .withDOM(Utils.getDataTableDom())
            .withOption('lengthMenu', [20, 50, 100])
            .withOption('responsive', true)
            .withOption('processing', true)
            .withOption('serverSide', true)
            .withOption('paging', true)
            .withFnServerData(getSignAssigneeData)
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
            // DTColumnBuilder.newColumn(null).withTitle(titleHtml).notSortable().renderWith(function (data, type, full, meta) {
            //     ctrl.selected[full.id] = false;
            //     return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="documentListCtrl.selected[' + data.id + ']" ng-change="documentListCtrl.toggleOne(documentListCtrl.selected)"/>';
            // }),
            DTColumnBuilder.newColumn('company_name').withTitle($filter('translate')('REPORT.SIGN_ASSIGNEE_COMPANY')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('company_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('full_name').withTitle($filter('translate')('REPORT.SIGN_ASSIGNEE_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('full_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('email').withTitle($filter('translate')('REPORT.EMAIL')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('email', meta.row, data);
            }),
            DTColumnBuilder.newColumn('national_id').withTitle($filter('translate')('REPORT.NATIONAL_ID')).withClass('text-center').renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('national_id', meta.row, data);
            }),
            DTColumnBuilder.newColumn('phone').withTitle($filter('translate')('REPORT.PHONE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('phone', meta.row, data);
            }),
            DTColumnBuilder.newColumn('code').withTitle($filter('translate')('COMMERCE.DOCUMENT_LIST.DOCUMENT_LIST_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('doc_name').withTitle($filter('translate')('COMMERCE.DOCUMENT_LIST.DOCUMENT_LIST_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('doc_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('REPORT.SIGN_ENVIRONMENT')).notSortable().renderWith(function (data, type, full, meta) {
                if (data.source ) {
                    return "Api"
                }
                else {
                    return "Web"
                }
            }),
            DTColumnBuilder.newColumn('submit_time').withTitle($filter('translate')('REPORT.SIGN_ASSIGNEE_SUBMIT_TIME')),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.renderDataTableAction(meta, "signAssigneeCtrl.openEditDocumentListModal", false, false, false, false, false, false, false, false, false, false);
            }),
        ];

        ctrl.openEditDocumentListModal = function (row) {
            let document = ctrl.lstSignAssignee[row];
            if (ctrl.searchSignAssignee.document_group_id == 1) {
                $state.go("index.internal.viewDocument", {docId: document.doc_id});
            } else {
                $state.go("index.commerce.viewDocument", {docId: document.doc_id});
            }
        }

        $scope.onSearchSignAssignee = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.onSearchDocumentGroupChange = function(){
            ctrl.searchSignAssignee.source = "-1";
            $scope.onSearchSignAssignee();
        }

        $scope.export = function () {
            ctrl.searchSignAssignee.startDate = Utils.parseDate(ctrl.searchSignAssignee.start_date);
            ctrl.searchSignAssignee.endDate = Utils.parseDateEnd(ctrl.searchSignAssignee.end_date);
            $(".loadingapp").removeClass("hidden");
            let formData = new FormData();
                formData.append('startDate', ctrl.searchSignAssignee.startDate);
                formData.append('endDate', ctrl.searchSignAssignee.endDate);
                formData.append('keyword', ctrl.searchSignAssignee.keyword);
                formData.append('document_group_id', ctrl.searchSignAssignee.document_group_id);
                formData.append('source', ctrl.searchSignAssignee.source);
                formData.append('type', ctrl.searchSignAssignee.type);
            DanhSachNguoiKy.exportSignAssignee(formData).then(
                function (response) {
                    $(".loadingapp").addClass("hidden");
                    let fileName = "DanhSachNguoiKy_" + ctrl.searchSignAssignee.startDate + "_" + ctrl.searchSignAssignee.endDate + ".xlsx";
                    Utils.downloadFormBinary(response, fileName);
                },
                function (response) {
                    $(".loadingapp").addClass("hidden");
                    $scope.initBadRequest(response);
                }
            );
        };
    }
})();
