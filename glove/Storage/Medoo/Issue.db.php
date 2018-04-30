<?php
/**
 * User: Derek
 * Date: 2018-04-24
 */
function db_get_issue_count($type, $begin, $end)
{
    $count = $GLOBALS['db']->count('issues',
        [
            'type' => $type,
            'issue_time[>=]' => $begin,
            'issue_time[<=]' => $end
        ]
    );
    return $count;
}

function db_insert_issues($arrayData)
{
    $stat = $GLOBALS['db']->insert('issues', $arrayData);
    if ($stat->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}

function db_get_next_issue($type)
{
    $issue = $GLOBALS['db']->get('issues',
        ['issue_num', 'issue_time'],
        [
            'type' => $type,
            'status' => 0,
            'issue_time[>]' => date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . " -3 minute")),
            'ORDER' => ['issue_time' => 'ASC']
        ]
    );
    return $issue;
}

function db_get_last_issue($type)
{
    $issue = $GLOBALS['db']->get('issues',
        ['issue_num', 'issue_time'],
        [
            'type' => $type,
            'status' => 1,
            'issue_time[<=]'  => date("Y-m-d H:i:s"),
            'ORDER' => ['issue_time' => 'DESC']
        ]
    );
    return $issue;
}

function db_update_issue($type, $issueNum, $arrayData)
{
    $state = $GLOBALS['db']->update('issues', $arrayData,
        [
            'type' => $type,
            'issue_num' => $issueNum
        ]
    );
    if ($state->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}

function db_get_issue_data($issueNum)
{
    $issue = $GLOBALS['db']->get('issues',
        ['issue_id', 'type', 'issue_num', 'n0', 'n1', 'n2', 'n3', 'n4', 'n5', 'n6', 'n7', 'n8', 'n9', 'status', 'issue_time', 'real_time'],
        [
            'issue_num' => $issueNum
        ]
    );
    return $issue;
}
