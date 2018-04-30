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
        ['order_id', 'order_sn', 'user_id', 'issue_num', 'line', 'value', 'amount', 'status', 'add_time'],
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
