<html>
    <head>
        <title>管理后台</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="/css/glove.css" />
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
                <td>角色</td>
                <td>操作</td>
            </tr>
            {foreach $UserList as $key => $user}
            <tr>
                <td>{$key+1}</td>
                <td>{$user.achat_name}</td>
                <td>{$user.group_name}</td>
                <td>{$user.user_name}</td>
                <td>{$user.reg_time}</td>
                <td>
                    {if $user.role eq 1}
                        机器用户
                    {else}
                        普通用户
                    {/if}
                </td>
                <td>
                    [<a href="/admin/User/do.php?act=to_charge&userid={$user.user_id}">充值</a>]&nbsp;/&nbsp;
                    [<a href="/admin/User/do.php?act=to_withdraw&userid={$user.user_id}">提款</a>]&nbsp;/&nbsp;
                    [<a href="/admin/User/do.php?act=switchrole&userid={$user.user_id}&role={$user.role}">转变角色</a>]
                </td>
            </tr>
            {/foreach}
        </table>
    </body>
</html>
