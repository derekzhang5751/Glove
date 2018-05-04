# -*- coding: UTF-8 -*-
#
import threading
import requests, json, base64
from storage import ChatDB
from config import *
from atools import *
from schedule import *
from model import Contact
from pprint import pprint


class WebReport(threading.Thread):
    __exit_flag = False
    chat_db = None
    chat_it = None
    room_name = ''
    room_nick = ''

    def __init__(self, it):
        threading.Thread.__init__(self)
        self.chat_it = it
        pass

    def __http_msg_post(self, msg):
        url = "http://" + SERVER_HOST + "/Achat/Message/do.php"
        msg_struct = {
            'id': msg.id,
            'achat_name': ACHAT_NAME,
            'group_name': "GroupName",
            'from_nick': msg.from_nick,
            'from_remark': msg.from_remark,
            'to_nick': msg.to_nick,
            'content': msg.content,
            'recvtime': msg.recvtime,
            'status': msg.status
        }
        data = json.dumps(msg_struct)
        base64_str = base64.b64encode(data)
        md5 = get_md5_value(base64_str + MD5_KEY)
        # print("Md5: " + md5)
        p = {'version': '1.0', 'DeviceType': '1', 'md5': md5, 'data': base64_str}
        try:
            resp = requests.post(url, data=p, timeout=5.0)
        except requests.exceptions.RequestException:
            return False

        if resp.status_code == requests.codes.ok:
            try:
                # print("Recv: " + resp.text)
                j = resp.json()
            except ValueError:
                print("Data Error: " + resp.text)
                return False
            else:
                msg.reply = j['data']['reply']
                msg.sendtime = j['data']['sendtime']
                msg.status = j['data']['status']
                return True
        else:
            return False

    def do_init_env(self):
        print("[{}]Initializing it chat ...".format(time.strftime("%H:%M:%S", time.localtime())))
        time.sleep(5)
        # init chat room
        chatrooms = self.chat_it.get_chatrooms()
        for chatroom in chatrooms:
            # print("Chatroom userName: {}".format(chatroom.userName))
            if chatroom.nickName == ROOM_NAME:
                # print("chatroom nickName: {} is my room".format(chatroom.nickName))
                self.room_name = chatroom.userName
                self.room_nick = chatroom.nickName
            else:
                # print("chatroom nickName: {} is not my room".format(chatroom.nickName))
                pass
        # init contacts
        chat_room = self.chat_it.update_chatroom(self.room_name, detailedMember = True)
        for friend in chat_room['MemberList']:
            c = self.chat_it.search_friends(userName=friend['UserName'])
            # print("UserName: {}".format(c['UserName']))
            # print("DisplayName: {}".format(c['DisplayName']))
            # print("RemarkName: {}".format(c['RemarkName']))
            # print("NickName: {}".format(c['NickName']))
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
            contact.isowner = 0

            last_id = self.chat_db.save_new_contact(contact)
            print("Contact ID inserted is " + str(last_id))
        #
        print("Initializing it chat completed.")
        pass

    def do_last_term(self):
        print("[{}]Last Term ...".format(time.strftime("%H:%M:%S", time.localtime())))
        to_user = self.room_name
        self.chat_it.send_msg(STR_LAST_TERM, to_user)
        pass

    def do_welcome(self):
        print("[{}]Welcome ...".format(time.strftime("%H:%M:%S", time.localtime())))
        to_user = self.room_name
        self.chat_it.send_msg(STR_WELCOME, to_user)
        pass

    def do_order(self):
        # print("[{}]Order ...".format(time.strftime("%H:%M:%S", time.localtime())))
        msg_list = []
        count = self.chat_db.read_message(msg_list)
        for i in range(0, count):
            msg = msg_list[i]
            print("Process Message [{}] {} {}".format(msg.id, msg.recvtime, msg.content))
            if self.__http_msg_post(msg):
                self.chat_db.update_reply_message(msg)
                # from_user = self.chat_db.get_userid_by_nickname(msg.from_nick)
                from_user = self.chat_db.get_userid_by_msgid(msg.id)
                reply_text = "@{} {}".format(msg.from_nick, msg.reply)
                self.chat_it.send_msg(reply_text, from_user)
        pass

    def do_end_tip(self):
        print("[{}]End tip ...".format(time.strftime("%H:%M:%S", time.localtime())))
        to_user = self.room_name
        self.chat_it.send_msg(STR_END_TIP, to_user)
        pass

    def do_end(self):
        print("[{}]End ...".format(time.strftime("%H:%M:%S", time.localtime())))
        to_user = self.room_name
        self.chat_it.send_msg(STR_END, to_user)
        pass

    def do_check(self):
        print("[{}]Check ...".format(time.strftime("%H:%M:%S", time.localtime())))
        to_user = self.room_name
        self.chat_it.send_msg(STR_CHECK, to_user)
        pass

    def do_issue(self):
        print("[{}]Issue ...".format(time.strftime("%H:%M:%S", time.localtime())))
        to_user = self.room_name
        self.chat_it.send_msg(STR_ISSUE, to_user)
        pass

    def run(self):
        self.chat_db = ChatDB(DB_NAME)
        schedule = Schedule()
        last_step = STEP_NULL

        print("Thread is running ...")
        while not self.__exit_flag:
            step = schedule.next()
            if step == last_step:
                if step == STEP_CLASS or step == STEP_END_TIP:
                    self.do_order()
                wait = schedule.get_next_wait()
                time.sleep(wait)
                continue
            pass
            last_step = step

            if step == STEP_INIT_ENV:
                self.do_init_env()
            elif step == STEP_LAST_TERM:
                self.do_last_term()
            elif step == STEP_WELCOME:
                self.do_welcome()
            # elif step == STEP_CLASS:
            #     self.do_order()
            elif step == STEP_END_TIP:
                self.do_end_tip()
            elif step == STEP_END:
                self.do_end()
            elif step == STEP_CHECK:
                self.do_check()
            elif step == STEP_ISSUE:
                self.do_issue()
            else:
                time.sleep(1)

            self.do_order()
            wait = schedule.get_next_wait()
            time.sleep(wait)
            # self.__exit_flag = True
            pass
        print("Thread is exit.")
        pass

    def stop(self):
        if not self.isAlive():
            return True
        self.__exit_flag = True
        self.join(60.0)
        if self.isAlive():
            return False
        else:
            return True
