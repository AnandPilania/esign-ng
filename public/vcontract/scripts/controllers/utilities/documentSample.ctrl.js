
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'DocumentSampleService']).controller("DocumentSampleCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'QuanLyTaiLieuMau', DocumentSampleCtrl]);
    function DocumentSampleCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, QuanLyTaiLieuMau) {

        var ctrl = this;

        ctrl.searchDocumentSample = {
            document_type: "-1",
            document_type_id: "-1",
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

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="documentSampleCtrl.selectAll" ng-change="documentSampleCtrl.toggleAll(documentSampleCtrl.selectAll, documentSampleCtrl.selected)">';

        function getData(sSource, aoData, fnCallback, oSettings) {
            if (!ctrl.permission) {
                setTimeout(() => { $scope.onSearchDocumentSample() }, 300);
                return;
            }
            ctrl.lstDocumentTypeTmp = angular.copy(ctrl.lstDocumentType);
            ctrl.lstDocumentTypeSearch = ctrl.lstDocumentTypeTmp.filter(e => e.document_type == ctrl.searchDocumentSample.document_type);
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
                searchData: ctrl.searchDocumentSample,
                isLoading: true
            }
            QuanLyTaiLieuMau.search(params).then(function (response) {
                ctrl.lstDocumentSample = response.data.data.data;
                ctrl.lstDocumentSample.forEach((e) => {
                    e.created_at = moment(e.created_at).format("DD/MM/YYYY");
                    e.updated_at = moment(e.updated_at).format("DD/MM/YYYY");
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
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="documentSampleCtrl.selected[' + data.id + ']" ng-change="documentSampleCtrl.toggleOne(documentSampleCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('created_at').withTitle($filter('translate')('DOCUMENT_SAMPLE.CREATED_AT')),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('DOCUMENT_SAMPLE.NAME')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('document_type_name').withTitle($filter('translate')('UTILITES.DOCUMENT_TYPE.DOCUMENT_TYPE_DOCUMENT_GROUP')).renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('document_type_name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('updated_at').withTitle($filter('translate')('DOCUMENT_SAMPLE.UPDATED_AT')),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                if (ctrl.permission.is_write != 1) {
                    return "";
                }
                return Utils.renderDataTableAction(meta, "documentSampleCtrl.openUpdateDocumentSampleModal", false, false, false, false, false, false, false, "documentSampleCtrl.onDeleteDocumentSample", false, false, "documentSampleCtrl.openEditDocumentSampleModal");
            }),
        ];

        ctrl.init = function () {
            QuanLyTaiLieuMau.init().then(function (response) {
                ctrl.permission = response.data.data.permission;
                ctrl.lstDocumentType = response.data.data.lstDocumentType;
                ctrl.lstDocumentGroup = response.data.data.lstDocumentGroup;
            }, function (response) {
                NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                $state.go("index.dashboard");
            });
        }();

        $scope.onSearchDocumentSample = function () {
            ctrl.dtInstance.rerender();
        }

        $scope.onSearchDocumentGroupChange = function(){
            ctrl.searchDocumentSample.document_type_id = "-1";
            $scope.onSearchDocumentSample();
        }

        $scope.initNewDocumentSample = function () {
            return {
                name: "",
                description: "",
                expired_month:"",
                expired_type: "",
                document_type: "1",
                document_type_id: "-1",
                is_encrypt: false,
                encrypt_password: "",
            }
        };

        $scope.onUploadFile = function (files) {
            let editDocumentSample = $scope.editDocumentSample;
            let errorMess = "";
            if (editDocumentSample.document_type_id == "-1") {
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_DOCUMENT_TYPE');
            }
            if (!editDocumentSample.name) {
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_NAME');
            }

            editDocumentSample.files = [];
            files = [...files].reverse();
            var formData = new FormData();
            formData.append("id", editDocumentSample.id);
            formData.append("document_type", editDocumentSample.document_type);
            if (!errorMess) {
                $.each(files, function (i, e) {
                    let file = {
                        name: e.name,
                        size: Utils.bytesToSize(e.size)
                    }
                    editDocumentSample.files.push(file);
                    formData.append(`files[${i}]`, e);
                });
                var fp = $("#uploadFile");
                var lg = fp[0].files.length;
                var items = fp[0].files;
                var fileSize = 0;
                if (lg > 0) {
                    for (var i = 0; i < lg; i++) {
                        fileSize = fileSize + items[i].size;
                    }
                    if (fileSize > ($scope.configData.file_size_upload * 1024 * 1024)) {
                        NotificationService.error($filter('translate')('DOCUMENT_SAMPLE.ERR_OVERSIZE_UPLOAD'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        selectedEl.html('');
                        $('#uploadFile').val('');
                        return false;
                    }
                }

                $('.loadingapp').removeClass('hidden');
                $.ajax({
                    method: 'POST',
                    enctype: 'multipart/form-data',
                    url: "/api/v1/quan-ly-tai-lieu-mau/upload-file",
                    contentType: false,
                    processData: false,
                    headers: {
                        'Authorization': 'Bearer ' + sessionStorage.getItem('etoken')
                    },
                    data: formData,
                    success: function (response) {
                        $('.loadingapp').addClass('hidden');
                        var lstFileUploaded = response.data.lstFileUploaded;
                        for(let i=0; i < lstFileUploaded.length; i++){
                            editDocumentSample.files[i].file_id = lstFileUploaded[i].file_id;
                            editDocumentSample.files[i].name = lstFileUploaded[i].name;
                            editDocumentSample.files[i].extension = lstFileUploaded[i].extension;
                            editDocumentSample.files[i].size = Utils.bytesToSize(lstFileUploaded[i].size);
                            editDocumentSample.files[i].size_raw = lstFileUploaded[i].size;
                            editDocumentSample.files[i].path = lstFileUploaded[i].path;
                        }
                        editDocumentSample.files.forEach(e => $scope.selectedFile.push(e));
                        $scope.$digest();
                    },
                    error: function (response) {
                        $('.loadingapp').addClass('hidden');
                        NotificationService.error($filter('translate')(response.responseJSON.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                        $(`#progressBar`).css('width',  '0%');
                    },
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                percentComplete = parseInt(percentComplete * 100);
                                $(`#progressBar`).css('width', percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    }
                });

            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        $scope.removeFile = function(file, index){
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT_SAMPLE.REMOVE_FILE_CONFIRM', {'fileName': file.name}), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function(response){
                $scope.selectedFile.splice(index, 1);
                if($scope.selectedFile.length == 0){
                    $(`#progressBar`).css('width', '0%');
                }
                $scope.$digest();
            }).catch(function (err) {
            });
        }

        ctrl.openAddDocumentSampleModal = function () {
            $scope.editDocumentSample = $scope.initNewDocumentSample();
            $scope.selectedFile = [];
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateDocumentSample.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }
        ctrl.openUpdateDocumentSampleModal = function (row) {
            $scope.editDocumentSample = angular.copy(ctrl.lstDocumentSample[row]);
            $scope.selectedFile = [];
            $scope.editDocumentSample.name = $scope.editDocumentSample.name;
            $scope.editDocumentSample.document_type = $filter('translate')($scope.editDocumentSample.document_type);
            $scope.editDocumentSample.document_type_id = $filter('translate')($scope.editDocumentSample.document_type_id);
            $scope.editDocumentSample.expired_type = $scope.editDocumentSample.expired_type;
            $scope.editDocumentSample.is_encrypt = $scope.editDocumentSample.is_encrypt ? true : false;
            console.log($scope.editDocumentSample.is_encrypt)
            $scope.editDocumentSample.encrypt_password = $scope.editDocumentSample.save_password;
            if($scope.editDocumentSample.expired_type == 0){
                $scope.editDocumentSample.nonExpire = true;
            } else if($scope.editDocumentSample.expired_type == 2){
                $scope.editDocumentSample.isExpire = true;
                $scope.editDocumentSample.expired_month = $scope.editDocumentSample.expired_month;
            }
            $scope.editDocumentSample.description = $scope.editDocumentSample.description;
            QuanLyTaiLieuMau.getFiles({id : $scope.editDocumentSample.id}).then(function (files) {
                files.data.data.forEach( f => {
                    let file = {
                        name: f.file_name_raw,
                        size: Utils.bytesToSize(f.file_size_raw),
                        extension: f.file_type_raw,
                        size_raw: f.file_size_raw,
                        path: f.file_path_raw,
                        file_id: f.file_id,
                    }
                    $scope.selectedFile.push(file);

                });
            });
            $uibModal.open({
                animation: true,
                templateUrl: 'vcontract/views/modal/addUpdateDocumentSample.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openEditDocumentSampleModal = function (row) {
            $state.go("index.utilities.detail_document_sample", {'id': ctrl.lstDocumentSample[row].id});
            // $scope.editDocumentSample = angular.copy(ctrl.lstDocumentSample[row]);
            // $scope.editDocumentSample.document_type = "" + $scope.editDocumentSample.document_type;
            // $scope.editDocumentSample.document_type_id = "" + $scope.editDocumentSample.document_type_id;
            // QuanLyTaiLieuMau.getDetail($scope.editDocumentSample).then(function(response){
            //     if (response.data.success) {
            //         $scope.selectedFile = response.data.data.lstDetail;
            //         $scope.selectedFile.forEach((e) => {
            //             e.name = e.file_name_raw;
            //             e.size = Utils.bytesToSize(e.file_size_raw);
            //             e.size_raw = e.file_size_raw;
            //             e.file_id = e.file_id;
            //             e.path = e.file_path_raw;
            //             e.extension = e.file_type_raw;
            //         })
            //         $uibModal.open({
            //             animation: true,
            //             templateUrl: 'vcontract/views/modal/addUpdateDocumentSample.html',
            //             windowClass: "fade show modal-blur",
            //             size: 'lg modal-dialog-centered',
            //             backdrop: 'static',
            //             backdropClass: 'show',
            //             keyboard: false,
            //             controller: ModalInstanceCtrl,
            //             scope: $scope
            //         });
            //     }
            // }, function(response){
            //     NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            // })
        }

        ctrl.onDeleteDocumentSample = function (row) {
            let editDocumentSample = ctrl.lstDocumentSample[row];
            const confirm = NotificationService.confirm($filter('translate')('DOCUMENT_SAMPLE.DELETE_CONFIRM', { 'document_name': editDocumentSample.name }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                QuanLyTaiLieuMau.delete(editDocumentSample).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchDocumentSample();
                        delete ctrl.selected[editDocumentSample.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiDocumentSamples = function () {
            let lstDocumentSample = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstDocumentSample.push(id);
                    }
                }
            }
            if (lstDocumentSample.length == 0) {
                NotificationService.error($filter('translate')('DOCUMENT_SAMPLE.ERR_NEED_CHOOSE_DOCUMENT_SAMPLE'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('DOCUMENT_SAMPLE.DELETE_MULTI_CONFIRM', { 'documentSampleLength': lstDocumentSample.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    QuanLyTaiLieuMau.deleteMulti({ 'lst': lstDocumentSample }).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchDocumentSample();
                            lstDocumentSample.forEach(e => {
                                delete ctrl.selected[e];
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

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, QuanLyTaiLieuMau) {
        $scope.onCreateUpdateDocumentSample = function (type) {
            let editDocumentSample = $scope.editDocumentSample;
            if(editDocumentSample.isExpire){
                editDocumentSample.expired_type = 2;
            }
            if(editDocumentSample.nonExpire){
                editDocumentSample.expired_type = 0;
                editDocumentSample.expired_month = '';
            }
            $scope.editDocumentSample.files = angular.copy($scope.selectedFile);
            let errorMess = "";
            if(!editDocumentSample.isExpire && !editDocumentSample.nonExpire) {
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EXPIRED');
            }
            if (editDocumentSample.isExpire && !editDocumentSample.expired_month) {
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EXPIRED_DATE');
            } else if (editDocumentSample.isExpire && !Utils.isNumeric(editDocumentSample.expired_month)) {
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_INVALID_EXPIRED_DATE');
            }
            if (editDocumentSample.name == "") {
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_NAME');
            } else if(editDocumentSample.name != "" && !Utils.validateName(editDocumentSample.name)){
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_INVALID_NAME');
            }

            if(editDocumentSample.description != "" && !Utils.validateVietnameseAddress(editDocumentSample.description)){
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_INVALID_DESCRIPTION');
            }

            if(editDocumentSample.document_type_id == -1){
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_DOCUMENT_TYPE');
            }
            if(editDocumentSample.is_encrypt && !editDocumentSample.encrypt_password){
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.ERR_EMPTY_ENCRYPT_PASSWORD');
            }

            if ($scope.selectedFile.length == 0) {
                errorMess += $filter('translate')('DOCUMENT_SAMPLE.NOT_UPLOAD_FILE');
            }

            if (errorMess == "") {
                if (!editDocumentSample.id) {
                    QuanLyTaiLieuMau.create(editDocumentSample).then(function (response) {
                        handleUpdateQuanLyTaiLieuMauResponse(response, type);
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                } else {
                    QuanLyTaiLieuMau.update(editDocumentSample).then(function (response) {
                        handleUpdateQuanLyTaiLieuMauResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdateQuanLyTaiLieuMauResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchDocumentSample();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editDocumentSample = $scope.initNewDocumentSample();
                    $scope.selectedFile = [];
                    $(`#progressBar`).css('width',  '0%');
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
