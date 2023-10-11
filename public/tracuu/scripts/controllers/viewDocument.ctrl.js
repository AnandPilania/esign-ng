(function () {
    'use strict';
    angular.module("app", ['datatables', 'datatables.select', 'SearchSrvc', 'webcam']).controller("ViewDocumentCtrl", ['$scope', '$rootScope', '$compile', '$uibModal', '$http', '$state', '$stateParams', '$window', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$filter', 'XuLyTaiLieu', 'WebcamService', ViewDocumentCtrl]);

    function ViewDocumentCtrl($scope, $rootScope, $compile, $uibModal, $http, $state, $stateParams, $window, $timeout, DTOptionsBuilder, DTColumnBuilder, $filter, XuLyTaiLieu, WebcamService) {

        var ctrl = this;

        $scope.initDocument = function (docId) {
            XuLyTaiLieu.initViewDoc({ docId: docId, isLoading: true }).then(function (response) {
                ctrl.viewDoc = response.data.data.document;
                ctrl.companySignature = response.data.data.company;
                ctrl.rejectReason = response.data.data.reject_reason;
                ctrl.viewDoc.sent_date = Utils.formatDateNoTime(new Date(ctrl.viewDoc.sent_date));
                ctrl.viewDoc.expired_date = Utils.formatDateNoTime(new Date(ctrl.viewDoc.expired_date));
                ctrl.viewDoc.created_at = Utils.formatDate(new Date(ctrl.viewDoc.created_at));
                let state = $scope.lstDocumentState.find(x => x.id == ctrl.viewDoc.document_state);
                if (!$scope.scale) {
                    $scope.scale = "" + 1.5
                }
                ctrl.viewDoc.state_name = state.description;
                ctrl.viewDoc.send_doc_date = ctrl.viewDoc.send_doc_date == null ? "N/A" : Utils.formatDate(new Date(ctrl.viewDoc.send_doc_date));
                ctrl.viewDoc.finished_date = ctrl.viewDoc.finished_date == null ? $filter('translate')("DOCUMENT.NOT_COMPLETE") : Utils.formatDate(new Date(ctrl.viewDoc.finished_date));
                for (let i = 0; i < ctrl.viewDoc.partners.length; i++) {
                    let partner = ctrl.viewDoc.partners[i];
                    if (partner.organisation_type == 3) {
                        partner.displayName = `[${i + 1}] ` + $filter('translate')("DOCUMENT.PERSONAL");
                    } else {
                        partner.displayName = `[${i + 1}] ${partner.company_name} - ${partner.tax}`;
                    }
                    partner.assignees.forEach(assignee => {
                        assignee.assignType = ($scope.lstAssigneeRole.find(x => x.id == assignee.assign_type)).description;
                        assignee.sent_email = "";
                        assignee.assign_date = assignee.submit_time == null ? "" : Utils.formatDate(new Date(assignee.submit_time));

                    })
                }
                if (ctrl.viewDoc.cur) {
                    ctrl.viewDoc.hasApprovalRole = ctrl.viewDoc.cur.email == $scope.loginUser.email && ctrl.viewDoc.cur.assign_type == 1;
                    ctrl.viewDoc.hasSignRole = ctrl.viewDoc.cur.email == $scope.loginUser.email && ctrl.viewDoc.cur.assign_type == 2;
                    ctrl.viewDoc.hasDenyRole = ctrl.viewDoc.cur.email == $scope.loginUser.email;
                    if (ctrl.viewDoc.cur.assign_type == 2) {
                        ctrl.viewDoc.cur.sign_method = ctrl.viewDoc.cur.sign_method ?? "";
                        ctrl.viewDoc.lstSigningMethod = ctrl.viewDoc.cur.sign_method.split(',');
                        ctrl.viewDoc.lstSigningMethod.forEach(method => {
                            if (method == 0) {
                                ctrl.viewDoc.canSignToken = true;
                            } else if (method == 1) {
                                ctrl.viewDoc.canSignOtp = true;
                            } else if (method == 2) {
                                ctrl.viewDoc.canSignKyc = true;
                            }
                        });

                    }
                    ctrl.signatureUrl = 'vcontract/assets/images/signature-icon.svg';
                    if (ctrl.viewDoc.cur.signature) {
                        ctrl.signatureUrl = ctrl.viewDoc.cur.signature.image_signature;
                    }
                }
                ctrl.lstPosition = [];
                ctrl.viewDoc.hasEditRole = ctrl.viewDoc.document_state == 4 && !ctrl.viewDoc.document_sample_id && ctrl.viewDoc.creator_email == $scope.loginUser.email;
                $scope.getViewSignDocument(docId, $scope.scale);

            }, function (response) {
                NotificationService.error($filter('translate')(response.data.message));
                $scope.goBack();
            })
        }


        ctrl.showRejectReason = function () {
            let reason = ctrl.rejectReason;
            var re = /\n/gi
            var rejectReason = reason.replace(re, "<br>")
            return NotificationService.alert(rejectReason, "Lý do từ chối");
        }

        ctrl.init = function () {
            let id = $stateParams.docId;
            $scope.initDocument(id);
        }();


        ctrl.downloadDocument = function () {
            let fileName = Utils.removeVietnameseTones(ctrl.viewDoc.name) + ".pdf";
            Utils.downloadFormBinary(ctrl.docFile, fileName);
        }

        ctrl.printDocument = function () {
            let blobUrl = Utils.blobUrlFromBinary(ctrl.docFile);
            printJS(blobUrl);
        }

        $scope.onChangeScale = function () {
            ctrl.lstPosition = [];
            let id = $stateParams.docId;
            $scope.getViewSignDocument(id, $scope.scale);
        }

        $scope.getViewSignDocument = function (docId, scale) {
            if (docId) {
                var formData = new FormData();
                formData.append("id", docId);
                XuLyTaiLieu.getSignDocument(formData).then(function (response) {
                    let oldCanv = document.getElementsByClassName('viewer-canvas-container');
                    while (oldCanv.length > 0) {
                        oldCanv[0].parentNode.removeChild(oldCanv[0]);
                    }
                    ctrl.hasChangeSignature = false;
                    ctrl.docFile = response;
                    // pdfLoad(response, (c, fc) => {});
                    signature.init(response, scale, true);
                    $scope.$digest();
                })
            } else {
            }
        }
    }
})();
