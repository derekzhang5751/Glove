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

function db_get_charge_by_sn($sn)
{
    $charge = $GLOBALS['db']->get('charge',
        ['id', 'charge_sn', 'user_id', 'user_name', 'amount', 'status', 'req_time'],
        [
            'charge_sn' => $sn,
            'status' => 0
        ]
    );
    return $charge;
}

function db_set_charge_status($sn, $status)
{
    $state = $GLOBALS['db']->update('charge',
        [
            'status' => $status
        ],
        [
            'charge_sn' => $sn
        ]
    );
    if ($state->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}

function db_money_balance($userId)
{
    $balance = $GLOBALS['db']->get('money', 'balance',
        [
            'user_id' => $userId,
            'ORDER' => ['id' => 'DESC']
        ]);
    if ($balance) {
        return $balance;
    } else {
        return 0.0;
    }
}

function db_money_insert($money)
{
    $data = array(
        'user_id'    => $money['user_id'],
        'user_name'  => $money['user_name'],
        'amount'     => $money['amount'],
        'balance'  => $money['balance'],
        'source'  => $money['source'],
        'add_time'   => $money['add_time'],
        'status'     => $money['status'],
        'sn'     => $money['sn'],
    );
    $stat = $GLOBALS['db']->insert('money', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        //exit (var_dump( $GLOBALS['db']->error() ));
        return false;
    }
}

function db_won_by_sn($orderSn)
{
    $won = $GLOBALS['db']->get('money', 'amount',
        [
            'sn' => $orderSn
        ]);
    if ($won) {
        return intval($won);
    } else {
        return 0;
    }
}

function db_user_won_amount($userId)
{
    $curDate = date("Y-m-d");
    $begin = $curDate . " 00:00:00";
    $end = $curDate . " 23:59:59";
    
    $won = $GLOBALS['db']->sum('money', 'amount',
        [
            'user_id' => $userId,
            'source' => 1,
            'add_time[>=]' => $begin,
            'add_time[<=]' => $end
        ]);
    if ($won) {
        return intval($won);
    } else {
        return 0;
    }
}
