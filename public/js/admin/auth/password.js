const updatePwdUrl = '/password'

var updatePwdObj = {

    init: function() {
        this.initEvent();
    },

    // 初始化事件
    initEvent: function() {
        let self = this;
        let updatePwdButton = document.getElementById('js-update-password');

        updatePwdButton.addEventListener('click', function() {
            self.updatePwd()
        })
    },

    updatePwd: function () {
        let password = $('#password').val().trim()
        let passwordConfirm = $('#password_confirmation').val().trim()

        if (password && passwordConfirm === password) {
            let data = {
                '_token': $Global.getCsrfToken(),
                'password': password,
                'password_confirmation': passwordConfirm
            }

            $.post(updatePwdUrl, data, function(res) {
                if (res.code === 0) {
                    window.location.href = '/login';
                } else {
                    $Toast.show(res.message, 'error');
                }
            })
        } else {
            $Toast.show('输入有误', 'error');
        }
    },
}

updatePwdObj.init();
