<html>
    <head>
        <title>管理后台</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="/css/glove.css" />
    </head>
    <body>
        <p>用户充值提款</p>
        <form id="chargeForm" method="post" action="/admin/User/do.php?act=charge" >
        <table>
            <tr>
                <td style="width: 110px; text-align: right;">用户名：</td>
                <td style="padding-left: 10px;">{$user.user_name}</td>
            </tr>
            <tr>
                <td style="text-align: right;">群名：</td>
                <td style="padding-left: 10px;">{$user.group_name}</td>
            </tr>
            <tr>
                <td style="text-align: right;">机器名：</td>
                <td style="padding-left: 10px;">{$user.achat_name}</td>
            </tr>
            <tr>
                <td style="text-align: right;">注册时间：</td>
                <td style="padding-left: 10px;">{$user.reg_time}</td>
            </tr>
            <tr>
                <td style="text-align: right;">角色：</td>
                <td style="padding-left: 10px;">
                    {if $user.role eq 1}
                        机器用户
                    {else}
                        普通用户
                    {/if}
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">余额：</td>
                <td style="padding-left: 10px;">{$user.balance}</td>
            </tr>
            <tr>
                <td style="text-align: right;">
                    {if $operation eq 'charge'}
                        充值金额：
                    {else}
                        提款金额：
                    {/if}
                </td>
                <td style="padding-left: 10px;">
                    <input type="number" id="amount" name="amount" value="0">
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">摘要说明：</td>
                <td style="padding-left: 10px;">
                    <input type="text" id="remark" name="remark" value="" maxlength="12">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="hidden" name="userid" value="{$user.user_id}">
                    <input type="hidden" id="operation" name="operation" value="{$operation}">
                </td>
                <td style="padding-left: 10px;">
                    <input type="button" value="提 交" onclick="onSubmitClick()" style="height: 30px; width: 60px;">
                </td>
            </tr>
        </table>
        </form>
<script type="text/javascript">
    function onSubmitClick() {
        var amount = document.getElementById("amount").value;
        var operation = document.getElementById("operation").value;
        var msg = "确定要提款 " + amount + " 元吗?";
        if (operation === "charge") {
            msg = "确定要充值 " + amount + " 元吗?";
        }
        var r = confirm(msg);
        if (r === true) {
            document.getElementById("chargeForm").submit();
        }
    }
</script>
    </body>
</html>
