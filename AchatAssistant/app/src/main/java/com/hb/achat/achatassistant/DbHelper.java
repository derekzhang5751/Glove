package com.hb.achat.achatassistant;

import android.util.Log;


public class DbHelper implements Runnable {
    public static final int MSG_INIT_DONE = 100;
    public static final int MSG_UPDATE_DONE = 101;

    private boolean mExitFlag;
    private Thread mThread;
    private String mThreadName;
    private AAService mService;

    DbHelper(AAService service) {
        mExitFlag = false;
        mThread = null;
        mThreadName = "DbHelperThread";
        mService = service;
    }

    @Override
    public void run() {
        Log.d("AASERVICE", "DbHelperThread start");
        initMessageList();

        while (!mExitFlag) {
            try {
                Thread.sleep(1000);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            }
            saveNewMessage();
        }
        Log.d("AASERVICE", "DbHelperThread exit");
    }

    public void start() {
        if (mThread == null) {
            mThread = new Thread(this, mThreadName);
            mThread.start();
        }
    }

    public void stop() {
        mExitFlag = true;
        if (mThread != null) {
            try {
                mThread.join(5000);
            } catch (InterruptedException ex) {
                mThread.interrupt();
            }
            if (mThread.isAlive()) {
                mThread.interrupt();
            }
        }
        mThread = null;
    }

    private void initMessageList() {
        mService.mMessageList.clear();
        Message[] arrayMsg = mService.mAchatDao.selectMessageLatest(5);
        Log.d("AASERVICE", "Init message list, size " + Integer.toString(arrayMsg.length));
        for (int i=arrayMsg.length-1; i>=0; i--) {
            Message msg = arrayMsg[i];
            mService.mMessageList.add(msg);
            Log.d("AASERVICE", "Read Message: " + msg.fromUserRemark + " -> " + msg.content);
        }

        android.os.Message msg = new android.os.Message();
        msg.what = MSG_INIT_DONE;
        mService.mHandle.sendMessage(msg);
    }

    private void saveNewMessage() {
        int len = mService.mMessageList.size();
        for (int i=0; i<len; i++) {
            Message msg = mService.mMessageList.get(i);
            if (msg.status < 0) {
                msg.status = 0;
                long id = mService.mAchatDao.insertMessage(msg);
                Log.d("AASERVICE", "Save Message: " + Long.toString(id) + " -> " + msg.fromUserRemark + " -> " + msg.content);
            }
        }
    }
}
