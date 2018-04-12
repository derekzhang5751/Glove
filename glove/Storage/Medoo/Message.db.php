<?php
/**
 * User: Derek
 * Date: 2018-02-28
 */

function db_message_insert($msg)
{
    $data = array(
        'achat_name' => $msg['achat_name'],
        'group_name' => $msg['group_name'],
        'achat_id' => $msg['id'],
        'from_userid' => '',
        'from_nick' => $msg['from_nick'],
        'to_userid' => '',
        'to_nick' => $msg['to_nick'],
        'msg' => $msg['content'],
        'recv_time' => $msg['recvtime'],
        'reply' => $msg['reply'],
        'send_time' => $msg['sendtime'],
        'status' => $msg['status']
    );
    $stat = $GLOBALS['db']->insert('achat_msg', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        return false;
    }
}


function db_get_user($userName)
{
    $user = $GLOBALS['db']->get('user',
        ['user_id', 'user_name', 'password', 'reg_time', 'last_time', 'achat_name', 'group_name'],
        [
            'user_name' => $userName,
        ]
    );
    return $user;
}

function db_user_insert($user)
{
    $data = array(
        'user_name'  => $user['user_name'],
        'password'   => '',
        'reg_time'   => $user['reg_time'],
        'last_time'  => $user['last_time'],
        'achat_name' => $user['achat_name'],
        'group_name' => $user['group_name']
    );
    $stat = $GLOBALS['db']->insert('user', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        return false;
    }
}


function db_money_insert($money)
{
    $data = array(
        'user_id'    => $money['user_id'],
        'user_name'  => $money['user_name'],
        'amount'     => $money['amount'],
        'req_source' => $money['req_source'],
        'req_time'   => $money['req_time'],
        'status'     => $money['status'],
        'charge_id'  => $money['charge_id']
    );
    $stat = $GLOBALS['db']->insert('money', $data);
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
            'status' => 1,
            'ORDER' => ['id' => 'DESC']
        ]);
    if ($balance) {
        return $balance;
    } else {
        return 0.0;
    }
}
