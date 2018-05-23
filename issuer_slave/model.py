# -*- coding: UTF-8 -*-
#
# Data Model


class Lottery(object):
    initialized = False
    curLotteryType = 0
    lastIssueNum = 0
    lastIssueTime = ""
    nextIssueNum = 0
    nextIssueTime = ""

    gotIssueNum = 0
    gotIssueTime = ""
    ranking = [0]*10
