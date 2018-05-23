# -*- coding: UTF-8 -*-
#
import threading
import requests, json, base64
import hashlib
from config import *


class WebReport(threading.Thread):
    __exit_flag = False

    def __init__(self):
        threading.Thread.__init__(self)
        pass

    def get_md5_value(self, src):
        str = src.encode('utf-8')
        md5 = hashlib.md5()
        md5.update(str)
        value = md5.hexdigest()
        return value

    def http_post_md5(self, url, data_json):
        data = json.dumps(data_json)
        base64_str = base64.b64encode(data)
        md5 = self.get_md5_value(base64_str + MD5_KEY)
        # print("Md5: " + md5)
        p = {'version': '1.0', 'DeviceType': '1', 'md5': md5, 'data': base64_str}
        try:
            resp = requests.post(url, data=p, timeout=5.0)
        except requests.exceptions.RequestException:
            return False

        if resp.status_code == requests.codes.ok:
            try:
                # print("MD5 Recv: " + resp.text)
                j = resp.json()
                return j
            except ValueError:
                print("MD5 Error: " + resp.text)
                return False
        else:
            return False
        pass

    def http_get(self, url):
        try:
            resp = requests.get(url, timeout=5.0, verify=False)
        except requests.exceptions.RequestException as e:
            print e
            return False
        else:
            if resp.status_code == requests.codes.ok:
                # print("Get Recv: " + resp.text)
                j = resp.json()
                return j
            else:
                print("Get Recv False: status_code=" + resp.status_code)
                return False
        pass

    def http_post(self, url, p):
        try:
            resp = requests.post(url, data=p, timeout=5.0)
        except requests.exceptions.RequestException:
            return False
        else:
            if resp.status_code == requests.codes.ok:
                # print("Post Recv: " + resp.text)
                j = resp.json()
                return j
            else:
                return False
        pass

    def launch(self):
        pass

    def run(self):
        self.launch()
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
