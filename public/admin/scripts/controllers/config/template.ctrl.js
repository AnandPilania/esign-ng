
(function () {
    'use strict';
    angular.module("app", ['ConfigService']).controller("TemplateCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$window', 'DTOptionsBuilder', 'DTColumnBuilder', '$timeout', '$filter', 'QuanLyMauThongBao', TemplateCtrl]);
    function TemplateCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $window, DTOptionsBuilder, DTColumnBuilder, $timeout, $filter, QuanLyMauThongBao) {

        var ctrl = this;

        ctrl.searchTemplate = {
            type: "-1",
            keyword: ""
        }

        ctrl.init = function() {
            QuanLyMauThongBao.init().then(function (response) {
                // ctrl.permission = response.data.data.permission;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="templateCtrl.selectAll" ng-change="templateCtrl.toggleAll(templateCtrl.selectAll, templateCtrl.selected)">';

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
                $scope.initBadRequest(response);
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
                return '<div style="white-space:normal; word-break:break-all;">'+ data.system_template + '</div>';
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                return Utils.renderDataTableAction(meta, "templateCtrl.openEditTemplateModal", false, false, false, false, false, false, false, false, false);
            }),
        ];

        $scope.onSearchTemplate = function () {
            ctrl.dtInstance.rerender();
        }

        ctrl.openEditTemplateModal = function (row) {
            $scope.editTemplate = angular.copy(ctrl.lstTemplate[row]);
            $scope.editTemplate.status = $scope.editTemplate.status == 1;
            $scope.editTemplate.template_content = $scope.editTemplate.system_template;
            $scope.current_count = 0;
            $scope.maximum_count = '/160';
            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/addUpdateTemplate.html',
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
