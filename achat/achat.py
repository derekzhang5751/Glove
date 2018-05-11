# -*- coding: UTF-8 -*-
#
# aChat Main Program
import sys
import logging
import itchat
from itchat.content import *
from storage import ChatDB
from model import Contact, Message
from webreport import WebReport
from atools import *
from config import *


ChatDB = ChatDB(DB_NAME)
WebRpt = WebReport(itchat)


@itchat.msg_register(TEXT)
def text_reply(msg):
    '''
    print "MsgType: " + str(msg.MsgType)
    print "MsgId: " + str(msg.MsgId)
    print "FromUserName: " + msg.FromUserName
    print "ToUserName: " + msg.ToUserName
    print "Content: " + msg.Content
    print "Message: " + msg.text
    '''
    ret = "Time is " + get_cur_time()
    return ret


@itchat.msg_register(TEXT, isGroupChat=True)
def text_reply(msg):
    '''
    print "MsgType: " + str(msg.MsgType)
    print "MsgId: " + str(msg.MsgId)
    print "FromUserName: " + msg.FromUserName
    print "ToUserName: " + msg.ToUserName
    print "Content: " + msg.Content
    print "Message: " + msg.text
    print "Actual Nick Name: " + msg.actualNickName
    print "IsAt: " + str(msg.isAt)
    '''
    # if msg.isAt:
    print "New Message: " + msg.text
    m = Message()
    m.id = 0
    m.from_user = msg.FromUserName
    m.from_nick = msg.actualNickName
    m.from_remark = ChatDB.get_remark_by_userid(m.from_user)
    m.to_user = msg.ToUserName
    m.to_nick = ChatDB.get_nickname_by_userid(m.to_user)
    m.content = msg.Content
    m.recvtime = get_cur_time()
    m.status = 0
    ChatDB.save_new_message(m)
    # msg.user.send(u'@%s\u2005I received: %s' % (msg.actualNickName, msg.text))
    pass


def test_insert_message():
    for i in range(1, 2):
        msg = Message()
        msg.id = 0
        msg.from_user = "@eifvejonvdlrkljrojflsjf"
        msg.from_nick = "常胜将军"
        msg.to_user = "@ildjflwjfdlsjflds"
        msg.to_nick = "李四"
        msg.content = "1，大，200"
        # msg.content = "取消"
        msg.recvtime = get_cur_time()
        msg.status = 0
        last_id = ChatDB.save_new_message(msg)
        print("Message ID inserted is " + str(last_id))

        msg.id = last_id
        msg.reply = "success"
        msg.sendtime = get_cur_time()
        msg.status = 2
        # ChatDB.update_reply_message(msg)
    pass


def main():
    reload(sys)
    sys.setdefaultencoding("utf-8")
    itchat.set_logging(True, "output.log", logging.DEBUG)

    print("=========== Achat start up ===========")
    itchat.auto_login(hotReload=True)
    ChatDB.delete_all_contacts()
    WebRpt.start()
    itchat.run()
    WebRpt.stop()
    # WebRpt.join()
    print("=========== Achat exit ===========")
    pass


if __name__ == '__main__':
    main()
    pass
