# -*- coding: UTF-8 -*-
#
import sys, time
import urllib3
from model import Lottery
from webinit import WebInit
from webissue import WebIssue


def get_active_time():
    active = False
    wait = 10
    cur_time = time.localtime()
    s_time = time.strftime("[%Y-%m-%d %H:%M:%S]", cur_time)

    s_tmp = s_time[12:20]
    if s_tmp < "09:00:00" and s_tmp > "04:07:00":
        active = False
    else:
        min = s_time[16:17]
        sec = s_time[18:20]
        i = int(min)
        if i >= 5:
            i = i - 5
        short = "{}:{}".format(i, sec)
        if lottery.curLotteryType == 1:
            begin = "0:00"
            end = "1:00"
        else:
            begin = "3:00"
            end = "4:00"

        if short >= begin and short < end:
            active = True
            wait = 60
        else:
            wait = 2
    return active, s_time, wait


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

    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

    init_issuer()

    print("Issue program has started up")
    while running:
        try:
            launch, str_time, wait = get_active_time()
            if launch:
                issue_report(str_time)
            # else:
            #    print(str_time + " Issuer is running ...")
            time.sleep(wait)
        except KeyboardInterrupt:
            print("Issuer is exiting ...")
            running = False

    print("Issue program exit.")
    pass
