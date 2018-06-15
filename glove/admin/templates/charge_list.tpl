<html>
    <head>
        <title>管理后台</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="/css/glove.css" />
    </head>
    <body>
        <p>充值申请列表</p>
        <table>
            <tr>
                <td>编号</td>
                <td>SN</td>
                <td>用户名</td>
                <td>申请时间</td>
                <td>金额</td>
                <td>状态</td>
                <td>操作</td>
            </tr>
            {foreach $ChargeList as $key => $charge}
            <tr>
                <td>{$key+1}</td>
                <td>{$charge.charge_sn}</td>
                <td>{$charge.user_name}</td>
                <td>{$charge.req_time}</td>
                <td>{$charge.amount}</td>
                <td>
                    {if $charge.status eq 0}
                        未处理
                    {else}
                        已处理
                    {/if}
                </td>
                <td>
                    {if $charge.amount gte 0}
                        [<a href="/admin/Charge/do.php?act=to_charge&chargeid={$charge.id}">处理充值</a>]
                    {else}
                        [<a href="/admin/Charge/do.php?act=to_withdraw&chargeid={$charge.id}">处理提款</a>]
                    {/if}
                    &nbsp;/&nbsp;
                    [<a href="/admin/Charge/do.php?act=ignore&chargeid={$charge.id}">忽略</a>]
                </td>
            </tr>
            {/foreach}
        </table>
        <p>充值列表</p>
        <table>
            <tr>
                <td>编号</td>
                <td>用户名</td>
                <td>金额</td>
                <td>余额</td>
                <td>时间</td>
                <td>SN</td>
            </tr>
            {foreach $MoneyList as $key => $money}
            <tr>
                <td>{$key+1}</td>
                <td>{$money.user_name}</td>
                <td>{$money.amount}</td>
                <td>{$money.balance}</td>
                <td>{$money.add_time}</td>
                <td>{$money.sn}</td>
            </tr>
            {/foreach}
        </table>
    </body>
</html>
