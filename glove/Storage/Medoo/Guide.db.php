<?php
/**
 * User: Derek
 * Date: 2018-06-20
 */

function db_get_guide_flag($issueNum)
{
    $guide = $GLOBALS['db']->get('guide',
        ['id', 'issue_num', 'remark'],
        [
            'issue_num' => $issueNum
        ]
    );
    return $guide;
}

function db_insert_guide($arrayData)
{
    $stat = $GLOBALS['db']->insert('guide', $arrayData);
    if ($stat->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}

function db_update_guide($issueNum, $arrayData)
{
    $state = $GLOBALS['db']->update('guide', $arrayData,
        [
            'issue_num' => $issueNum
        ]
    );
    if ($state->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}
