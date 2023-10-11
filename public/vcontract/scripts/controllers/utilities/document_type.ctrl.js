
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'UtilitiesSrvc']).controller("DocumentTypeCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'PhanLoaiTaiLieu', DocumentTypeCtrl]);
    function DocumentTypeCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, PhanLoaiTaiLieu) {

        var ctrl = this;

        ctrl.searchDocumentType = {
            status: "-1",
            keyword: "",
            document_group_id: "-1",
            dc_style: "-1"
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
            PhanLoaiTaiLieu.init().then(function (response) {
                ctrl.lstDocumentGroup = response.data.data.lstDocumentGroup;
                ctrl.permission = response.data.data.permission;
            }, function(response) {
                $scope.initBadRequest(response);
            });
        }();

        ctrl.dtInstance = {}

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="document_typeCtrl.selectAll" ng-change="document_typeCtrl.toggleAll(document_typeCtrl.selectAll, document_typeCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchDocumentType() }, 300);
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
                searchData: ctrl.searchDocumentType,
                isLoading: true
            }
            PhanLoaiTaiLieu.search(params).then(function (response) {
                ctrl.lstDocumentType = response.data.data.data;
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="document_typeCtrl.selected[' + data.id + ']" ng-change="document_typeCtrl.toggleOne(document_typeCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('dc_type_code').withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_TYPE_DC_TYPE_CODE')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('dc_type_code', meta.row, data);
            }),
            DTColumnBuilder.newColumn('dc_type_name').withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_TYPE_DC_TYPE_NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('dc_type_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('group_name').withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_TYPE_DOCUMENT_GROUP')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('group_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.DEFAULT')).notSortable().renderWith(function (data, type, full, meta) {
                if(data.dc_style == 1){
                    return $filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.BUY_IN');
                } else if(data.dc_style == 2) {
                    return $filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.SELL_OUT');
                } else {
                    return $filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.ELSE');
                }
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
                return Utils.renderDataTableAction(meta, "document_typeCtrl.openEditDocumentTypeModal", false, false, false, false, false, false, false, "document_typeCtrl.onDeleteDocumentType", false);
            }),
        ];

        $scope.onSearchDocumentType = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.initNewDocumentType = function() {
            return {
                dc_type_name: "",
                dc_type_code: "",
                dc_style:"-1",
                document_group_id: "-1",
                is_order_auto: false,
                is_auto_reset: false,
                dc_length: "",
                dc_format: "-1",
                note: "",
                status: true
            }
        };

        ctrl.openAddDocumentTypeModal = function () {
            $scope.editDocumentType = $scope.initNewDocumentType();
            $scope.isEdit = false;
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateDocumentType.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openEditDocumentTypeModal = function (row) {
            $scope.editDocumentType = angular.copy(ctrl.lstDocumentType[row]);
            $scope.isEdit = true;
            $scope.editDocumentType.document_group_id = "" + $scope.editDocumentType.document_group_id;
            $scope.editDocumentType.dc_style="" + $scope.editDocumentType.dc_style;
            $scope.editDocumentType.status = $scope.editDocumentType.status == 1;
            $scope.editDocumentType.is_order_auto = $scope.editDocumentType.is_order_auto == 1;
            $scope.editDocumentType.is_auto_reset = $scope.editDocumentType.is_auto_reset == 1;
            $scope.dc_format_type_warning = $filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_TYPE_FORMAT_WARNING');
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateDocumentType.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.onDeleteDocumentType = function (row) {
            let document_type = ctrl.lstDocumentType[row];
            const confirm = NotificationService.confirm($filter('translate')('UTILITES.DOCUMENT_TYPE.DELETE_CONFIRM', {'document_typeName': document_type.dc_type_name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                PhanLoaiTaiLieu.delete(document_type).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchDocumentType();
                        delete ctrl.selected[document_type.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiDocumentTypes = function () {
            let lstDocumentType = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstDocumentType.push(id);
                    }
                }
            }
            if (lstDocumentType.length == 0) {
                NotificationService.error($filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_NEED_CHOOSE_DOCUMENT_TYPE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('UTILITES.DOCUMENT_TYPE.DELETE_MULTI_CONFIRM', { 'document_typeLength': lstDocumentType.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    PhanLoaiTaiLieu.deleteMulti({'lst': lstDocumentType}).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchDocumentType();
                            lstDocumentType.forEach(e => delete ctrl.selected[e]);
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

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, PhanLoaiTaiLieu) {
        $scope.isOrderAutoChange = function(){
            if($scope.editDocumentType.is_order_auto){
                if($scope.editDocumentType.dc_length != undefined && $scope.editDocumentType.dc_length > 0){
                    $scope.documentLengthChange();
                }
            } else {
                $scope.dc_length_change = '';
                $scope.dc_format_change = '';
            }

        }
        $scope.isAutoResetChange = function(){
            if($scope.editDocumentType.is_auto_reset){
                $scope.dc_length_change = '01';
                $scope.documentFormatChange();
            } else {
                $scope.dc_length_change = '';
                $scope.dc_format_change = '';
                if($scope.editDocumentType.dc_length != undefined && $scope.editDocumentType.dc_length > 0){
                    $scope.documentLengthChange();
                }
            }
        }

        $scope.documentTypeCodeChange = function(){
            if($scope.editDocumentType.is_order_auto && $scope.editDocumentType.dc_length != '' && $scope.editDocumentType.dc_format >= 0){
                $scope.documentFormatChange();
            }
        }

        $scope.documentLengthChange = function(){
            if($scope.editDocumentType.dc_length != undefined && $scope.editDocumentType.dc_length > 0 && !isNaN($scope.editDocumentType.dc_length)){
                $scope.dc_length_change = '1'.padStart(parseInt($scope.editDocumentType.dc_length), '0');
                if($scope.editDocumentType.dc_type_code.length > 0){
                    $scope.documentFormatChange();
                } else {
                    if($scope.editDocumentType.dc_format >= 0){
                        return NotificationService.error($filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_NEED_CHOOSE_DC_TYPE_CODE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    }
                }
            } else {
                $scope.dc_length_change = '';
                $scope.editDocumentType.dc_length = '';
            }
        }

        $scope.documentFormatChange = function(){
            if($scope.editDocumentType.dc_type_code === ''){
                return NotificationService.error($filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_NEED_CHOOSE_DC_TYPE_CODE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
            if(!$scope.editDocumentType.is_auto_reset){
                if($scope.editDocumentType.dc_length == undefined != $scope.editDocumentType.dc_length.length == 0){
                    return NotificationService.error($filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_NEED_CHOOSE_DC_LENGTH'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                }
            }

            var ma_loai = $scope.editDocumentType.dc_type_code;
            var currentDate = new Date(Date.now());
            $scope.dc_format_change = "";
            switch($scope.editDocumentType.dc_format){
                case "{number}/{YY}-{code}":
                    $scope.dc_format_change = $scope.dc_length_change + '/' + currentDate.getFullYear().toString().substr(-2) + "-" + ma_loai;
                    break;
                case "{number}/{YYYY}-{code}":
                    $scope.dc_format_change = $scope.dc_length_change + '/' + currentDate.getFullYear() + "-" + ma_loai;
                    break;
                case "{number}/{MM}/{YY}-{code}":
                    $scope.dc_format_change = $scope.dc_length_change + '/' + String(currentDate.getMonth() + 1).padStart(2, '0') + "/" + currentDate.getFullYear().toString().substr(-2) + "-" + ma_loai;
                    break;
                case "{number}/{MM}/{YYYY}-{code}":
                    $scope.dc_format_change = $scope.dc_length_change + '/' + String(currentDate.getMonth() + 1).padStart(2, '0') + "/" + currentDate.getFullYear() + "-" + ma_loai;
                    break;
                default:
                    $scope.dc_format_change = "";
                    break;
            }
        }

        if($scope.editDocumentType.is_order_auto == 1){
            if(!$scope.editDocumentType.is_auto_reset){
                $scope.dc_length_change = '1'.padStart(parseInt($scope.editDocumentType.dc_length), '0');
                $scope.documentFormatChange();
            }else{
                $scope.dc_length_change = '01';
                $scope.documentFormatChange();
            }
        } else {
            $scope.dc_length_change = '';
            $scope.dc_format_change = '';
        }

        $scope.onCreateUpdateDocumentType = function (type) {
            let document_type = $scope.editDocumentType;
            let errorMess = "";
            if(document_type.document_group_id == "-1" || document_type.document_group_id == ""){
                errorMess += $filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_NEED_CHOOSE_DOCUMENT_GROUP');
            }
            if(document_type.dc_style == "-1" || document_type.dc_style == ""){
                errorMess += $filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_NEED_CHOOSE_DOCUMENT_STYLE');
            }
            if (document_type.dc_type_code == "") {
                errorMess += $filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_EMPTY_DC_TYPE_CODE');
            } else if(document_type.dc_type_code != "" && !Utils.validateCode(document_type.dc_type_code)){
                errorMess += $filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_INVALID_DC_TYPE_CODE');
            }
            if (document_type.dc_type_name == "") {
                errorMess += $filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_EMPTY_DC_TYPE_NAME');
            } else if(document_type.dc_type_name != "" && !Utils.validateVietnameseCharacterWithNumber(document_type.dc_type_name)){
                errorMess += $filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_INVALID_DC_TYPE_NAME');
            }
            if (document_type.is_order_auto == "1"){
                if(!document_type.is_auto_reset){
                    if(document_type.dc_length == null || document_type.dc_length == ""){
                    errorMess += $filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_NEED_CHOOSE_DC_LENGTH');
                    }
                }
                if(document_type.dc_format == null || document_type.dc_format == "-1"){
                    errorMess += $filter('translate')('UTILITES.DOCUMENT_TYPE.ERR_NEED_CHOOSE_DC_FORMAT');
                }
            }

            if (errorMess == "") {
                if (!document_type.id) {
                    PhanLoaiTaiLieu.create(document_type).then(function (response) {
                        handleUpdatePhanLoaiTaiLieuResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                } else {
                    PhanLoaiTaiLieu.update(document_type).then(function (response) {
                        handleUpdatePhanLoaiTaiLieuResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdatePhanLoaiTaiLieuResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchDocumentType();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editDocumentType = $scope.initNewDocumentType();
                    $scope.dc_length_change = '';
                    $scope.dc_format_change = '';
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
