<?php
/**
 * User: Derek
 * Date: 2018-04-09
 */

function db_get_user_list()
{
    $users = $GLOBALS['db']->select('user',
        ['user_id', 'user_name', 'password', 'reg_time', 'last_time', 'achat_name', 'group_name'],
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
