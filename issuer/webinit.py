# -*- coding: UTF-8 -*-
#
from webreport import WebReport
from config import *


class WebInit(WebReport):
    lottery = None

    def __init__(self, lty):
        self.lottery = lty
        super(WebInit, self).__init__()
        pass

    def launch(self):
        url = "http://" + SERVER_HOST + "/Issuer/Issue/do.php"
        init_json = {
            'act': "init"
        }

        resp = self.http_post_md5(url, init_json)
        if resp:
            if resp['success']:
                self.lottery.curLotteryType = resp['data']['curLotteryType']
                self.lottery.lastIssueNum = resp['data']['lastIssueNum']
                self.lottery.lastIssueTime = resp['data']['lastIssueTime']
                self.lottery.nextIssueNum = resp['data']['nextIssueNum']
                self.lottery.nextIssueTime = resp['data']['nextIssueTime']
                self.lottery.initialized = True
            else:
                self.lottery.initialized = False
        else:
            self.lottery.initialized = False
        pass
