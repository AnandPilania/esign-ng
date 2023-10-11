var Utils = (function () {

    return {
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
        renderDataTableAction: function (meta, editFunc = false, viewFunc = false, pheDuyetFunc = false, kySoFunc = false, sendFunc = false, passChangeFunc = false, downLoadFunc = false, lichSuFunc = false, delFunc = false, recoverFunc = false, lichsuTrucFunc = false) {
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
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${kySoFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Ký số"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M14 3v4a1 1 0 0 0 1 1h4\" /><path d=\"M5 8v-3a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5\" /><circle cx=\"6\" cy=\"14\" r=\"3\" /><path d=\"M4.5 17l-1.5 5l3 -1.5l3 1.5l-1.5 -5\" /></svg></button>`;
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
            if (delFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-danger btn-icon action" ng-click="${delFunc}(${meta.row});" data-toggle="tooltip" data-placement="left" title="Xóa"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\" /><line x1=\"4\" y1=\"7\" x2=\"20\" y2=\"7\" /><line x1=\"10\" y1=\"11\" x2=\"10\" y2=\"17\" /><line x1=\"14\" y1=\"11\" x2=\"14\" y2=\"17\" /><path d=\"M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12\" /><path d=\"M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3\" /></svg></button>`;
            }
            if (recoverFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${recoverFunc}(${meta.row});" data-toggle="tooltip" data-placement="left" title="Khôi phục"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><path d=\"M15 4.55a8 8 0 0 0 -6 14.9m0 -4.45v5h-5\" /><line x1=\"18.37\" y1=\"7.16\" x2=\"18.37\" y2=\"7.17\" /><line x1=\"13\" y1=\"19.94\" x2=\"13\" y2=\"19.95\" /><line x1=\"16.84\" y1=\"18.37\" x2=\"16.84\" y2=\"18.38\" /><line x1=\"19.37\" y1=\"15.1\" x2=\"19.37\" y2=\"15.11\" /><line x1=\"19.94\" y1=\"11\" x2=\"19.94\" y2=\"11.01\" /></svg></button>`;
            }
            if (lichsuTrucFunc) {
                action += `&nbsp;<button type="button" class="btn btn-outline-primary btn-icon action" ng-click="${lichsuTrucFunc}('${meta.row}');" data-toggle="tooltip" data-placement="left" title="Xem lịch sử"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><rect x=\"4\" y=\"5\" width=\"16\" height=\"16\" rx=\"2\" /><line x1=\"16\" y1=\"3\" x2=\"16\" y2=\"7\" /><line x1=\"8\" y1=\"3\" x2=\"8\" y2=\"7\" /><line x1=\"4\" y1=\"11\" x2=\"20\" y2=\"11\" /><rect x=\"8\" y=\"15\" width=\"2\" height=\"2\" /></svg></button>`;
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
                case 11:
                    return "<span class='badge bg-orange me-1'></span> Đã hết hiệu lực";
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
        validateName: function(string){
            if(string === null) {
                return true;
            }
            var re = /^[a-zA-Z0-9,|_()<>&%$#\/\\\-\[\]\s]+$/;
            return re.test(this.removeVietnameseTones(string));
        },
        validateVietnameseCharacterWithNumber: function(string){
            if(string === null) {
                return true;
            }
            var re = /^[a-zA-Z0-9\s]+$/;
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
