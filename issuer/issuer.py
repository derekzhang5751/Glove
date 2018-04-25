# -*- coding: UTF-8 -*-
#
import sys, time
from config import *
from model import Lottery
from webinit import WebInit
from webissue import WebIssue


def get_active_time():
    active = False
    cur_time = time.localtime()
    yu = (cur_time.tm_min - DELAY_MINS) % 5
    if yu == 0:
        active = True
    s_time = time.strftime("[%Y-%m-%d %H:%M:%S]", cur_time)
    return active, s_time


running = True
lottery = Lottery()


def init_issuer():
    retry = 2
    while retry > 0:
        print "Init issuer [{}] ...".format(retry)
        web_init = WebInit(lottery)
        # web_init.start()
        # web_init.join()
        web_init.launch()
        if lottery.initialized:
            break
        else:
            retry = retry - 1
        time.sleep(2)

    if retry > 0:
        print("Init issuer success")
        print("Last issue num [{}], time [{}]".format(lottery.lastIssueNum, lottery.lastIssueTime))
        print("Next issue num [{}], time [{}]".format(lottery.nextIssueNum, lottery.nextIssueTime))
    else:
        print("Init issuer failed, program exit.")
        sys.exit(0)
    pass


def issue_report(s_time):
    print "\n{} Issuing ...".format(s_time)
    web_issue = WebIssue(lottery)
    web_issue.launch()
    print("Next issue num [{}], time [{}]".format(lottery.nextIssueNum, lottery.nextIssueTime))
    pass


if __name__ == '__main__':
    reload(sys)
    sys.setdefaultencoding("utf-8")

    init_issuer()

    print("Issue program has started up")
    while running:
        try:
            launch, str_time = get_active_time()
            if launch:
                issue_report(str_time)
            # else:
            #    print(str_time + " Issuer is running ...")
            time.sleep(60)
        except KeyboardInterrupt:
            print("Issuer is exiting ...")
            running = False

    print("Issue program exit.")
    pass
