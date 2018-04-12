# -*- coding: UTF-8 -*-
#
import sqlite3
from model import Contact, Message
from config import *


class ChatDB(object):
    __dbName = DB_NAME
    __tableContactName = "contacts"
    __tableMessageName = "message"
    __conn = None

    def __init__(self, db_name):
        self.__dbName = db_name
        self.__create_db()
        self.__create_table_contacts()
        self.__create_table_message()
        pass

    def close(self):
        if self.__conn is not None:
            self.__conn.close()
            self.__conn = None
        pass

    def __create_db(self):
        self.__conn = sqlite3.connect(self.__dbName)
        if self.__conn is None:
            raise Exception("打开数据库失败!!!")
        self.__conn.text_factory = str
        # print("connect to db success " + self.__dbName)
        # print("Sqlite3 Version: " + sqlite3.sqlite_version)
        pass

    def __table_exist(self, table_name):
        c = self.__conn.cursor()
        p = (str(table_name),)
        c.execute("SELECT name FROM sqlite_master WHERE type='table' AND name=?", p)
        row = c.fetchone()
        if row is None:
            # print("table %s is not exist." % table_name)
            return False
        else:
            # print("table %s is exist." % table_name)
            return True

    def __create_table_contacts(self):
        table_name = self.__tableContactName
        if self.__table_exist(table_name):
            return
        c = self.__conn.cursor()
        c.execute('''CREATE TABLE {} (id INTEGER PRIMARY KEY, achat_name TEXT, uin INTEGER, user_name TEXT,
                nick_name TEXT, remark_name TEXT, flag INTEGER, member_count INTEGER, sex INTEGER,
                signature TEXT, isowner INTEGER)'''.format(table_name))
        self.__conn.commit()
        pass

    def __create_table_message(self):
        table_name = self.__tableMessageName
        if self.__table_exist(table_name):
            return
        c = self.__conn.cursor()
        c.execute('''CREATE TABLE {} (id INTEGER PRIMARY KEY, from_user TEXT, from_nick TEXT, to_user TEXT, to_nick TEXT,
                content TEXT, recvtime TEXT, reply TEXT, sendtime TEXT, status INTEGER)'''.format(table_name))
        self.__conn.commit()
        pass

    def save_new_message(self, msg):
        table_name = self.__tableMessageName
        c = self.__conn.cursor()
        p = (msg.from_user, msg.from_nick, msg.to_user, msg.to_nick, msg.content, msg.recvtime)
        c.execute('''INSERT INTO {} (from_user,from_nick,to_user,to_nick,content,recvtime,reply,sendtime,status) 
                VALUES (?, ?, ?, ?, ?, ?, '', '', 0)'''.format(table_name), p)
        self.__conn.commit()
        return c.lastrowid

    def update_reply_message(self, msg):
        table_name = self.__tableMessageName
        c = self.__conn.cursor()
        p = (msg.reply, msg.sendtime, msg.status, msg.id)
        c.execute('''UPDATE {} SET reply=?, sendtime=?, status=? WHERE id=?'''.format(table_name), p)
        self.__conn.commit()
        pass

    def save_new_contact(self, contact):
        table_name = self.__tableContactName
        c = self.__conn.cursor()
        p = (contact.achat_name, contact.uin, contact.user_name, contact.nick_name, contact.remark_name,
             contact.flag, contact.member_count, contact.sex, contact.signature, contact.isowner)
        c.execute('''INSERT INTO {} (achat_name, uin, user_name, nick_name, remark_name, flag, member_count,
                sex, signature, isowner) VALUES (?,?,?,?,?,?,?,?,?,?)'''.format(table_name), p)
        self.__conn.commit()
        return c.lastrowid

    def delete_all_contacts(self):
        table_name = self.__tableContactName
        c = self.__conn.cursor()
        c.execute("DELETE FROM {}".format(table_name))
        self.__conn.commit()
        pass

    def read_message(self, msg_list):
        count = 0
        c = self.__conn.cursor()
        c.execute("SELECT id,from_user,from_nick,to_user,to_nick,content,recvtime,reply,sendtime,status FROM message WHERE status=0 ORDER BY id LIMIT 5")
        rows = c.fetchall()
        if not rows:
            return count
        for row in rows:
            msg = Message()
            msg.id        = row[0]
            msg.from_user = row[1]
            msg.from_nick = row[2]
            msg.to_user   = row[3]
            msg.to_nick   = row[4]
            msg.content   = row[5]
            msg.recvtime  = row[6]
            msg.reply     = row[7]
            msg.sendtime  = row[8]
            msg.status    = row[9]
            msg_list.append(msg)
            count = count + 1
            print("read message id " + str(msg.id))
            if count > 10:
                break;
        return count

    def get_nickname_by_userid(self, user_id):
        c = self.__conn.cursor()
        p = (str(user_id),)
        c.execute("SELECT nick_name FROM contacts WHERE user_name=?", p)
        row = c.fetchone()
        if row is None:
            return ""
        else:
            return row[0]

    def get_userid_by_nickname(self, nick_name):
        c = self.__conn.cursor()
        p = (str(nick_name),)
        c.execute("SELECT user_name FROM contacts WHERE nick_name=?", p)
        row = c.fetchone()
        if row is None:
            return ""
        else:
            return row[0]

    def get_userid_by_msgid(self, msg_id):
        c = self.__conn.cursor()
        p = (msg_id,)
        c.execute("SELECT from_user FROM message WHERE id=?", p)
        row = c.fetchone()
        if row is None:
            return ""
        else:
            return row[0]


