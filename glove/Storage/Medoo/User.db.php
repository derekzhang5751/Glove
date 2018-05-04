<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */

function db_get_user_list()
{
    $users = $GLOBALS['db']->select('user',
        ['user_id', 'user_name', 'nick_name', 'password', 'reg_time', 'last_time', 'achat_name', 'group_name'],
        [
            'ORDER' => ['user_id' => 'ASC']
        ]
    );
    return $users;
}

function db_check_admin_password($username, $password)
{
    $users = $GLOBALS['db']->get('admin',
        ['admin_id', 'user_name', 'password', 'level'],
        [
            'user_name' => $username,
            'password'  => $password
        ]
    );
    return $users;
}


function db_get_user($userName)
{
    $user = $GLOBALS['db']->get('user',
        ['user_id', 'user_name', 'nick_name', 'password', 'reg_time', 'last_time', 'achat_name', 'group_name'],
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
        'nick_name'  => $user['nick_name'],
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
