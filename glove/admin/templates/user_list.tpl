<html>
    <head>
        <title>管理后台</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="/css/glove.css">
    </head>
    <body>
        <p>用户列表</p>
        <table border="1" cellspacing="0" bordercolor="#000000" width="80%" style="border-collapse:collapse;">
            <tr>
                <td>编号</td>
                <td>机器名</td>
                <td>群名</td>
                <td>用户名</td>
                <td>注册时间</td>
                <td>角色</td>
            </tr>
            {foreach $UserList as $key => $user}
            <tr>
                <td>{$key+1}</td>
                <td>{$user.achat_name}</td>
                <td>{$user.group_name}</td>
                <td>{$user.user_name}</td>
                <td>{$user.reg_time}</td>
                <td>
                    {html_options name=UserRole options=$arrRoles selected=$user.role}
                </td>
            </tr>
            {/foreach}
        </table>
    </body>
</html>
