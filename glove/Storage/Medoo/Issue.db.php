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

function db_get_next_issue($type, $timeOffset = 0)
{
    $offset = intval($timeOffset);
    if ($offset >= 0 || $offset < -5) {
        $offset = 0;
        $issueTime = strtotime(date("Y-m-d H:i:s"));
    } else {
        $issueTime = strtotime(date("Y-m-d H:i:s") . " " . strval($offset) . " minute");
    }
    //$issueTime = strtotime(date("Y-m-d H:i:s") . " -3 minute");
    
    $issue = $GLOBALS['db']->get('issues',
        ['issue_num', 'issue_time'],
        [
            'type' => $type,
            //'status' => 0,
            'issue_time[>=]' => date("Y-m-d H:i:s", $issueTime),
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
            //'status' => 1,
            'issue_time[<=]'  => date("Y-m-d H:i:s"),
            'ORDER' => ['issue_time' => 'DESC']
        ]
    );
    return $issue;
}

function db_get_last_term_issued($type, $maxSize)
{
    $issueList = $GLOBALS['db']->select('issues',
        ['type', 'issue_num', 'n0', 'n1', 'n2', 'n3', 'n4', 'n5', 'n6', 'n7', 'n8', 'n9', 'status', 'issue_time'],
        [
            'issue_time[>]' => date("Y-m-d 00:00:00"),
            'type' => $type,
            'status' => 1,
            'ORDER' => ['issue_time' => 'DESC'],
            'LIMIT' => $maxSize
        ]
    );
    return $issueList;
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

function db_get_issue_list($type, $begin, $end)
{
    $issueList = $GLOBALS['db']->select('issues',
        ['issue_id', 'type', 'issue_num', 'n0', 'n1', 'n2', 'n3', 'n4', 'n5', 'n6', 'n7', 'n8', 'n9', 'status', 'issue_time'],
        [
            'type' => $type,
            'issue_time[>=]' => $begin,
            'issue_time[<=]' => $end,
            'ORDER' => ['issue_id' => 'DESC']
        ]
    );
    return $issueList;
}
