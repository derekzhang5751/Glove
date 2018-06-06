<html>
    <head>
        <title>管理后台</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <p>运营数据统计</p>
        <form method="post" action="/admin/main/do.php?act=monitor">
            <span>统计时间段(北京时间)：</span><input type="date" name="daybegin" min="2018-01-01" value="{$dayBegin}">
            <span>&nbsp;至&nbsp;&nbsp;</span><input type="date" name="dayend" min="2018-01-01" value="{$dayEnd}">
            <input type="submit" value="统计">
        </form>
        <table border="1" cellspacing="0" bordercolor="#000000" width="80%" style="border-collapse:collapse;">
            <tr>
                <td>编号</td>
                <td>用户</td>
                <td>流水</td>
                <td>胜负</td>
                <td>余额</td>
            </tr>
            {foreach $UserList as $key => $user}
            <tr>
                <td>{$key+1}</td>
                <td>{$user.user_name}</td>
                <td>{$user.total}</td>
                <td>{$user.won}</td>
                <td>{$user.balance}</td>
            </tr>
            {/foreach}
        </table>
    </body>
</html>
