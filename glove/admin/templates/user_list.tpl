<html>
    <head>
        <title>管理后台</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <p>用户列表</p>
        <table>
            <tr>
                <td>编号</td>
                <td>机器名</td>
                <td>群名</td>
                <td>用户名</td>
                <td>注册时间</td>
                <td>活跃时间</td>
            </tr>
            {foreach $UserList as $user}
            <tr>
                <td>#</td>
                <td>{$user.achat_name}</td>
                <td>{$user.group_name}</td>
                <td>{$user.user_name}</td>
                <td>{$user.reg_time}</td>
                <td>{$user.last_time}</td>
            </tr>
            {/foreach}
        </table>
    </body>
</html>
