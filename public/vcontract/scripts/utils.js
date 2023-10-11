var Utils = (function () {

    return {
        getPluginUrl: function() {
            return "http://127.0.0.1:6789/";
        },
        getDataTableLanguage: function (lang) {
            if (lang == 'en') {
                return {
                    "emptyTable": "No data to display",
                    "paginate": {
                        "first": "<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"11 7 6 12 11 17\" /><polyline points=\"17 7 12 12 17 17\" /></svg>",
                        "previous": "<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"15 6 9 12 15 18\" /></svg>",
                        "next": "<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"9 6 15 12 9 18\" /></svg>",
                        "last": "<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"7 7 12 12 7 17\" /><polyline points=\"13 7 18 12 13 17\" /></svg>"
                    },
                    "lengthMenu": "Show _MENU_ entries"
                }
            }
            return {
                "emptyTable": "Không có dữ liệu",
                "paginate": {
                    "first": "<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"11 7 6 12 11 17\" /><polyline points=\"17 7 12 12 17 17\" /></svg>",
                    "previous": "<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"15 6 9 12 15 18\" /></svg>",
                    "next": "<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"9 6 15 12 9 18\" /></svg>",
                    "last": "<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"7 7 12 12 7 17\" /><polyline points=\"13 7 18 12 13 17\" /></svg>"
                },
                "lengthMenu": "Hiển thị _MENU_ bản ghi"
            }
        },
        getDataTableDom: function () {
            return "<'row'<'col-12 table-body't>>" + "<'card-footer bg-transparent d-flex align-items-center'<'m-0 text-muted'l><'pagination m-0 ms-auto'p>>";
        },
        getDataTableStatusColumn: function (data) {
            if (data.status == 1) {
                return '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-helpdesk text-teal" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><polyline points="9 11 12 14 20 6"></polyline><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9"></path></svg>'
            } else {
                return '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-helpdesk text-secondary" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><polyline points="9 11 12 14 20 6"></polyline><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9"></path></svg>'
            }
        },
        getDataTableCustomerTypeColumn: function (data) {
            if (data.customer_type == 1) {
                return '<svg xmlns="http://www.w3.org/2000/svg" class="icon text-red icon-helpdesk" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><circle cx="9" cy="7" r="4"></circle><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path></svg>'
            } else {
                return '<svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary icon-helpdesk" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><circle cx="12" cy="7" r="4"></circle><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path></svg>'
            }
        },
        renderDataTableAction: function (meta, editFunc = false, viewFunc = false, pheDuyetFunc = false, kySoFunc = false, sendFunc = false, passChangeFunc = false, downLoadFunc = false, lichSuFunc = false, delFunc = false, recoverFunc = false, lichsuTrucFunc = false, thietKeTaiLieuMauFunc = false) {
            let action = '<div class="action-button-group">';
            if (editFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${editFunc}(${meta.row});" data-toggle="tooltip" data-placement="left" title="Chi tiết"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><circle cx=\"12\" cy=\"12\" r=\"2\" /><path d=\"M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7\" /></svg></button>`;
            }
            if (viewFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${viewFunc}(${meta.row});" data-toggle="tooltip" data-placement="left" title="Xem"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><circle cx=\"12\" cy=\"12\" r=\"2\" /><path d=\"M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7\" /></svg></button>`;
            }
            if (pheDuyetFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${pheDuyetFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Phê duyệt"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"9 11 12 14 20 6\" /><path d=\"M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9\" /></svg></button>`;
            }
            if (kySoFunc) {
                action += `&nbsp;
                <button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${kySoFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Ký số"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M14 3v4a1 1 0 0 0 1 1h4\" /><path d=\"M5 8v-3a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5\" /><circle cx=\"6\" cy=\"14\" r=\"3\" /><path d=\"M4.5 17l-1.5 5l3 -1.5l3 1.5l-1.5 -5\" /></svg></button>`;
            }
            if (sendFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${sendFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Gửi"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><path d=\"M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4\" /></svg></button>`;
            }
            if (passChangeFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${passChangeFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Đổi mật khẩu"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3\" /><circle cx=\"12\" cy=\"11\" r=\"1\" /><line x1=\"12\" y1=\"12\" x2=\"12\" y2=\"14.5\" /></svg></button>`;
            }
            if (downLoadFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${downLoadFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Tải về máy"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M14 3v4a1 1 0 0 0 1 1h4\" /><path d=\"M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z\" /><line x1=\"12\" y1=\"11\" x2=\"12\" y2=\"17\" /><polyline points=\"9 14 12 17 15 14\" /></svg></button>`;
            }
            if (lichSuFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${lichSuFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Xem lịch sử"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><rect x=\"4\" y=\"5\" width=\"16\" height=\"16\" rx=\"2\" /><line x1=\"16\" y1=\"3\" x2=\"16\" y2=\"7\" /><line x1=\"8\" y1=\"3\" x2=\"8\" y2=\"7\" /><line x1=\"4\" y1=\"11\" x2=\"20\" y2=\"11\" /><rect x=\"8\" y=\"15\" width=\"2\" height=\"2\" /></svg></button>`;
            }
            if (thietKeTaiLieuMauFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${thietKeTaiLieuMauFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Thiết kế tài liệu mẫu"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 30 30\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d="M27.99 6.437c-.58-.58-1.54-.58-2.12 0l-.433.432c-.58.58-.58 1.54 0 2.12l1.573 1.574c.582.58 1.54.58 2.122 0l.432-.433c.58-.58.58-1.538 0-2.12L27.99 6.437zm-.706.707l1.573 1.573c.202.202.202.505 0 .707l-.433.433c-.2.202-.505.202-.707 0l-1.573-1.573c-.202-.202-.202-.505 0-.707l.433-.433c.202-.202.505-.202.707 0zm-3.41 1.86c-.3-.024-.666.06-.938.334l-10.29 10.308c-.187.19-.385.386-.503.692-.117.306-.142.64-.142 1.162V23c0 .505.43 1 1 1h1.5c.524 0 .865-.03 1.17-.148.306-.12.503-.317.684-.498l10.29-10.31c.495-.496.45-1.256 0-1.706l-2-2c-.212-.21-.47-.31-.77-.334zm.062 1.04l2 2c.038.038.076.218 0 .294l-10.29 10.308c-.18.18-.233.233-.337.274-.105.04-.332.08-.81.08H13v-1.5c0-.48.038-.705.077-.805.038-.1.09-.153.277-.34l10.29-10.31c.09-.09.247-.045.292 0zM4.5 11h13c.277 0 .5.223.5.5s-.223.5-.5.5h-13c-.277 0-.5-.223-.5-.5s.223-.5.5-.5zm0-3h13c.277 0 .5.223.5.5s-.223.5-.5.5h-13c-.277 0-.5-.223-.5-.5s.223-.5.5-.5zm0-3h13c.277 0 .5.223.5.5s-.223.5-.5.5h-13c-.277 0-.5-.223-.5-.5s.223-.5.5-.5zm-3-5C.678 0 0 .678 0 1.5v27c0 .822.678 1.5 1.5 1.5h19c.822 0 1.5-.678 1.5-1.5v-8c0-.668-1-.648-1 0v8c0 .286-.214.5-.5.5h-19c-.286 0-.5-.214-.5-.5v-27c0-.286.214-.5.5-.5h19c.286 0 .5.214.5.5v7c0 .672 1 .648 1 0v-7c0-.822-.678-1.5-1.5-1.5z"/></svg></button>`;
            }
            if (delFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-danger btn-icon action" ng-click="${delFunc}(${meta.row});" data-toggle="tooltip" data-placement="left" title="Xóa"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><line x1=\"4\" y1=\"7\" x2=\"20\" y2=\"7\" /><line x1=\"10\" y1=\"11\" x2=\"10\" y2=\"17\" /><line x1=\"14\" y1=\"11\" x2=\"14\" y2=\"17\" /><path d=\"M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12\" /><path d=\"M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3\" /></svg></button>`;
            }
            if (recoverFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${recoverFunc}(${meta.row});" data-toggle="tooltip" data-placement="left" title="Khôi phục"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M15 4.55a8 8 0 0 0 -6 14.9m0 -4.45v5h-5\" /><line x1=\"18.37\" y1=\"7.16\" x2=\"18.37\" y2=\"7.17\" /><line x1=\"13\" y1=\"19.94\" x2=\"13\" y2=\"19.95\" /><line x1=\"16.84\" y1=\"18.37\" x2=\"16.84\" y2=\"18.38\" /><line x1=\"19.37\" y1=\"15.1\" x2=\"19.37\" y2=\"15.11\" /><line x1=\"19.94\" y1=\"11\" x2=\"19.94\" y2=\"11.01\" /></svg></button>`;
            }
            if (lichsuTrucFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${lichsuTrucFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Xem lịch sử xác thực trục">
                <?xml version="1.0" encoding="utf-8"?>
                <!-- Generator: Adobe Illustrator 17.0.2, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
                <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 32 32\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\" xml:space="preserve">
                <g>
                    <path fill="#828282" d="M0.034,16.668C0.388,25.179,7.403,32,16,32s15.612-6.821,15.966-15.332C31.985,16.615,32,16.56,32,16.5
                        c0-0.036-0.013-0.067-0.02-0.1C31.983,16.266,32,16.135,32,16c0-8.822-7.178-16-16-16S0,7.178,0,16c0,0.135,0.017,0.266,0.02,0.4
                        C0.013,16.433,0,16.464,0,16.5C0,16.56,0.015,16.615,0.034,16.668z M24.921,22.742c-1.319-0.552-2.735-0.979-4.215-1.271
                        c0.158-1.453,0.251-2.962,0.28-4.47h4.98C25.875,19.055,25.51,20.994,24.921,22.742z M26.965,17h3.984
                        c-0.186,2.806-1.138,5.403-2.663,7.579c-0.759-0.533-1.577-1.019-2.457-1.44C26.474,21.27,26.871,19.196,26.965,17z M12.389,22.286
                        C13.567,22.102,14.776,22,16,22c1.224,0,2.433,0.102,3.61,0.286C18.916,27.621,17.4,31,16,31S13.084,27.621,12.389,22.286z
                         M13.908,30.664c-2.751-0.882-5.078-3.471-6.482-6.984c1.246-0.525,2.586-0.935,3.99-1.217
                        C11.875,25.959,12.714,29.005,13.908,30.664z M12.274,10.709C13.491,10.897,14.739,11,16,11c1.261,0,2.509-0.103,3.726-0.291
                        C19.898,12.329,20,14.097,20,16h-8C12,14.097,12.102,12.329,12.274,10.709z M19.985,17c-0.028,1.525-0.118,2.961-0.26,4.291
                        C18.509,21.103,17.262,21,16,21c-1.262,0-2.509,0.103-3.726,0.291c-0.173-1.626-0.237-3.057-0.26-4.291H19.985z M20.585,22.463
                        c1.404,0.282,2.743,0.692,3.99,1.217c-1.404,3.513-3.731,6.102-6.482,6.984C19.286,29.005,20.125,25.959,20.585,22.463z M21,16
                        c0-1.836-0.102-3.696-0.294-5.47c1.48-0.292,2.896-0.72,4.215-1.271C25.605,11.288,26,13.574,26,16H21z M20.585,9.537
                        c-0.46-3.496-1.298-6.543-2.493-8.201c2.751,0.882,5.078,3.471,6.482,6.984C23.328,8.845,21.989,9.256,20.585,9.537z M19.611,9.714
                        C18.433,9.898,17.224,10,16,10s-2.433-0.102-3.611-0.286C13.084,4.379,14.6,1,16,1C17.4,1,18.916,4.379,19.611,9.714z
                         M11.415,9.537c-1.404-0.282-2.743-0.692-3.99-1.217c1.404-3.513,3.731-6.102,6.482-6.984C12.714,2.995,11.875,6.041,11.415,9.537z
                         M11.294,10.53C11.102,12.304,11,14.164,11,16H6c0-2.426,0.395-4.712,1.079-6.742C8.398,9.81,9.814,10.237,11.294,10.53z
                         M11.014,17c0.029,1.508,0.122,3.017,0.28,4.471c-1.48,0.292-2.896,0.72-4.215,1.271C6.49,20.994,6.125,19.055,6.034,17H11.014z
                         M6.17,23.139c-0.88,0.422-1.697,0.907-2.456,1.44C2.189,22.403,1.237,19.807,1.051,17h3.984C5.129,19.196,5.526,21.27,6.17,23.139
                        z M4.313,25.38c0.685-0.479,1.417-0.922,2.207-1.305c1.004,2.485,2.449,4.548,4.186,5.943C8.18,29.06,5.977,27.45,4.313,25.38z
                         M21.294,30.017c1.738-1.394,3.182-3.458,4.186-5.943c0.79,0.384,1.522,0.826,2.207,1.305C26.023,27.45,23.82,29.06,21.294,30.017z
                         M27,16c0-2.567-0.428-4.987-1.17-7.139c0.88-0.422,1.698-0.907,2.457-1.44C29.991,9.855,31,12.81,31,16H27z M27.688,6.62
                        c-0.685,0.479-1.417,0.921-2.207,1.305c-1.004-2.485-2.449-4.549-4.186-5.943C23.82,2.94,26.023,4.55,27.688,6.62z M10.706,1.983
                        C8.968,3.377,7.524,5.441,6.52,7.926C5.729,7.542,4.998,7.099,4.313,6.62C5.977,4.55,8.18,2.94,10.706,1.983z M3.714,7.421
                        C4.472,7.954,5.29,8.439,6.17,8.861C5.428,11.013,5,13.433,5,16H1C1,12.81,2.009,9.855,3.714,7.421z"/>
                </g>
                </svg>
                `;
            }

            action += "</div>";
            return action;
        },
        renderDataTableAction2: function (meta, editFunc = false, viewFunc = false,  sendFunc = false,  lichSuFunc = false, delFunc = false, recoverFunc = false) {
            let action = '<div class="action-button-group">';
            if (editFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${editFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Chi tiết"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 30 30\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d="M27.99 6.437c-.58-.58-1.54-.58-2.12 0l-.433.432c-.58.58-.58 1.54 0 2.12l1.573 1.574c.582.58 1.54.58 2.122 0l.432-.433c.58-.58.58-1.538 0-2.12L27.99 6.437zm-.706.707l1.573 1.573c.202.202.202.505 0 .707l-.433.433c-.2.202-.505.202-.707 0l-1.573-1.573c-.202-.202-.202-.505 0-.707l.433-.433c.202-.202.505-.202.707 0zm-3.41 1.86c-.3-.024-.666.06-.938.334l-10.29 10.308c-.187.19-.385.386-.503.692-.117.306-.142.64-.142 1.162V23c0 .505.43 1 1 1h1.5c.524 0 .865-.03 1.17-.148.306-.12.503-.317.684-.498l10.29-10.31c.495-.496.45-1.256 0-1.706l-2-2c-.212-.21-.47-.31-.77-.334zm.062 1.04l2 2c.038.038.076.218 0 .294l-10.29 10.308c-.18.18-.233.233-.337.274-.105.04-.332.08-.81.08H13v-1.5c0-.48.038-.705.077-.805.038-.1.09-.153.277-.34l10.29-10.31c.09-.09.247-.045.292 0zM4.5 11h13c.277 0 .5.223.5.5s-.223.5-.5.5h-13c-.277 0-.5-.223-.5-.5s.223-.5.5-.5zm0-3h13c.277 0 .5.223.5.5s-.223.5-.5.5h-13c-.277 0-.5-.223-.5-.5s.223-.5.5-.5zm0-3h13c.277 0 .5.223.5.5s-.223.5-.5.5h-13c-.277 0-.5-.223-.5-.5s.223-.5.5-.5zm-3-5C.678 0 0 .678 0 1.5v27c0 .822.678 1.5 1.5 1.5h19c.822 0 1.5-.678 1.5-1.5v-8c0-.668-1-.648-1 0v8c0 .286-.214.5-.5.5h-19c-.286 0-.5-.214-.5-.5v-27c0-.286.214-.5.5-.5h19c.286 0 .5.214.5.5v7c0 .672 1 .648 1 0v-7c0-.822-.678-1.5-1.5-1.5z"/></svg></button>`;
            }
            if (viewFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${viewFunc}(${meta.row});" data-toggle="tooltip" data-placement="left" title="Xem tài liệu"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><circle cx=\"12\" cy=\"12\" r=\"2\" /><path d=\"M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7\" /></svg></button>`;
            }
            if (sendFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${sendFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Gửi"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><path d=\"M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4\" /></svg></button>`;
            }
            if (lichSuFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${lichSuFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Xem lịch sử"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><rect x=\"4\" y=\"5\" width=\"16\" height=\"16\" rx=\"2\" /><line x1=\"16\" y1=\"3\" x2=\"16\" y2=\"7\" /><line x1=\"8\" y1=\"3\" x2=\"8\" y2=\"7\" /><line x1=\"4\" y1=\"11\" x2=\"20\" y2=\"11\" /><rect x=\"8\" y=\"15\" width=\"2\" height=\"2\" /></svg></button>`;
            }
            if (delFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-danger btn-icon action" ng-click="${delFunc}(${meta.row});" data-toggle="tooltip" data-placement="left" title="Xóa"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><line x1=\"4\" y1=\"7\" x2=\"20\" y2=\"7\" /><line x1=\"10\" y1=\"11\" x2=\"10\" y2=\"17\" /><line x1=\"14\" y1=\"11\" x2=\"14\" y2=\"17\" /><path d=\"M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12\" /><path d=\"M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3\" /></svg></button>`;
            }
            if (recoverFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${recoverFunc}(${meta.row});" data-toggle="tooltip" data-placement="left" title="Khôi phục"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M15 4.55a8 8 0 0 0 -6 14.9m0 -4.45v5h-5\" /><line x1=\"18.37\" y1=\"7.16\" x2=\"18.37\" y2=\"7.17\" /><line x1=\"13\" y1=\"19.94\" x2=\"13\" y2=\"19.95\" /><line x1=\"16.84\" y1=\"18.37\" x2=\"16.84\" y2=\"18.38\" /><line x1=\"19.37\" y1=\"15.1\" x2=\"19.37\" y2=\"15.11\" /><line x1=\"19.94\" y1=\"11\" x2=\"19.94\" y2=\"11.01\" /></svg></button>`;
            }

            action += "</div>";
            return action;
        },
        defaultUploadImage: function () {
            return "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgxIiBoZWlnaHQ9IjE1MiIgdmlld0JveD0iMCAwIDE4MSAxNTIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48cGF0aCBkPSJNMTYyLjQ5OCAxMTkuNzAyTDc4LjY2NiAxMDQuOTJjLS42MDMtLjEwNi0uOTktLjc3Ny0uODYyLTEuNDk4TDkyLjUxIDIwLjAxOWMuMTI4LS43MjIuNzItMS4yMiAxLjMyMi0xLjExM2w4My44MzIgMTQuNzgxYy42MDQuMTA3Ljk4OC43NzYuODYxIDEuNDk5bC0xNC43MDYgODMuNDAzYy0uMTI3LjcyLS43MTggMS4yMi0xLjMyMSAxLjExM3oiIHN0cm9rZT0iI0IzQjNGQyIgc3Ryb2tlLXdpZHRoPSIxLjkiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIgc3Ryb2tlLWRhc2hhcnJheT0iMTEuMzg0ODAwMTk1NjkzOTcsMTEuMzg0ODAwMTk1NjkzOTciLz48cGF0aCBkPSJNMTQzLjg1NSAxMjkuMjJMMzEuMzE5IDE0OS4wNjFjLS44MDguMTQzLTEuNjA0LS41MjYtMS43NzUtMS40OTRMOS44MDMgMzUuNjA4Yy0uMTcxLS45Ny4zNDgtMS44NjkgMS4xNTYtMi4wMTFsMTEyLjUzNi0xOS44NDNjLjgxLS4xNDMgMS42MDIuNTI1IDEuNzczIDEuNDk1bDE5Ljc0MiAxMTEuOTZjLjE3Ljk2Ny0uMzQ1IDEuODY4LTEuMTU1IDIuMDF6IiBzdHJva2U9IiNCM0IzRkMiIHN0cm9rZS13aWR0aD0iMS45IiBmaWxsPSIjRkZGIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHN0cm9rZS1kYXNoYXJyYXk9IjExLjM4NDgwMDE5NTY5Mzk3LDExLjM4NDgwMDE5NTY5Mzk3Ii8+PGcgZmlsbD0iIzgwODBGMSI+PHBhdGggZD0iTTg3Ljc2MiA2Ny44OGMtMS44MTUtLjc1Ny00LjUyLS43MjMtNi41MjEuNjczLTIuMzU2IDEuNjQzLTMuMTE5IDQuODgtMS45NDYgOC4wMDIgMS4yNiAzLjM2IDQuNzA1IDQuOTE4IDcuNzIgMy43ODIgMy4xNC0xLjE4MiA0LjY2OS00Ljc3NyAzLjg0OC04LjMyMy0uNDItMS44MTMtMS40NTYtMy40NS0zLjEtNC4xMzQiLz48cGF0aCBkPSJNMTA2LjM2NCA5Ni45M2E5Ny40MDIgOTcuNDAyIDAgMDAtMTIuMjMyLTEyLjFjLS42Ny0uNTU1LTEuNjU2LS4yOC0yLjEyNi4zNzZsLTYuMjkgOC43NzNjLS4zNC40NzQtMi4yNjMgNC0yLjg1OSAzLjk4Ny0uNjgyLS4wMTQtMy42NC0zLjc2NC00LjEyLTQuMjhMNjQuMDU2IDc3Ljg4Yy0uODYtLjkyNi0yLjA4Mi0uNjQtMi41OTguNDU4LTMuOTQxIDguNDEtNS4zMDQgMTcuODk4LTcuMDk0IDI3LjM5N2wtNy4xMTctNDAuMzZzMzYuMjctNi41MzUgNTIuNTA0LTkuNDVjMi4zNjIgMTMuOTk2IDUuMTczIDI2Ljg4OSA2LjYxMyA0MS4wMDRtNC45NjIgNC4wOTZjLjAxLS4xMDctNS4xNC0zMi4yNDMtNy44NzMtNDguMjEtLjEzNi0uNzg4LS42NzEtMS4xOS0xLjI1NS0xLjI3YTEuNCAxLjQgMCAwMC0uOTY0LS4xNWwtNTQuMDgyIDkuNzFjLS4wNjguMDExLS4xMjYuMDM1LS4xODguMDU1bC0zLjA2LjU0Yy0uOTUyLjE2Ny0xLjMgMS4wMTgtMS4xNzggMS43OThhMS43MiAxLjcyIDAgMDAuMDIzLjQxM2w4LjcxNSA0OS40MjZjLjExOS42NzUgMS4yNi4zOTQgMS45MTcuMjggMTIuMzYyLTIuMTQ5IDM3LjQ5My02LjE5NyA1Ny40NjQtMTAuMTAyLjk3My0uMzQ4LjQ4NC0yLjQ4Ny40ODEtMi40OSIvPjwvZz48ZyBmaWxsPSIjQzdFQkZGIj48cGF0aCBkPSJNMTQ0LjkzOCAxMy4wNzVjNC4zNjgtMS43MzUgNC4xMTktMTEuOTcgMy42MzMtMTMuMDc1LTEuNzczIDEuNDE4LTMuNjMzIDEzLjA3NS0zLjYzMyAxMy4wNzUiLz48cGF0aCBkPSJNMTUzLjEwMiA4LjE1OGMtMS4yNTQtMy4xNC0xMS0zLjU1NC0xMS42MTgtMy4xMjQtLjYxNy40MyA4Ljg1NyAzLjgzMiAxMS42MTggMy4xMjQiLz48L2c+PGcgZmlsbD0iI0M3RUJGRiI+PHBhdGggZD0iTTAgMTIxLjY5YzMuNTczIDMuMDUzIDEyLjUwOC0xLjk0NiAxMy4yNTktMi44OTEtMi4wODItLjkwNS0xMy4yNTkgMi44OS0xMy4yNTkgMi44OSIvPjxwYXRoIGQ9Ik04LjE1OSAxMjYuNjE0YzIuMTkyLTIuNTc0LTEuOTkyLTExLjM4OC0yLjY2LTExLjczMi0uNjY4LS4zNDUuNzQ1IDkuNjIxIDIuNjYgMTEuNzMyIi8+PC9nPjxnIGZpbGw9IiNDN0VCRkYiPjxwYXRoIGQ9Ik0xNTguOTE2IDE0NS41MDNjNC43LS4wNDIgOC4xNTktOS42OCA4LjEwNC0xMC44ODQtMi4xNjQuNjg0LTguMTA0IDEwLjg4NC04LjEwNCAxMC44ODQiLz48cGF0aCBkPSJNMTY4LjMwMyAxNDMuODYyYy0uMDM2LTMuMzgxLTguOTc3LTcuMjgzLTkuNzA4LTcuMTA0LS43My4xNzggNi44NzggNi43NjkgOS43MDggNy4xMDQiLz48L2c+PC9nPjwvc3ZnPg==";
        },
        resizeCanvas: function (canvas) {
            let ratio = window.devicePixelRatio || 1;
            canvas.width = 490 * ratio;
            canvas.height = 256 * ratio;
            let context = canvas.getContext("2d");
            context.scale(ratio, ratio);
            context.clearRect(0, 0, canvas.width, canvas.height);
        },
        removeVietnameseTones: function (str) {
            if(str != null && str != undefined){
                str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
                str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
                str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
                str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
                str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
                str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
                str = str.replace(/đ/g, "d");
                str = str.replace(/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, "A");
                str = str.replace(/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/g, "E");
                str = str.replace(/Ì|Í|Ị|Ỉ|Ĩ/g, "I");
                str = str.replace(/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g, "O");
                str = str.replace(/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/g, "U");
                str = str.replace(/Ỳ|Ý|Ỵ|Ỷ|Ỹ/g, "Y");
                str = str.replace(/Đ/g, "D");
                // Some system encode vietnamese combining accent as individual utf-8 characters
                // Một vài bộ encode coi các dấu mũ, dấu chữ như một kí tự riêng biệt nên thêm hai dòng này
                str = str.replace(/\u0300|\u0301|\u0303|\u0309|\u0323/g, ""); // ̀ ́ ̃ ̉ ̣  huyền, sắc, ngã, hỏi, nặng
                str = str.replace(/\u02C6|\u0306|\u031B/g, ""); // ˆ ̆ ̛  Â, Ê, Ă, Ơ, Ư
                // Remove extra spaces
                // Bỏ các khoảng trắng liền nhau
                str = str.replace(/ + /g, " ");
                str = str.trim();
                // Remove punctuations
                // Bỏ dấu câu, kí tự đặc biệt
                //// str = str.replace(/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'|\"|\&|\#|\[|\]|~|\$|_|`|-|{|}|\||\\/g, " ");
                //// str = str.replace(/ /g, "_");
            }
            return str;
        },
        downloadFormBinary: function (file, fileName) {
            let dl = document.createElement("a");
            const contentType = 'application/octet-stream';
            let url = this.blobUrlFromBinary(file, contentType);
            dl.href = url;
            dl.download = fileName;
            dl.className = "hidden";
            document.body.appendChild(dl);
            dl.click();
        },
        blobUrlFromBinary: function (bytes, contentType) {
            var blob = new Blob([bytes], {
                type: contentType
            });
            return URL.createObjectURL(blob);
        },
        isNumeric: function (n) {
            return !isNaN(parseFloat(n)) && isFinite(n);
        },
        validateEmail: function (email) {
            var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        },
        isValidPhoneNumber: function (phone) {
            if(phone === null) {
                return true;
            }
            return /^\+?\d+$/.test(phone) && phone.length >= 8 && phone.length <= 20;
        },
        isPositiveNumber(x) {
            return parseInt(x, 10) == x && x > 0;
        },
        parseDate: function (d) {
            let arr = d.split("/");
            return arr[2] + "-" + arr[1] + "-" + arr[0] + " 00:00:00";
        },
        parseDateEnd: function (d) {
            let arr = d.split("/");
            return arr[2] + "-" + arr[1] + "-" + arr[0] + " 23:59:59";
        },
        validateDate: function (d) {
            var date_regex = /^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[012])[\/]\d{4}$/;
            if (!date_regex.test(d.trim())) return false;
            var parts = d.split('/');
            var day = parseInt(parts[0], 10);
            var month = parseInt(parts[1], 10);
            var year = parseInt(parts[2], 10);
            if (year <= 0) return false;
            var monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            if (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)) {
                monthLength[1] = 29;
            }

            return day > 0 && day <= monthLength[month - 1];
        },
        formatFullDate: function (date) {
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var seconds = date.getSeconds();
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            var strTime = hours + ':' + minutes + ':' + seconds;
            return date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate() + " " + strTime;
        },
        formatDate: function (date) {
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var seconds = date.getSeconds();
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            var strTime = hours + ':' + minutes + ':' + seconds;
            return strTime + " " + date.getDate() + "/" + (date.getMonth() + 1) + "/" + date.getFullYear();
        },
        formatDateNoTime: function (date) {
            let month = date.getMonth() + 1;
            let day = date.getDate();
            return (day < 10 ? "0" + day : day) + "/" + (month < 10 ? "0" + month : month) + "/" + date.getFullYear();
        },
        convertDateFromTimestamp: function (date) {
            if (!date) {
                return '';
            }
            date = new Date(date);
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var seconds = date.getSeconds();
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            var strTime = hours + ':' + minutes + ':' + seconds;
            return date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate() + " " + strTime;
        },
        validatePassword: function (data) {
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/.test(data);
        },
        generatePassword: function (len) {
            var length = (len) ? (len) : (8);
            var string = "abcdefghijknopqrstuvwxyzACDEFGHJKLMNPQRSTUVWXYZ"; //to upper
            var numeric = '123456789';
            var punctuation = '!@#$%^&*()_+~`|}{[]\:;?><,./-=';
            var password = "";
            var character = "";
            var crunch = true;
            while (password.length < length) {
                entity1 = Math.ceil(string.length * Math.random() * Math.random());
                entity2 = Math.ceil(numeric.length * Math.random() * Math.random());
                entity3 = Math.ceil(punctuation.length * Math.random() * Math.random());
                hold = string.charAt(entity1);
                hold = (password.length % 2 == 0) ? (hold.toUpperCase()) : (hold);
                character += hold;
                character += numeric.charAt(entity2);
                character += punctuation.charAt(entity3);
                password = character;
            }
            password = password.split('').sort(function () {
                return 0.5 - Math.random()
            }).join('');
            return password.substr(0, len);
        },
        bytesToSize: function (bytes) {
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes == 0) return '0 Byte';
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        },
        isAfterToday: function (date) {
            const today = new Date();
            today.setHours(23, 59, 59, 998);
            return date > today;
        },
        uuidv4: function () {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        },
        getDataTableDocumentStateColumn: function (data) {
            switch (data.document_state) {
                case 1:
                    return "<span class='badge bg-secondary me-1'></span> Tạo mới - bước " + data.document_draft_state;
                case 2:
                    return "<span class='badge bg-orange me-1'></span> Chờ duyệt";
                case 3:
                    return "<span class='badge bg-blue me-1'></span> Chờ ký";
                case 4:
                    return "<span class='badge bg-red me-1'></span> Bị từ chối";
                case 5:
                    return "<span class='badge bg-red me-1'></span> Bị quá hạn";
                case 6:
                    return "<span class='badge bg-red me-1'></span> Bị hủy bỏ";
                case 7:
                    return "<span class='badge bg-orange me-1'></span> Chưa xác thực";
                case 8:
                    return "<span class='badge bg-green me-1'></span> Hoàn thành";
                case 9:
                    return "<span class='badge bg-orange me-1'></span> Đang xác thực";
                case 10:
                    return "<span class='badge bg-orange me-1'></span> Xác thực không thành công";
                default:
                    return "";
            }
        },
        dateDiffInDays: function (d1, d2) {
            var t2 = d2.getTime();
            var t1 = d1.getTime();
            return Math.floor((t2 - t1) / (24 * 3600 * 1000));
        },
        dateDiffInHours: function (d1, d2) {
            var t2 = d2.getTime();
            var t1 = d1.getTime();
            return Math.floor((t2 - t1) / (3600 * 1000));
        },
        validateCode: function(string){
            if(string === null) {
                return true;
            }
            var re = /^[a-zA-Z0-9-]+$/;
            return re.test(string);
        },
        validateVietnameseCharacterWithoutNumber: function(string){
            if(string === null) {
                return true;
            }
            var re = /^[a-zA-Z\s]+$/;
            return re.test(this.removeVietnameseTones(string));
        },
        validateVietnameseCharacterWithNumber: function(string){
            if(string === null) {
                return true;
            }
            var re = /^[a-zA-Z0-9\s]+$/;
            return re.test(this.removeVietnameseTones(string));
        },
        validateName: function(string){
            if(string === null) {
                return true;
            }
            var re = /^[a-zA-Z0-9,|_()<>&%$#\/\\\-\[\]\s]+$/;
            return re.test(this.removeVietnameseTones(string));
        },
        validateVietnameseAddress: function(string){
            if(string === null) {
                return true;
            }
            var re = /^[a-zA-Z0-9.,-_/\s]+$/;
            return re.test(this.removeVietnameseTones(string));
        },
        validateCCCD: function(string){
            if(string === null) {
                return true;
            }
            var re_passport = /^[A-Z]{1}\d{7}$/;
            var cmnd_cccd = /^\d{9}$|^\d{12}$/;
            return re_passport.test(string) || cmnd_cccd.test(string);
        },
        getDocumentStyle: function(style){
            if(style == 1){
                return 'UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.BUY_IN';
            } else if(style == 2) {
                return 'UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.SELL_OUT';
            } else {
                return 'UTILITES.DOCUMENT_TYPE.DOCUMENT_STYLE.ELSE';
            }
        },
        getSignMethod: function(sign_method){
            let result = {
                0: false,
                1: false,
                2: false,
            };
            sign_method.forEach(method =>{
                if (method == "0") {
                    result[0] = true;
                } else if (method == "1") {
                    result[1] = true;
                }else if (method == "2") {
                    result[2] = true;
                }
            });
            let sign_method_icon = '<div style="font-size:8px;">';
            sign_method_icon += `   <badge class="badge bg-info" ng-if="${result[0] == true}">USB token</badge>
                                    <badge class="badge bg-info" ng-if="${result[1] == true}">Mã OTP</badge>
                                    <badge class="badge bg-info" ng-if="${result[2] == true}">EKYC</badge>
                                    `;
            sign_method_icon += '</div>';
            return sign_method_icon;
        },
        removeItemInArray: function(arr){
            var what, a = arguments, L = a.length, ax;
            while (L > 1 && arr.length) {
                what = a[--L];
                while ((ax= arr.indexOf(what)) !== -1) {
                    arr.splice(ax, 1);
                }
            }
            return arr;
        },
        getTotal: function(arr){
            var total = 0
			for(let i = 0; i < arr.length; i++){
				var sum = arr[i].total;
				total += sum;
            }
            return total;
        }
    }

}())
