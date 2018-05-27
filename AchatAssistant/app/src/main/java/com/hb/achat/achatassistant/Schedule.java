package com.hb.achat.achatassistant;


import android.util.Log;

public class Schedule {
    public static final int STEP_NULL = -1;
    public static final int STEP_INIT_ENV = 0;
    public static final int STEP_BREAK = 1;
    public static final int STEP_LAST_TERM = 2;
    public static final int STEP_WELCOME = 3;
    public static final int STEP_CLASS = 4;
    public static final int STEP_END_TIP = 5;
    public static final int STEP_END = 6;
    public static final int STEP_CHECK = 7;
    public static final int STEP_ISSUE = 8;

    public static final int LOTTERY_PK10 = 0;
    public static final int LOTTERY_XYFT = 1;

    private int mStep = STEP_NULL;
    private int mLottery = LOTTERY_PK10;
    private String mCurTime = "";
    private int mNextWait = 10;

    private String getShortTime() {
        String min = mCurTime.substring(4, 5);
        String sec = mCurTime.substring(6, 8);
        int i = Integer.parseInt(min);
        if (i > 5) {
            i = i - 5;
        }
        return String.format("%d:%s", i, sec);
    }

    public int next() {
        mCurTime = Tools.getCurTimeFormatted();
        //Log.d("AASERVICE", "Schedule, current time: " + mCurTime);
        if (mStep == STEP_ISSUE) {
            mStep = STEP_LAST_TERM;
        }

        if (mStep == STEP_NULL) {
            mStep = mStep + 1;
            mNextWait = 2;
            return mStep;
        }

        if (mCurTime.compareTo("04:00:00") >= 0 && mCurTime.compareTo("09:00:00") < 0) {
            mStep = STEP_BREAK;
            mNextWait = 10;
        } else {
            String shortTime = getShortTime();
            //Log.d("AASERVICE", "Schedule, short time: " + shortTime);
            int step = 0;

            if (mCurTime.compareTo("00:05:00") >= 0 && mCurTime.compareTo("04:05:00") < 0) {
                mLottery = LOTTERY_XYFT;
                if (shortTime.compareTo("0:00") >= 0 && shortTime.compareTo("0:30") < 0) {
                    step = STEP_LAST_TERM;
                } else if (shortTime.compareTo("0:30") >= 0 && shortTime.compareTo("0:40") < 0) {
                    step = STEP_WELCOME;
                } else if (shortTime.compareTo("0:40") >= 0 && shortTime.compareTo("3:00") < 0) {
                    step = STEP_CLASS;
                } else if (shortTime.compareTo("3:00") >= 0 && shortTime.compareTo("4:00") < 0) {
                    step = STEP_END_TIP;
                } else if (shortTime.compareTo("4:00") >= 0 && shortTime.compareTo("4:05") < 0) {
                    step = STEP_END;
                } else if (shortTime.compareTo("4:05") >= 0 && shortTime.compareTo("4:55") < 0) {
                    step = STEP_CHECK;
                } else if (shortTime.compareTo("4:55") >= 0 && shortTime.compareTo("5:00") < 0) {
                    step = STEP_ISSUE;
                } else {
                    step = STEP_CLASS;
                }
            } else {
                mLottery = LOTTERY_PK10;
                if (shortTime.compareTo("3:00") >= 0 && shortTime.compareTo("3:30") < 0) {
                    step = STEP_LAST_TERM;
                } else if (shortTime.compareTo("3:30") >= 0 && shortTime.compareTo("3:40") < 0) {
                    step = STEP_WELCOME;
                } else if (shortTime.compareTo("3:40") >= 0) {
                    step = STEP_CLASS;
                } else if (shortTime.compareTo("1:00") >= 0 && shortTime.compareTo("2:00") < 0) {
                    step = STEP_END_TIP;
                } else if (shortTime.compareTo("2:00") >= 0 && shortTime.compareTo("2:05") < 0) {
                    step = STEP_END;
                } else if (shortTime.compareTo("2:05") >= 0 && shortTime.compareTo("2:55") < 0) {
                    step = STEP_CHECK;
                } else if (shortTime.compareTo("2:55") >= 0 && shortTime.compareTo("3:00") < 0) {
                    step = STEP_ISSUE;
                } else {
                    step = STEP_CLASS;
                }
            }

            if (mStep < step) {
                mStep = mStep + 1;
            }

            switch (mStep) {
                case STEP_LAST_TERM:
                    mNextWait = 1;
                    break;
                case STEP_WELCOME:
                    mNextWait = 1;
                    break;
                case STEP_END_TIP:
                    mNextWait = 1;
                    break;
                case STEP_END:
                    mNextWait = 2;
                    break;
                case STEP_CHECK:
                    mNextWait = 2;
                    break;
                case STEP_ISSUE:
                    mNextWait = 5;
                    break;
                default:
                    mNextWait = 1;
                    break;
            }
        }
        return mStep;
    }

    public int getStep() {
        return mStep;
    }

    public int getNextWait() {
        return mNextWait;
    }

}
