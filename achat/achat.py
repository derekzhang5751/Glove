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
    if msg.isAt:
        m = Message()
        m.id = 0
        m.from_user = msg.FromUserName
        m.from_nick = msg.actualNickName
        m.to_user = msg.ToUserName
        m.to_nick = ChatDB.get_nickname_by_userid(m.to_user)
        m.content = msg.Content
        m.recvtime = get_cur_time()
        m.status = 0
        ChatDB.save_new_message(m)
        # msg.user.send(u'@%s\u2005I received: %s' % (msg.actualNickName, msg.text))
    pass


def read_all_contact():
    contact_list = itchat.get_contact()
    for c in contact_list:
        contact = Contact()
        contact.id = 0
        contact.achat_name = ACHAT_NAME
        contact.uin = c.Uin
        contact.user_name = c.UserName
        contact.nick_name = c.NickName
        contact.remark_name = c.RemarkName
        contact.flag = c.ContactFlag
        contact.member_count = c.MemberCount
        contact.sex = c.Sex
        contact.signature = c.Signature
        contact.isowner = c.IsOwner

        last_id = ChatDB.save_new_contact(contact)
        print("Contact ID inserted is " + str(last_id))
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
    # itchat.set_logging(True, "output.log", logging.DEBUG)

    print("=========== Achat start up ===========")
    # itchat.auto_login(hotReload=True)
    # ChatDB.delete_all_contacts()
    read_all_contact()
    WebRpt.start()
    # itchat.run()
    # WebRpt.stop()
    WebRpt.join()
    print("=========== Achat exit ===========")
    pass


if __name__ == '__main__':
    test_insert_message()
    main()
    pass
