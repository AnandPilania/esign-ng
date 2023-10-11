<!DOCTYPE html>
<html ng-app="app">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<title><?= Config::get('app.name') ?></title>

        <link rel="shortcut icon" type="image/png" href="<?= "fcontract-favicon.png" . '?v=' . Config::get('app.version') ?>">

		<!-- build:css(.) assets/css/vendor.css -->
		<link rel="stylesheet" href="bower_components/PACE/themes/blue/pace-theme-minimal.css" />
		<link rel="stylesheet" href="bower_components/perfect-scrollbar/css/perfect-scrollbar.min.css" />
		<!-- endbuild -->

		<!-- lazyload Start -->
		<link id="lazyload-holder">
		<!-- lazyload End -->

		<!-- build:css({.tmp,app}) assets/css/style.css -->
		<link href="vcontract_sign/assets/styles/tabler.css" rel="stylesheet">
        <link href="vcontract_sign/assets/styles/CssCustomize.css" rel="stylesheet">
        <link href="vcontract_sign/assets/styles/LoginSign.css" rel="stylesheet">
        <link href="vcontract_sign/assets/javascript/libs/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
		<link href="vcontract_sign/assets/javascript/libs/alertify/css/alertify.css" rel="stylesheet">
		<link href="vcontract_sign/assets/javascript/libs/alertify/css/default.min.css" rel="stylesheet">
        <link href="vcontract_sign/assets/javascript/libs/flatpickr/flatpickr.min.css" rel="stylesheet">

		<!-- endbuild -->
	</head>

	<body ng-controller="AppCtrl as ctrl" ng-class="{'login-bg border-top-wide border-primary d-flex flex-column':$state.includes('signin')}">

		<!-- Main view  -->
		<div ng-class="{'flex-fill d-flex flex-column justify-content-center py-4':$state.includes('signin')}">
            <ui-view>
		</div>

		<!-- build:js(.) scripts/vendor.js -->
		<script src="bower_components/PACE/pace.min.js"></script>
        <script src="vcontract_sign/assets/javascript/libs/jquery/jquery.min.js"></script>
        <script src="vcontract_sign/assets/javascript/libs/bootstrap/js/bootstrap.min.js"></script>
		<script src="bower_components/angular/angular.js"></script>
		<script src="bower_components/popper.js/dist/umd/popper.min.js"></script>
		<script src="bower_components/angular-bootstrap/ui-bootstrap-tpls.js"></script>
		<script src="bower_components/angular-ui-router/release/angular-ui-router.js"></script>
		<script src="bower_components/oclazyload/dist/ocLazyLoad.js"></script>
		<script src="bower_components/ui-jq/jq.js"></script>
		<script src="bower_components/perfect-scrollbar/js/perfect-scrollbar.jquery.js"></script>
		<script src="bower_components/angular-animate/angular-animate.js"></script>
		<script src="bower_components/angular-deep-blur/angular-deep-blur.js"></script>
		<script src="bower_components/angular-sanitize/angular-sanitize.min.js"></script>
		<script src="bower_components/angular-translate/angular-translate.min.js"></script>
		<script src="bower_components/angular-translate/angular-translate-loader-static-files.min.js"></script>
		<script src="vcontract_sign/assets/javascript/libs/bootstrap-notify/bootstrap-notify.js"></script>
		<script src="vcontract_sign/assets/javascript/libs/alertify/alertify.min.js"></script>
        <script src="vcontract_sign/assets/javascript/libs/flatpickr/flatpickr.min.js"></script>
        <script src="vcontract_sign/assets/javascript/libs/flatpickr/ng-flatpickr.min.js"></script>
		<script src="vcontract_sign/assets/lib/moment.js"></script>
		<script src="bower_components/signaturepad/signature_pad.js"></script>

		<!-- build:js({.tmp,app}) scripts/app.min.js -->
		<!-- Angular App Script -->
		<script src="<?php $path = "vcontract_sign/scripts/utils.js";  echo $path . '?v=' . Config::get('app.version'); ?>"></script>
		<script src="<?php $path = "vcontract_sign/scripts/notificationService.js";  echo $path . '?v=' . Config::get('app.version'); ?>"></script>
		<script src="<?php $path = "vcontract_sign/scripts/app.js";  echo $path . '?v=' . Config::get('app.version'); ?>"></script>
        <script src="<?php $path = "vcontract_sign/scripts/router.config.js";  echo $path . '?v=' . Config::get('app.version'); ?>"></script>
        <script src="<?php $path = "vcontract_sign/scripts/app.ctrl.js";  echo $path . '?v=' . Config::get('app.version'); ?>"></script>
        <script src="<?php $path = "vcontract_sign/scripts/services/signServices.js";  echo $path . '?v=' . Config::get('app.version'); ?>"></script>
        <script src="vcontract_sign/assets/javascript/libs/pdf/pdf-edittext.js"></script>
        <script src="vcontract_sign/assets/javascript/libs/pdfjs/pdf.min.js"></script>
        <script src="vcontract_sign/assets/javascript/libs/pdfjs/pdf.worker.js"></script>
        <script src="vcontract_sign/assets/javascript/libs/fabric/fabric.min.js"></script>
        <script src="vcontract_sign/assets/javascript/libs/printjs/print.min.js"></script>

        <script src="vcontract_sign/assets/lib/pdfLoader.js"></script>
        <script src="vcontract_sign/assets/lib/signdoc.js"></script>
        <script src="vcontract_sign/assets/lib/signature.js"></script>
		<!-- endbuild -->

		<script>
            angular.module("app").constant('CSRF_TOKEN', '<?php echo csrf_token(); ?>');
			var version_file = "<?= Config::get('app.version') ?>";
            var footerUrl = "<?= Config::get('app.footerUrl') ?>";
        </script>
	</body>
</html>
