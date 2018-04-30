<?php
/**
 * User: Derek
 * Date: 2018-02-28
 */

function db_order_insert($order)
{
    $data = array(
        'issue_num'    => $order['issue_num'],
        'line'  => $order['line'],
        'value'     => $order['value'],
        'amount' => $order['amount'],
        'msg_id'   => $order['msg_id'],
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
