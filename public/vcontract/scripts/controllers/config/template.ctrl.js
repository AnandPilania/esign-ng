
(function () {
    'use strict';
    angular.module("app", ['ConfigService']).controller("TemplateCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$window', 'DTOptionsBuilder', 'DTColumnBuilder', '$timeout', '$filter', 'QuanLyMauThongBao', TemplateCtrl]);
    function TemplateCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, DTOptionsBuilder, DTColumnBuilder, $timeout, $filter, QuanLyMauThongBao) {

        var ctrl = this;

        ctrl.searchTemplate = {
            status:"-1",
            type: "-1",
            keyword: ""
        }

        $scope.init = function() {
            QuanLyMauThongBao.init({isLoading: true}).then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.lstTemplate = response.data.data.lstTemplate;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        };

        $scope.init();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="templateCtrl.selectAll" ng-change="templateCtrl.toggleAll(templateCtrl.selectAll, templateCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchTemplate() }, 300);
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
                searchData: ctrl.searchTemplate,
                isLoading: true
            }
            QuanLyMauThongBao.search(params).then(function (response) {
                ctrl.lstTemplate = response.data.data.data;
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
            .withOption('order', [[1, 'asc']])
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
            DTColumnBuilder.newColumn('template_description').withTitle($filter('translate')('CONFIG.TEMPLATE.TEMPLATE_DESCRIPTION')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('template_description', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('CONFIG.TEMPLATE.TEMPLATE_TYPE')).renderWith(function (data, type, full, meta) {
                if(data.type){
                    return $filter('translate')('CONFIG.TEMPLATE.TEMPLATE_EMAIL');
                } else {
                    return $filter('translate')('CONFIG.TEMPLATE.TEMPLATE_SMS')
                }
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('CONFIG.TEMPLATE.TEMPLATE_CONTENT')).notSortable().withOption('width', '50%').renderWith(function (data, type, full, meta) {
                if(data.company_template){
                    return '<div style="white-space:normal; word-break:break-word;">'+ data.company_template + '</div>';
                } else {
                    return '<div style="white-space:normal; word-break:break-word;">'+ data.system_template + '</div>';
                }
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('CONFIG.TEMPLATE.USE_TEMPLATE')).withClass('text-center').notSortable().withOption('width', '10%').renderWith(function (data, type, full, meta) {
                if(data.ct_status != null ){
                    if (data.ct_status == 1) {
                        return '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-helpdesk text-teal" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><polyline points="9 11 12 14 20 6"></polyline><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9"></path></svg>'
                    } else {
                        return '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-helpdesk text-secondary" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><polyline points="9 11 12 14 20 6"></polyline><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9"></path></svg>'
                    }
                }
                else{
                    return Utils.getDataTableStatusColumn(data)
                }
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "templateCtrl.openEditTemplateModal", false, false, false, false, false, false, false, false, false);
            }),
        ];

        $scope.onSearchTemplate = function () {
            ctrl.dtInstance.rerender();
        }

        ctrl.openEditTemplateModal = function (row) {
            $scope.editTemplate = angular.copy(ctrl.lstTemplate[row]);
            $scope.editTemplate.ct_status = $scope.editTemplate.ct_status == null ? $scope.editTemplate.status == 1 : $scope.editTemplate.ct_status == 1
            $scope.editTemplate.template_content = $scope.editTemplate.company_template != null && $scope.editTemplate.company_template != '' ? $scope.editTemplate.company_template : $scope.editTemplate.system_template;
            $scope.current_count = 0;
            $scope.maximum_count = '/160';
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateTemplate.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }
    }

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, QuanLyMauThongBao) {

        $scope.smsCount = function(){
            var content = $scope.editTemplate.template_content;
            $scope.current_count = content ? content.length : 0;
            if (!/[^\u0000-\u007f]/.test(content) || content == undefined || content == '') {
                $scope.maximum_count = '/' + ((Math.floor($scope.current_count / 160) + 1) * 160);
            } else {
                $scope.maximum_count = '/' + ((Math.floor($scope.current_count / 65) + 1) * 65);
            }
        }
        $scope.smsCount();

        $scope.onCreateUpdateTemplate = function () {
            let template = $scope.editTemplate;
            template.isLoading = true;

            QuanLyMauThongBao.update(template).then(function (response) {
                NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                $scope.onSearchTemplate();
                $uibModalInstance.close(false);
            }, function (response) {
                $scope.initBadRequest(response);
            });
        }

        $scope.cancel = function () {
            $uibModalInstance.close(false);
        };
    }
})();
