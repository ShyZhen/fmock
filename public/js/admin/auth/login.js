const loginUrl = '/login'

function doLogin() {
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
