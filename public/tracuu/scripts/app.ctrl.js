
(function () {
	'use strict';
	angular.module("app").controller("AppCtrl", ['$scope', '$http', '$window', '$timeout', '$filter', AppCtrl]);
	function AppCtrl($scope, $http, $window, $timeout, $filter) {

        var ctrl = this;
        $scope.lstMethodSign = [
			{ 'id': '0', 'description': 'INTERNAL.SIGN_MANAGE.DIGITAL_SIGN' },
			{ 'id': '1', 'description': 'INTERNAL.SIGN_MANAGE.OTP_SIGN' },
			{ 'id': '2', 'description': 'INTERNAL.SIGN_MANAGE.EKYC_SIGN' },
		]
		$scope.lstBranchStatus = [
			{ 'id': '-1', 'description': 'UTILITES.BRANCH.STATUS.DEFAULT' },
			{ 'id': '1', 'description': 'UTILITES.BRANCH.STATUS.ACTIVE' },
			{ 'id': '0', 'description': 'UTILITES.BRANCH.STATUS.INACTIVE' },
		]
		$scope.lstActiveStatus = [
			{ 'id': '-1', 'description': 'COMMON.STATUS' },
			{ 'id': '1', 'description': 'COMMON.USING' },
			{ 'id': '0', 'description': 'COMMON.NOT_USING' },
		]

		$scope.lstGender = [
			{ 'id': '0', 'description': 'Nam' },
			{ 'id': '1', 'description': 'Nữ' },
			{ 'id': '2', 'description': 'Khác' },
		]

        $scope.themeConfig = {
            "appName": appName,
            "logoLogin": "vcontract/assets/images/" + 'fcontract' + "-logo.png" + '?v=' + version_file,
            "logoDashboard": "vcontract/assets/images/" + 'fcontract' + "-dashboard.png" + '?v=' + version_file,
            "navbarStyle": "navbar-" + 'primary',
            "isLoginBg": themeConfig == "vt" || themeConfig == "vtt" || themeConfig == "vtc",
            "footerUrl": footerUrl,
            "isBgPrimary": themeConfig == "fcontract",
            "stepColor": "steps-" + 'yellow',
            "color": themeConfig == "vtt" ? "danger" : "primary"
        }

		$scope.dateOpts = {
			dateFormat: 'd/m/Y',
			placeholder: 'Ngày/Tháng/Năm',
			locale: {
				firstDayOfWeek: 1,
				weekdays: {
					shorthand: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
					longhand: ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'],
				},
				months: {
					shorthand: ['Tháng 01', 'Tháng 02', 'Tháng 03', 'Tháng 04', 'Tháng 05', 'Tháng 06', 'Tháng 07', 'Tháng 08', 'Tháng 09', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
					longhand: ['Tháng 01', 'Tháng 02', 'Tháng 03', 'Tháng 04', 'Tháng 05', 'Tháng 06', 'Tháng 07', 'Tháng 08', 'Tháng 09', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
				}
			}
		}

		$scope.datePostSetup = function (fpItem) {
			// console.log('flatpickr', fpItem);
		}

		$scope.lstDocumentFormat = [
			{ 'id': '{number}/{YY}-{code}', 'description': 'Loại 1: {Dải số}/{YY}-{Mã loại}' },
            { 'id': '{number}/{YYYY}-{code}', 'description': 'Loại 2: {Dải số}/{YYYY}-{Mã loại}' },
            { 'id': '{number}/{MM}/{YY}-{code}', 'description': 'Loại 3: {Dải số}/{MM}/{YY}-{Mã loại}' },
			{ 'id': '{number}/{MM}/{YYYY}-{code}', 'description': 'Loại 4: {Dải số}/{MM}/{YYYY}-{Mã loại}'},
		]

		$scope.lstCustomerType = [
			{ 'id': '-1', 'description': 'UTILITES.CUSTOMER.CLASSIFY' },
			{ 'id': '0', 'description': 'UTILITES.CUSTOMER.PERSONAL' },
			{ 'id': '1', 'description': 'UTILITES.CUSTOMER.ORGANIZATION' },
		]

		$scope.lstDocumentState = [
			{ 'id': '-1', 'description': 'COMMON.STATUS' },
			{ 'id': '1', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_SAVE_DRAFT' },
			{ 'id': '2', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_WAIT_APPROVE' },
			{ 'id': '3', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_WAIT_SIGN' },
			{ 'id': '4', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_DENIED' },
			{ 'id': '5', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_EXPIRED' },
			{ 'id': '6', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_CANCELED' },
			{ 'id': '7', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_NOT_VERIFY' },
			{ 'id': '8', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_COMPLETED' },
            { 'id': '9', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_VERIFYING' },
            { 'id': '10', 'description': 'INTERNAL.DOCUMENT_LIST.STATE_VERIFYING_FAIL' },
		]

		$scope.lstAssigneeRole = [
			{ 'id': '1', 'description': 'DOCUMENT.APPROVE_ROLE' },
			{ 'id': '2', 'description': 'DOCUMENT.SIGN_ROLE' },
			{ 'id': '3', 'description': 'DOCUMENT.STORAGE_ROLE' },
		]

		$scope.lstNotiType = [
			{ 'id': '1', 'description': 'DOCUMENT.THROUGH_EMAIL' },
			{ 'id': '2', 'description': 'DOCUMENT.THROUGH_SMS' },
			{ 'id': '3', 'description': 'DOCUMENT.THROUGH_BOTH' },
		]

		$scope.lstSignType = [
			{ 'id': '-1', 'description': '-Chọn-' },
			{ 'id': '0', 'description': 'Ký số USB Token' },
			{ 'id': '1', 'description': 'Ký số Remote Signing' },
		]

		$scope.lstRole = [
			{ 'id': '-1', 'description': 'CONFIG.ACCOUNT.CONSIGNEE_ROLE' },
			{ 'id': '0', 'description': 'CONFIG.ACCOUNT.CONSIGNEE_ROLE_0' },
			{ 'id': '1', 'description': 'CONFIG.ACCOUNT.CONSIGNEE_ROLE_1' },
			{ 'id': '2', 'description': 'CONFIG.ACCOUNT.CONSIGNEE_ROLE_2' },
		]

		$scope.lstLanguage = [
			{ 'id': 'vi', 'description': 'Tiếng Việt' },
			{ 'id': 'en', 'description': 'Tiếng Anh' },
		]

		$scope.lstConsigneeType = [
			{ 'id': '-1', 'description': 'INTERNAL.SIGN_MANAGE.CONSIGNEE_TYPE' },
			{ 'id': '0', 'description': 'INTERNAL.SIGN_MANAGE.CONSIGNEE_TYPE_MY_ORGANIZATION' },
			{ 'id': '1', 'description': 'INTERNAL.SIGN_MANAGE.CONSIGNEE_TYPE_PATNERS' },
		]

		$scope.lstConsigneeCreator = [
			{ 'id': '-1', 'description': 'INTERNAL.SIGN_MANAGE.CONSIGNEE_LIST' },
			{ 'id': '0', 'description': 'INTERNAL.SIGN_MANAGE.CONSIGNEE_LIST_WAIT_ME' },
			{ 'id': '1', 'description': 'INTERNAL.SIGN_MANAGE.CONSIGNEE_LIST_WAIT_SOMEONE' },
		]

		$scope.lstSendState = [
			{ 'id': '-1', 'description': 'INTERNAL.COMMON.SEND_STATE' },
			{ 'id': '0', 'description': 'INTERNAL.COMMON.SEND_WAITING' },
			{ 'id': '2', 'description': 'INTERNAL.COMMON.SEND_FAILED' },
			{ 'id': '1', 'description': 'INTERNAL.COMMON.SEND_SUCCEED' },
		]

		$scope.lstTemplateType = [
			{ 'id': '-1', 'description': 'CONFIG.TEMPLATE.TEMPLATE_TYPE' },
			{ 'id': '0', 'description': 'CONFIG.TEMPLATE.TEMPLATE_SMS' },
			{ 'id': '1', 'description': 'CONFIG.TEMPLATE.TEMPLATE_EMAIL' },
		]

		$scope.lstParamTemplate = [
			{ 'id': '0', 'description': 'CONFIG.TEMPLATE.PARAM_RECEIVER', 'name': '[[${ten_nguoi_nhan}]]' },
			{ 'id': '1', 'description': 'CONFIG.TEMPLATE.PARAM_DOCUMENT_NAME', 'name': '[[${ten_tai_lieu]]' },
			{ 'id': '2', 'description': 'CONFIG.TEMPLATE.PARAM_DOCUMENT_CODE', 'name': '[[${ma_tra_cuu]]' },
			{ 'id': '3', 'description': 'CONFIG.TEMPLATE.PARAM_PAGE_CODE', 'name': '[[${trang_tra_cuu]]' },
			{ 'id': '4', 'description': 'CONFIG.TEMPLATE.PARAM_COMPLETED_TIME', 'name': '[[${thoi_gian_hoan_thanh]]' },
			{ 'id': '5', 'description': 'CONFIG.TEMPLATE.PARAM_DENIED_USER', 'name': '[[${nguoi_tu_choi]]' },
			{ 'id': '6', 'description': 'CONFIG.TEMPLATE.PARAM_DENIED_REASON', 'name': '[[${ly_do_tu_choi]]' },
			{ 'id': '7', 'description': 'CONFIG.TEMPLATE.PARAM_APPROVE_DATE', 'name': '[[${ngay_duyet]]' },
			{ 'id': '8', 'description': 'CONFIG.TEMPLATE.PARAM_APPROVE_USER', 'name': '[[${nguoi_duyet]]' },
			{ 'id': '9', 'description': 'CONFIG.TEMPLATE.PARAM_OTP_CODE', 'name': '[[${ma_otp]]' },
			{ 'id': '10', 'description': 'CONFIG.TEMPLATE.PARAM_OLD_EXPIRED', 'name': '[[${thoi_han_cu]]' },
			{ 'id': '11', 'description': 'CONFIG.TEMPLATE.PARAM_NEW_EXPIRED', 'name': '[[${thoi_han_moi]]' },
			{ 'id': '12', 'description': 'CONFIG.TEMPLATE.PARAM_DOCUMENT_EXPIRED', 'name': '[[${han_tai_lieu]]' },
			{ 'id': '13', 'description': 'CONFIG.TEMPLATE.PARAM_APPROVE_DATE', 'name': '[[${ngay_ky]]' },
			{ 'id': '14', 'description': 'CONFIG.TEMPLATE.PARAM_APPROVE_USER', 'name': '[[${nguoi_ky]]' },

		]

		$scope.lstEmailProtocol = [
			{ 'id': 'smtp', 'description': 'CONFIG.SEND_DOCUMENT.EMAIL_PROTOCOL_SMTP' },
			{ 'id': 'pop', 'description': 'CONFIG.SEND_DOCUMENT.EMAIL_PROTOCOL_POP' },
			{ 'id': 'pop3', 'description': 'CONFIG.SEND_DOCUMENT.EMAIL_PROTOCOL_POP3' },
			{ 'id': 'imap', 'description': 'CONFIG.SEND_DOCUMENT.EMAIL_PROTOCOL_IMAP' },
		]

		$scope.lstDocumentStyle = [
			{'id': '1', 'description':'UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.BUY_IN'},
			{'id': '2', 'description':'UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.SELL_OUT'},
			{'id': '0', 'description':'UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.ELSE'},
		]

		$scope.lstActionGroup = [
			{'id': '2', 'description':'ACTION_HISTORY.CONFIG_ACTION'},
			{'id': '3', 'description':'ACTION_HISTORY.UTILITIES_ACTION'},
			{'id': '4', 'description':'ACTION_HISTORY.USER_ACTION'},
		]

		$scope.lstScale = [
            { 'value': '1.5', 'description': '150%' },
            { 'value': '1', 'description': '100%' },
            { 'value': '0.9', 'description': '90%' },
            { 'value': '0.8', 'description': '80%' },
            { 'value': '0.75', 'description': '75%' },
            { 'value': '0.67', 'description': '67%' },
            { 'value': '0.5', 'description': '50%' },
            { 'value': '0.33', 'description': '33%' },
        ]

	}
})();
