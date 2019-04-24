<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>oauth github</title>
</head>
<body>
登陆中...
<script>

    window.onload = function () {
        window.opener.postMessage('{!! $response !!}', '{{ env('APP_URL') }}');
        window.close();
    }
</script>
</body>
</html>
