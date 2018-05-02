# -*- coding: UTF-8 -*-
#
# Schedule Object
import time


STEP_CLASS = 0
STEP_BREAK = 1
STEP_WELCOME = 2
STEP_BEGIN = 3
STEP_END_TIP = 4
STEP_END = 5
STEP_CHECK = 6
STEP_ISSUE = 7

LOTTERY_PK10 = 0
LOTTERY_XYFT = 1


class Schedule(object):
    # private variables
    __step = STEP_CLASS
    __lottery = LOTTERY_PK10
    __cur_time = ''
    __tip_msg = ''
    __next_wait = 60

    # construction function
    def __init__(self, it):
        self.__cur_time = time.strftime("%H:%M:%S", time.localtime())

        if self.__cur_time >= '04:00:00' and self.__cur_time < '09:00:00':
            self.__step = STEP_BREAK
            self.__tip_msg = ''
        else:
            short = self.__cur_time[4:]
            if short >= '0:00' and short < '5:00':
                self.__step = STEP_BEGIN
                pass
            else:
                self.__step = STEP_CLASS
                pass
        pass

    # private functions

    # public functions
    def get_step(self):
        return self.__step

    def get_tip_msg(self):
        return self.__tip_msg

    def get_next_wait(self):
        return self.__next_wait

    pass
