<?php
/**
 * User: Derek
 * Date: 2018-02-28
 */

function db_order_insert($order, $user)
{
    $data = array(
        'order_sn'   => $order['order_sn'],
        'user_id' => $user['user_id'],
        'user_name' => $user['user_name'],
        'issue_num'    => $order['issue_num'],
        'line'  => $order['line'],
        'value'     => $order['value'],
        'amount' => $order['amount'],
        'status'     => $order['status'],
        'add_time'  => date("Y-m-d H:i:s")
    );
    $stat = $GLOBALS['db']->insert('order', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        //exit (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}

function db_order_cancel($userId, $issueNum)
{
    $state = $GLOBALS['db']->update('order', 
        [
            'status' => -1,
        ],
        [
            'user_id' => $userId,
            'issue_num' => $issueNum
        ]
    );
    if ($state->rowCount() >= 0) {
        return true;
    } else {
        return false;
    }
}

function db_get_order_new($issueNum, $maxSize)
{
    $orders = $GLOBALS['db']->select('order',
        ['order_id', 'order_sn', 'user_id', 'user_name', 'issue_num', 'line', 'value', 'amount', 'status', 'add_time'],
        [
            'issue_num' => $issueNum,
            'status' => 0,
            'LIMIT' => $maxSize
        ]
    );
    return $orders;
}

function db_update_order_status($orderId, $status)
{
    $state = $GLOBALS['db']->update('order', 
        [
            'status' => $status
        ],
        [
            'order_id' => $orderId
        ]
    );
    if ($state->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}

function db_get_user_orders_cost($userId)
{
    $curDate = date("Y-m-d");
    $begin = $curDate . " 00:00:00";
    $end = $curDate . " 23:59:59";
    
    $sum = $GLOBALS['db']->sum('order', 'amount',
        [
            'user_id' => $userId,
            'status[>]' => 0,
            'add_time[>=]' => $begin,
            'add_time[<=]' => $end
        ]
    );
    return $sum;
}

function db_order_win_sum($userId)
{
    $sum = $GLOBALS['db']->sum('order', 'amount',
        [
            'user_id' => $userId,
            'status' => 2
        ]
    );
    return $sum;
}

function db_order_lose_sum($userId)
{
    $sum = $GLOBALS['db']->sum('order', 'amount',
        [
            'user_id' => $userId,
            'status' => 1
        ]
    );
    return $sum;
}

function db_order_frozen_sum($userId)
{
    $sum = $GLOBALS['db']->sum('order', 'amount',
        [
            'user_id' => $userId,
            'status' => 0
        ]
    );
    return $sum;
}

function db_get_order_for_verify($userId, $issueNum)
{
    $orders = $GLOBALS['db']->select('order',
        ['order_id', 'order_sn', 'user_id', 'user_name', 'issue_num', 'line', 'value', 'amount', 'status', 'add_time'],
        [
            'user_id' => $userId,
            'issue_num' => $issueNum,
            'status' => 0,
            'LIMIT' => 100
        ]
    );
    return $orders;
}

function db_get_order_for_won($userId, $issueNum)
{
    $orders = $GLOBALS['db']->select('order',
        ['order_id', 'order_sn', 'user_id', 'user_name', 'issue_num', 'line', 'value', 'amount', 'status', 'add_time'],
        [
            'user_id' => $userId,
            'issue_num' => $issueNum,
            'status' => 2,
            'LIMIT' => 100
        ]
    );
    return $orders;
}

function db_cancel_old_order_undealed()
{
    $state = $GLOBALS['db']->update('order', 
        [
            'status' => -1
        ],
        [
            'status' => 0,
            'add_time[<]'  => date("Y-m-d H:i:s")
        ]
    );
    return $state->rowCount();
}
