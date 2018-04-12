# -*- coding: UTF-8 -*-
#
import threading
import requests, json, base64
from storage import ChatDB
from config import *
from atools import *


class WebReport(threading.Thread):
    __exit_flag = False
    chat_db = None
    chat_it = None

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
        else:
            if resp.status_code == requests.codes.ok:
                # print("Recv: " + resp.text)
                j = resp.json()
                msg.reply = j['data']['reply']
                msg.sendtime = j['data']['sendtime']
                msg.status = j['data']['status']
                return True
            else:
                return False

    def run(self):
        self.chat_db = ChatDB(DB_NAME)
        while not self.__exit_flag:
            # print("Thread is running ...")
            time.sleep(1.0)
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
            # self.__exit_flag = True
        # print("Thread is exit.")
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
