
(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'ConfigService']).controller("GuideVideoCtrl", ['$scope', '$compile', '$uibModal', '$http', '$state', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'QuanLyVideoHuongDan', GuideVideoCtrl]);
    function GuideVideoCtrl($scope, $compile, $uibModal, $http, $state, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, QuanLyVideoHuongDan) {

        var ctrl = this;

        ctrl.searchGuideVideo = {
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

        var titleHtml = '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="guideVideoCtrl.selectAll" ng-change="guideVideoCtrl.toggleAll(guideVideoCtrl.selectAll, guideVideoCtrl.selected)">';

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
                searchData: ctrl.searchGuideVideo,
                isLoading: true
            }
            QuanLyVideoHuongDan.search(params).then(function (response) {
                ctrl.lstGuideVideo = response.data.data.data;
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
            DTColumnBuilder.newColumn(null).withTitle('#').withClass('text-center').withOption('width', '3%').notSortable().renderWith(function (data, type, full, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }),
            DTColumnBuilder.newColumn(null).withTitle(titleHtml).withOption('width', '10%').notSortable().renderWith(function (data, type, full, meta) {
                ctrl.selected[full.id] = false;
                return '<input type="checkbox" class="form-check-input m-0 align-middle select-row" ng-model="guideVideoCtrl.selected[' + data.id + ']" ng-change="guideVideoCtrl.toggleOne(guideVideoCtrl.selected)"/>';
            }),
            DTColumnBuilder.newColumn('name').withTitle($filter('translate')('GUIDE_VIDEO.NAME')).withOption('width', '30%').renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('name', meta.row, data);
            }),
            DTColumnBuilder.newColumn('description').withTitle($filter('translate')('GUIDE_VIDEO.DESCRIPTION')).withOption('width', '40%').renderWith(function (data, type, full, meta){
                return $scope.avoidXSSRender('description', meta.row, data);
            }),
            DTColumnBuilder.newColumn(null).withTitle("").withClass('text-center').notSortable().renderWith(function (data, type, full, meta) {
                var html = '<div class="action-button-group">';
                // view detail
                html += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="guideVideoCtrl.openEditGuideVideoModal('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Chi tiết"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 30 30\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d="M27.99 6.437c-.58-.58-1.54-.58-2.12 0l-.433.432c-.58.58-.58 1.54 0 2.12l1.573 1.574c.582.58 1.54.58 2.122 0l.432-.433c.58-.58.58-1.538 0-2.12L27.99 6.437zm-.706.707l1.573 1.573c.202.202.202.505 0 .707l-.433.433c-.2.202-.505.202-.707 0l-1.573-1.573c-.202-.202-.202-.505 0-.707l.433-.433c.202-.202.505-.202.707 0zm-3.41 1.86c-.3-.024-.666.06-.938.334l-10.29 10.308c-.187.19-.385.386-.503.692-.117.306-.142.64-.142 1.162V23c0 .505.43 1 1 1h1.5c.524 0 .865-.03 1.17-.148.306-.12.503-.317.684-.498l10.29-10.31c.495-.496.45-1.256 0-1.706l-2-2c-.212-.21-.47-.31-.77-.334zm.062 1.04l2 2c.038.038.076.218 0 .294l-10.29 10.308c-.18.18-.233.233-.337.274-.105.04-.332.08-.81.08H13v-1.5c0-.48.038-.705.077-.805.038-.1.09-.153.277-.34l10.29-10.31c.09-.09.247-.045.292 0zM4.5 11h13c.277 0 .5.223.5.5s-.223.5-.5.5h-13c-.277 0-.5-.223-.5-.5s.223-.5.5-.5zm0-3h13c.277 0 .5.223.5.5s-.223.5-.5.5h-13c-.277 0-.5-.223-.5-.5s.223-.5.5-.5zm0-3h13c.277 0 .5.223.5.5s-.223.5-.5.5h-13c-.277 0-.5-.223-.5-.5s.223-.5.5-.5zm-3-5C.678 0 0 .678 0 1.5v27c0 .822.678 1.5 1.5 1.5h19c.822 0 1.5-.678 1.5-1.5v-8c0-.668-1-.648-1 0v8c0 .286-.214.5-.5.5h-19c-.286 0-.5-.214-.5-.5v-27c0-.286.214-.5.5-.5h19c.286 0 .5.214.5.5v7c0 .672 1 .648 1 0v-7c0-.822-.678-1.5-1.5-1.5z"/></svg></button>`;
                // view
                html += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="guideVideoCtrl.openViewGuideVideoModal(${meta.row});" data-toggle="tooltip" data-placement="left" title="Xem"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><circle cx=\"12\" cy=\"12\" r=\"2\" /><path d=\"M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7\" /></svg></button>`;
                // delete
                html += `&nbsp;<button type="button" class="btn btn-outline-danger btn-icon action" ng-click="guideVideoCtrl.onDeleteGuideVideo(${meta.row});" data-toggle="tooltip" data-placement="left" title="Xóa"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><line x1=\"4\" y1=\"7\" x2=\"20\" y2=\"7\" /><line x1=\"10\" y1=\"11\" x2=\"10\" y2=\"17\" /><line x1=\"14\" y1=\"11\" x2=\"14\" y2=\"17\" /><path d=\"M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12\" /><path d=\"M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3\" /></svg></button>`;
                html += '</div>';
                return html;
            }),
        ];

        ctrl.init = function () {
            QuanLyVideoHuongDan.init()
        }();

        $scope.onSearchGuideVideo = function () {
            ctrl.dtInstance.rerender();
        }


        $scope.initNewGuideVideo = function () {
            return {
                name: "",
                description: "",
                link: "",
            }
        };

        ctrl.openAddGuideVideoModal = function () {
            $scope.editGuideVideo = $scope.initNewGuideVideo();
            $scope.selectedFile = [];
            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/addUpdateGuideVideo.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openEditGuideVideoModal = function (row) {
            $scope.editGuideVideo = angular.copy(ctrl.lstGuideVideo[row]);
            $scope.editGuideVideo.name = "" + $scope.editGuideVideo.name;
            $scope.editGuideVideo.description = "" + $scope.editGuideVideo.description;
            $scope.editGuideVideo.link = "" + $scope.editGuideVideo.link;

            $uibModal.open({
                animation: true,
                templateUrl: 'admin/views/modal/addUpdateGuideVideo.html',
                windowClass: "fade show modal-blur",
                size: 'lg modal-dialog-centered',
                backdrop: 'static',
                backdropClass: 'show',
                keyboard: false,
                controller: ModalInstanceCtrl,
                scope: $scope
            });
        }

        ctrl.openViewGuideVideoModal = function (row) {
            $state.go("index.config.detail_guide_video", {'id': ctrl.lstGuideVideo[row].id});
        }

        ctrl.onDeleteGuideVideo = function (row) {
            let editGuideVideo = ctrl.lstGuideVideo[row];
            const confirm = NotificationService.confirm($filter('translate')('GUIDE_VIDEO.DELETE_CONFIRM', { 'guide_video_name': editGuideVideo.name }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
            confirm.then(function () {
                QuanLyVideoHuongDan.delete(editGuideVideo).then(function (response) {
                    if (response.data.success) {
                        $scope.onSearchGuideVideo();
                        delete ctrl.selected[editGuideVideo.id];
                        NotificationService.success($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.SUCCESS"));
                    }
                }, function (response) {
                    $scope.initBadRequest(response);
                });
            }).catch(function (err) {
            });
        }

        ctrl.onDeleteMultiGuideVideos = function () {
            let lstGuideVideo = [];
            for (var id in ctrl.selected) {
                if (ctrl.selected.hasOwnProperty(id)) {
                    if (ctrl.selected[id]) {
                        lstGuideVideo.push(id);
                    }
                }
            }
            if (lstGuideVideo.length == 0) {
                NotificationService.error($filter('translate')('GUIDE_VIDEO.ERR_NEED_CHOOSE_GUIDE_VIDEO'), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            } else {
                const confirm = NotificationService.confirm($filter('translate')('GUIDE_VIDEO.DELETE_MULTI_CONFIRM', { 'guideVideoLength': lstGuideVideo.length }), $filter('translate')("COMMON.NOTIFICATION.CONFIRM"), $filter('translate')("COMMON.NOTIFICATION.CANCEL"), $filter('translate')("COMMON.NOTIFICATION.OK"));
                confirm.then(function () {
                    QuanLyVideoHuongDan.deleteMulti({ 'lst': lstGuideVideo }).then(function (response) {
                        if (response.data.success) {
                            $scope.onSearchGuideVideo();
                            lstGuideVideo.forEach(e => {
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

    function ModalInstanceCtrl($scope, $uibModalInstance, $http, $window, $filter, QuanLyVideoHuongDan) {
        $scope.onCreateUpdateGuideVideo = function (type) {
            let editGuideVideo = $scope.editGuideVideo;
            $scope.editGuideVideo.files = angular.copy($scope.selectedFile);
            let errorMess = "";
            if (editGuideVideo.name == "") {
                errorMess += $filter('translate')('GUIDE_VIDEO.ERR_EMPTY_NAME');
            }
            if (editGuideVideo.description == "") {
                errorMess += $filter('translate')('GUIDE_VIDEO.ERR_EMPTY_DESCRIPTION');
            }
            if (editGuideVideo.link == "") {
                errorMess += $filter('translate')('GUIDE_VIDEO.ERR_EMPTY_LINK');
            }

            if (errorMess == "") {
                if (!editGuideVideo.id) {
                    QuanLyVideoHuongDan.create(editGuideVideo).then(function (response) {
                        handleUpdateQuanLyVideoHuongDanResponse(response, type);
                    }, function (response) {
                        NotificationService.error($filter('translate')(response.data.message), $filter('translate')("COMMON.NOTIFICATION.ERROR"));
                    });
                } else {
                    QuanLyVideoHuongDan.update(editGuideVideo).then(function (response) {
                        handleUpdateQuanLyVideoHuongDanResponse(response, type);
                    }, function (response) {
                        $scope.initBadRequest(response);
                    });
                }
            } else {
                NotificationService.error(errorMess, $filter('translate')("COMMON.NOTIFICATION.ERROR"));
            }
        }

        function handleUpdateQuanLyVideoHuongDanResponse(response, type) {
            if (response.data.success) {
                $scope.onSearchGuideVideo();
                if (type == 0) {
                    $uibModalInstance.close(false);
                } else {
                    $scope.editGuideVideo = $scope.initNewGuideVideo();
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
