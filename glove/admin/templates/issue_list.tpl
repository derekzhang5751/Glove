<html>
    <head>
        <title>管理后台</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="/css/glove.css" />
    </head>
    <body>
        <p>开奖数据</p>
        <form method="post" action="/admin/Issue/do.php?act=list">
            <span>开奖时间(北京时间)：</span><input type="date" name="dayIssue" min="2018-01-01" value="{$IssueDate}">
            <span>&nbsp;&nbsp;&nbsp;类型：</span>
            {html_options name=typeIssue options=$arrIssueType selected=$IssueType}
            &nbsp;&nbsp;&nbsp;<input type="submit" value="查 询">
        </form>
        <table>
            <tr>
                <td>编号</td>
                <td>类型</td>
                <td>奖期</td>
                <td>第一名</td>
                <td>第二名</td>
                <td>第三名</td>
                <td>第四名</td>
                <td>第五名</td>
                <td>第六名</td>
                <td>第七名</td>
                <td>第八名</td>
                <td>第九名</td>
                <td>第十名</td>
                <td>时间</td>
                <td>状态</td>
            </tr>
            {foreach $IssueList as $key => $issue}
            <tr {if $issue.status eq 0}bgcolor="#FF0000"{/if}>
                <td>{$key+1}</td>
                <td>
                    {if $issue.type eq 0}
                        PK10
                    {else}
                        XYFT
                    {/if}
                </td>
                <td>{$issue.issue_num}</td>
                <td>{$issue.n0}</td>
                <td>{$issue.n1}</td>
                <td>{$issue.n2}</td>
                <td>{$issue.n3}</td>
                <td>{$issue.n4}</td>
                <td>{$issue.n5}</td>
                <td>{$issue.n6}</td>
                <td>{$issue.n7}</td>
                <td>{$issue.n8}</td>
                <td>{$issue.n9}</td>
                <td>{$issue.issue_time}</td>
                <td>
                    {if $issue.status eq 1}
                        已开
                    {else}
                        未开
                    {/if}
                </td>
            </tr>
            {/foreach}
        </table>
    </body>
</html>
