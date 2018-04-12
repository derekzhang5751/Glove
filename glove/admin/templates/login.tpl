<html>
    <head>
        <title>管理后台</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <form id="loginform" method="post" action="/admin/login/do.php?act=login">
        <table align="center" valign="middle" style="margin-top: 50px;">
            <tr style="height: 70px;">
                <td colspan="2" align="center"><font size="5">用户登录</font></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><font size="2">{$msg}</font></td>
            </tr>
            <tr style="height: 35px;">
                <td><font size="3">用户名：</font></td>
                <td><input id="username" name="username" type="text" style="font-size: 16px; height: 30px; line-height: 30px;"></td>
            </tr>
            <tr style="height: 35px;">
                <td><font size="3">密&emsp;码：</font></td>
                <td><input id="password" name="password" type="password" style="font-size: 16px; height: 30px; line-height: 30px;"></td>
            </tr>
            <tr style="height: 40px;">
                <td><font size="3">验证码：</font></td>
                <td>
                    <input id="captcha" name="captcha" type="text" style="font-size: 16px; width: 70px; height: 30px; line-height: 30px;" value="">
                </td>
            </tr>
            <tr style="height: 40px;">
                <td></td>
                <td>
                    <img src="{$CaptchaPic}" style="height: 40px; width: 150px;" />
                </td>
            </tr>
            <tr style="height: 50px;">
                <td colspan="2" align="center">
                    <button type="submit" form="loginform" style="width: 100px; height: 30px; font-size: 14px;">登&emsp;录</button>
                </td>
            </tr>
        </table>
        </form>
    </body>
</html>
