var NotificationService = (function () {
    return {
        delayTime: 500000000,
        success: function (message, title = 'Thành công', onClosed = null) {
            $.notify({
                icon: 'fa fa-bell',
                title: title,
                message: message
            },
                {
                    type: 'success',
                    z_index: 1060,
                    placement: { from: 'top', align: 'right' },
                    time: this.delayTime,
                    onClosed: onClosed
                });
        },

        error: function (message, title = 'Không thể thực hiện', onClosed = null) {
            $.notify({
                icon: 'fa fa-exclamation-triangle',
                title: title,
                message: message
            },
                {
                    type: 'danger',
                    z_index: 1060,
                    placement: { from: 'top', align: 'right' }, time: this.delayTime
                });
        },

        warning: function (message, title = 'Cảnh báo', onClosed = null) {
            $.notify({
                icon: 'fa fa-exclamation-triangle',
                title: title,
                message: message
            },
                {
                    type: 'warning',
                    z_index: 1060,
                    placement: { from: 'top', align: 'right' }, time: this.delayTime
                });
        },
        info: function (message, title = 'Thông tin', onClosed = null) {
            $.notify({
                icon: 'fa fa-info-circle',
                title: title,
                message: message
            },
                {
                    type: 'info',
                    z_index: 1060,
                    placement: { from: 'top', align: 'right' }, time: this.delayTime
                });
        },
        notify: function (message, type) {
            switch (type) {
                case "-1", 1:
                    this.error(message);
                    break;
                case "0", 0:
                    this.warning(message);
                    break;
                case "1", 1:
                    this.warning(message);
                    break;
                case "2", 2:
                    this.success(message);
                    break;
                default:
                    this.info(message);
                    break;
            }
        },

        alert: function (message, header = "Thông báo", labelBtn = "Đóng") {
            alertify.alert().setHeader(header)
                .setting({
                    'label': labelBtn,
                    'message': message,
                    'transition': 'zoom'
                }).show();
        },

        confirm: function (message, header = "Thông báo", cancel = "Đóng", ok = "Thực hiện") {
            return new window.Promise((resolve, reject) => {
                alertify.confirm().setHeader(header).set('labels', { cancel: cancel, ok: ok })
                    .setting({
                        'transition': 'fade',
                        'message': message,
                        'onok': function () { resolve(true); },
                        'oncancel': function () { reject(false); }
                    }).show();
            });
        }

    }
}())
