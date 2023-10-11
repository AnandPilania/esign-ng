<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title')</title>
    <style>
        body {
            font-family: 'examplefont', sans-serif;
        }
		@font-face {
		  font-family: 'RoundedMplus1c-Regular';
		  font-style: normal;
		  font-weight: normal;
		  src: url('fonts/RoundedMplus1c-Regular.ttf') format('truetype');
		}
		@font-face {
		  font-family: 'RoundedMplus1c-Medium';
		  font-style: normal;
		  font-weight: normal;
		  src: url('fonts/RoundedMplus1c-Medium.ttf') format('truetype');
		}
		@font-face {
		  font-family: 'RoundedMplus1c-Bold';
		  font-style: normal;
		  font-weight: normal;
		  src: url('fonts/RoundedMplus1c-Bold.ttf') format('truetype');
		}
		* {
			font-family: 'RoundedMplus1c-Medium';
			box-sizing: border-box;
		}
		*, ::after, ::before {
		  box-sizing: border-box;
		}
		html {
			font-family: sans-serif;
			-webkit-text-size-adjust: 100%;
			-ms-text-size-adjust: 100%;
		}
		.clearfix::after {
		  display: block;
		  clear: both;
		  content: "";
		}
		body {
			font-family: 'RoundedMplus1c-Medium';
			margin: 0px;
			position: relative;
			color: #000;
			font-size: 13px;
		}
        .container {
            max-width: 1170px;
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
		}
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
		table {
			border-collapse: collapse;
		}
		.table {
		  width: 100%;
		  margin-bottom: 1rem;
		  color: #212529;
		}
		p {
			margin-bottom: 5px;
			margin-top: 0px;
			line-height: 1;
		}
		table tbody tr,
		table tbody td {
			line-height: 1;
			padding: 2px 15px;
			margin: 0px;
	    border: 1px solid #333;
		}
		table tbody tr.item,
		table tbody tr.item td {
			border-bottom: 0px;
			border-top: 0px;
		}
		.table>tbody>tr>td,
		.table>tbody>tr>th,
		.table>tfoot>tr>td,
		.table>tfoot>tr>th,
		.table>thead>tr>td,
		.table>thead>tr>th {
			border-top:1px solid #333;
		}
        td.align-middle {
            vertical-align: middle;
        }
		table tbody tr.total-price-tax,
		table tbody tr.total-price-tax span {
			font-family: 'RoundedMplus1c-Bold','RoundedMplus1c-Regular', 'Helvetica';
			font-size: 16px;
		}
        .pos {
			float: right;
		}
        h1,h2,h3,h4,h5,h6 {
            font-weight: inherit;
        }
    </style>
    @yield('addCss')
</head>
<body>
    @yield('content')
</body>
</html>
