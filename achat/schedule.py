# -*- coding: UTF-8 -*-
#
# Schedule Object
import time


STEP_NULL = -1
STEP_INIT_ENV = 0
STEP_BREAK = 1
STEP_LAST_TERM = 2
STEP_WELCOME = 3
STEP_CLASS = 4
STEP_END_TIP = 5
STEP_END = 6
STEP_CHECK = 7
STEP_ISSUE = 8

LOTTERY_PK10 = 0
LOTTERY_XYFT = 1


class Schedule(object):
    # private variables
    __step = STEP_NULL
    __lottery = LOTTERY_PK10
    __cur_time = ''
    __next_wait = 10

    # construction function
    def __init__(self):
        pass

    # private functions
    def get_short_time(self):
        min = self.__cur_time[4:5]
        sec = self.__cur_time[6:8]
        i = int(min)
        if i > 5:
            i = i - 5
        short = "{}:{}".format(i, sec)
        return short

    # public functions
    def next(self):
        self.__cur_time = time.strftime("%H:%M:%S", time.localtime())

        if self.__step == STEP_ISSUE:
            self.__step = STEP_LAST_TERM

        if self.__step == STEP_NULL:
            self.__step = self.__step + 1
            self.__next_wait = 2
            return self.__step

        if self.__cur_time >= '04:00:00' and self.__cur_time < '09:00:00':
            self.__step = STEP_BREAK
            self.__next_wait = 10
        else:
            if self.__cur_time >= '09:00:00' and self.__cur_time < '24:00:00':
                self.__lottery = LOTTERY_PK10
            else:
                self.__lottery = LOTTERY_XYFT

            short = self.get_short_time()

            if short >= '0:00' and short < '1:00':
                step = STEP_LAST_TERM
            elif short >= '1:00' and short < '1:05':
                step = STEP_WELCOME
            elif short >= '3:00' and short < '4:00':
                step = STEP_END_TIP
            elif short >= '4:00' and short < '4:05':
                step = STEP_END
            elif short >= '4:05' and short < '4:55':
                step = STEP_CHECK
            elif short >= '4:55' and short < '5:00':
                step = STEP_ISSUE
            else:
                step = STEP_CLASS

            if self.__step < step:
                self.__step = self.__step + 1

            if self.__step == STEP_LAST_TERM:
                self.__next_wait = 1
            elif self.__step == STEP_WELCOME:
                self.__next_wait = 1
            elif self.__step == STEP_END_TIP:
                self.__next_wait = 1
            elif self.__step == STEP_END:
                self.__next_wait = 2
            elif self.__step == STEP_CHECK:
                self.__next_wait = 5
            elif self.__step == STEP_ISSUE:
                self.__next_wait = 1
            else:
                self.__next_wait = 1
            pass
        return self.__step

    def get_step(self):
        return self.__step

    def get_next_wait(self):
        return self.__next_wait

    pass
