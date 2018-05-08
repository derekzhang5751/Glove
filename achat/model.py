# -*- coding: UTF-8 -*-
#
# Data Model


class Contact(object):
    id = 0
    achat_name = ""
    uin = 0
    user_name = ""
    nick_name = ""
    remark_name = ""
    flag = 0
    member_count = 0
    sex = 0
    signature = ""
    isowner = 0


class Message(object):
    id = 0
    from_user = ""
    from_nick = ""
    from_remark = ""
    to_user = ""
    to_nick = ""
    content = ""
    recvtime = ""
    reply = ""
    sendtime = ""
    status = 0

class Inquiry(object):
    action = ""
    issue_num = ""
    user_list = []
