# -*- coding: UTF-8 -*-
#
# Common Functions
import time
import hashlib


def get_cur_time():
    r = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
    return r


def get_md5_value(src):
    str = src.encode('utf-8')
    md5 = hashlib.md5()
    md5.update(str)
    value = md5.hexdigest()
    return value
