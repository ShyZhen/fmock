const searchUserUrl = '/user/all'

var userObj = {

    // 初始化数据
    init: function() {
        this.getUserList()
        this.initEvent();
    },

    // 初始化绑定的动作事件
    initEvent: function() {
        // let self = this;
        // let loginButton = document.getElementById('js-dologin');
        //
        // loginButton.addEventListener('click', function() {
        //     self.doLogin()
        // })
    },

    // 获取用户列表(全部、按条件过滤)
    getUserList: function () {
        let data = {
            '_token': $Global.getCsrfToken(),
            'uuid': '',
            'email': '',
            'mobile': '13476835720',
            'name': ''
        }

        $.post(searchUserUrl, data, function(res) {
            if (res.code === 0) {
                console.log(res)
            } else {
                $Toast.show(res.message, 'error');
            }
        })
    },

    doLogin: function () {
        let account = $('#account').val().trim()
        let password = $('#password').val().trim()

        if (account && password) {
            let data = {
                '_token': $Global.getCsrfToken(),
                'account': account,
                'password': password
            }

            $.post(loginUrl, data, function(res) {
                if (res.code === 0) {
                    window.location.href = '/dashboard';
                } else {
                    $Toast.show(res.message, 'error');
                }
            })
        } else {
            $Toast.show('账号密码不得为空', 'error');
        }
    }
}

userObj.init();
