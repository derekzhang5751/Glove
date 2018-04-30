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
        'cmd' => $msg['cmd'],
        'recv_time' => $msg['recvtime'],
        'reply' => $msg['reply'],
        'send_time' => $msg['sendtime'],
        'status' => $msg['status'],
        'link_id' => $msg['link_id']
    );
    $stat = $GLOBALS['db']->insert('achat_msg', $data);
    if ($stat->rowCount() == 1) {
        return $GLOBALS['db']->id();
    } else {
        return false;
    }
}
