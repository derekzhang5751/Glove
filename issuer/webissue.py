# -*- coding: UTF-8 -*-
#
import time
from webreport import WebReport
from config import *


class WebIssue(WebReport):
    lottery = None

    def __init__(self, lty):
        self.lottery = lty
        super(WebIssue, self).__init__()
        pass

    def kjkj88888_get_pk10(self, issue):
        print "Fetching PK10 {} ...".format(issue)
        self.lottery.gotIssueNum = 0

        url = "http://508590.com/api/pk10/getLotteryBase.php?issue={}".format(issue)
        resp = self.http_get(url)
        if resp and resp['errorCode']==0:
            arr_data = resp['result']['data']
            if len(arr_data) > 0:
                term = arr_data[0]
                self.lottery.gotIssueNum = term['preDrawIssue']
                self.lottery.gotIssueTime = term['preDrawTime']
                self.lottery.ranking[0] = term['firstNum']
                self.lottery.ranking[1] = term['secondNum']
                self.lottery.ranking[2] = term['thirdNum']
                self.lottery.ranking[3] = term['fourthNum']
                self.lottery.ranking[4] = term['fifthNum']
                self.lottery.ranking[5] = term['sixthNum']
                self.lottery.ranking[6] = term['seventhNum']
                self.lottery.ranking[7] = term['eighthNum']
                self.lottery.ranking[8] = term['ninthNum']
                self.lottery.ranking[9] = term['tenthNum']
                print "Got PK10 {} [{}]".format(self.lottery.gotIssueNum, self.lottery.gotIssueTime)
                print "Ranking: " + ', '.join(map(str, self.lottery.ranking))
                return
        print "Fetch PK10 {} ERROR !!!".format(issue)
        pass

    def ny1819_get_xyft(self, issue):
        issue_num = "20" + issue
        print "Fetching XYFT {} ...".format(issue_num)
        self.lottery.gotIssueNum = 0

        url = "http://kai.ny1819.com/xyft/getHistoryData.do"
        p = {'count': '1'}
        resp = self.http_post(url, p)
        if resp and resp['success']:
            arr_data = resp['rows']
            if len(arr_data) > 0:
                term = arr_data[0]
                if issue_num == term['termNum']:
                    self.lottery.gotIssueNum = term['termNum']
                    self.lottery.gotIssueTime = term['lotteryTime']
                    self.lottery.ranking[0] = term['n1']
                    self.lottery.ranking[1] = term['n2']
                    self.lottery.ranking[2] = term['n3']
                    self.lottery.ranking[3] = term['n4']
                    self.lottery.ranking[4] = term['n5']
                    self.lottery.ranking[5] = term['n6']
                    self.lottery.ranking[6] = term['n7']
                    self.lottery.ranking[7] = term['n8']
                    self.lottery.ranking[8] = term['n9']
                    self.lottery.ranking[9] = term['n10']
                    print "Got XYFT {} [{}]".format(self.lottery.gotIssueNum, self.lottery.gotIssueTime)
                    print "Ranking: " + ', '.join(map(str, self.lottery.ranking))
                    return
        print "Fetch XYFT {} ERROR !!!".format(issue)
        pass

    def fetch_issue(self):
        if self.lottery.curLotteryType == 1:
            self.ny1819_get_xyft(self.lottery.nextIssueNum)
        else:
            self.kjkj88888_get_pk10(self.lottery.nextIssueNum)

        if int(self.lottery.nextIssueNum) == int(self.lottery.gotIssueNum):
            return True
        else:
            return False
        pass

    def upload_issue(self):
        url = "http://" + SERVER_HOST + "/Issuer/Issue/do.php"
        issue_json = {
            'act': "issue",
            'type': self.lottery.curLotteryType,
            'issueNum': self.lottery.gotIssueNum,
            'issueTime': self.lottery.gotIssueTime,
            'n0': self.lottery.ranking[0],
            'n1': self.lottery.ranking[1],
            'n2': self.lottery.ranking[2],
            'n3': self.lottery.ranking[3],
            'n4': self.lottery.ranking[4],
            'n5': self.lottery.ranking[5],
            'n6': self.lottery.ranking[6],
            'n7': self.lottery.ranking[7],
            'n8': self.lottery.ranking[8],
            'n9': self.lottery.ranking[9]
        }

        resp = self.http_post_md5(url, issue_json)
        if resp:
            if resp['success']:
                self.lottery.nextIssueNum = resp['data']['nextIssueNum']
                self.lottery.nextIssueTime = resp['data']['nextIssueTime']
                return True
            else:
                return False
        else:
            return False
        pass

    def launch(self):
        retry = 2
        while retry > 0:
            ret = self.fetch_issue()
            if ret:
                break
            else:
                retry = retry - 1
            time.sleep(30)

        if retry <= 0:
            self.lottery.gotIssueNum = 0
        self.upload_issue()
        pass
