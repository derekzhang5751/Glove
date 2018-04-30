<?php
/**
 * User: Derek
 * Date: 2018-04-30
 */

function db_charge_insert($money)
{
    $data = array(
        'charge_sn'  => $money['charge_id'],
        'user_id'    => $money['user_id'],
        'user_name'  => $money['user_name'],
        'amount'     => $money['amount'],
        'status'     => $money['status'],
        'req_time'   => $money['req_time'],
    );
    $stat = $GLOBALS['db']->insert('charge', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        //exit (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}

function db_money_balance($userId)
{
    $balance = $GLOBALS['db']->get('money', 'balance',
        [
            'user_id' => $userId,
            //'status' => 1,
            'ORDER' => ['id' => 'DESC']
        ]);
    if ($balance) {
        return $balance;
    } else {
        return 0.0;
    }
}
