package com.hb.achat.achatassistant;

import android.accessibilityservice.AccessibilityService;
import android.arch.persistence.room.Room;
import android.content.Intent;
import android.os.Handler;
import android.util.Log;
import android.view.accessibility.AccessibilityEvent;
import android.view.accessibility.AccessibilityNodeInfo;
import android.widget.Toast;

import java.util.ArrayList;
import java.util.List;

public class AAService extends AccessibilityService {
    private String mGroupName = Tools.GROUP_NAME;
    public List<Message> mMessageList = new ArrayList<>();
    public boolean mMessageListReady = false;
    public AchatDao mAchatDao;

    private AchatDatabase mAppDb;
    private WebReport mWebReport;
    private DbHelper  mDbHelper;

    public AAService() {
    }

    @Override
    public void onAccessibilityEvent(AccessibilityEvent event) {
        int eventType = event.getEventType();
        //String msg = "";
        switch (eventType) {
            case AccessibilityEvent.TYPE_VIEW_SCROLLED:
                //if (mMessageListReady) {
                //    refreshMessageInGroup();
                //}
                break;
            case AccessibilityEvent.TYPE_WINDOW_CONTENT_CHANGED:
                if (mMessageListReady) {
                    refreshMessageInGroup();
                }
                break;
            //case AccessibilityEvent.TYPE_WINDOW_STATE_CHANGED:
            //    msg = "TYPE_WINDOW_STATE_CHANGED " + event.getClassName().toString();
            //    showToastMessage(msg);
            //    break;
            //case AccessibilityEvent.TYPE_WINDOWS_CHANGED:
            //    msg = "TYPE_WINDOWS_CHANGED " + event.getClassName().toString();
            //    showToastMessage(msg);
            //    break;
            default:
                break;
        }
    }

    @Override
    public void onServiceConnected() {
        super.onServiceConnected();
        showToastMessage("服务已连接，正在对初始化");
        if (mAppDb == null) {
            mAppDb = Room.databaseBuilder(getApplicationContext(), AchatDatabase.class, "achat.db").build();
            mAchatDao = mAppDb.getAchatDao();
        }
        initMessageList();
        // start up web report thread
        WebReport.ThreadParameter tp = new WebReport.ThreadParameter();
        tp.mAchatDao = mAchatDao;
        tp.mHandle = mHandle;
        tp.mContext = getApplicationContext();
        mWebReport = new WebReport(tp);
        mWebReport.start();
    }

    @Override
    public void onInterrupt() {
        mWebReport.stop();
        mWebReport = null;
        mDbHelper.stop();
        mDbHelper = null;
        if (mAppDb != null) {
            mAchatDao = null;
            mAppDb.close();
            mAppDb = null;
        }
        showToastMessage("我要被终结啦！！！");
    }

    @Override
    public boolean onUnbind(Intent intent) {
        mWebReport.stop();
        mWebReport = null;
        mDbHelper.stop();
        mDbHelper = null;
        if (mAppDb != null) {
            mAchatDao = null;
            mAppDb.close();
            mAppDb = null;
        }
        showToastMessage("服务被关闭");
        return super.onUnbind(intent);
    }

    private void showToastMessage(String msg) {
        Toast.makeText(this, msg, Toast.LENGTH_SHORT).show();
    }

    private void refreshMessageInGroup() {
        Log.d("AASERVICE", "=============== AAService, refresh message ==============");
        AccessibilityNodeInfo rootNode = getRootInActiveWindow();
        if (AchatLayout.isTargetGroupByName(rootNode, mGroupName)) {
            Log.d("AASERVICE", "AAService, DOES IN achat");
            if (mWebReport.mPauseFlag) {
                mWebReport.mPauseFlag = false;
            }
            //showToastMessage("已打开服务页面");
            List<Message> msgList = new ArrayList<>();
            AchatLayout.fetchMessageList(rootNode, msgList);

            if (msgList.size() > 0) {
                //Log.d("AASERVICE", "AAService, get message list ok");
                for (int j=0; j<msgList.size(); j++) {
                    Message mm = msgList.get(j);
                    //Log.d("AASERVICE", "AAService, get message: " + mm.fromUserNick + " -> " + mm.content);
                }
                List<Message> newList = new ArrayList<>();
                Tools.getNewMessageList(msgList, mMessageList, newList);

                int len = newList.size();
                //if (len <= 0) {
                //    Log.d("AASERVICE", "AAService, new message list is empty");
                //}

                String str = "";
                for (int i=0; i<len; i++) {
                    Message msg = newList.get(i);
                    str = str + "[" + msg.fromUserRemark + "] says [" + msg.content + "]\n";
                    Log.d("AASERVICE", "AAService, new message: " + msg.content);
                }
                if (!str.isEmpty()) {
                    showToastMessage(str);
                }
            } else {
                //Log.d("AASERVICE", "AAService, get message list failed");
            }
        } else {
            Log.d("AASERVICE", "AAService, NOT IN achat");
            if (!mWebReport.mPauseFlag) {
                mWebReport.mPauseFlag = true;
            }
        }
    }

    private void sendChatMessage(String msg) {
        //Log.d("AASERVICE", "AAService, send message: " + msg);
        AccessibilityNodeInfo rootNode = getRootInActiveWindow();
        if (AchatLayout.pasteChatMessage(rootNode, msg)) {
            AchatLayout.clickSendButton(rootNode);
        } else {
            //Log.d("AASERVICE", "AAService, find input failed");
        }
    }

    private boolean sendIssuePicture() {
        boolean success = false;
        AccessibilityNodeInfo rootNode = getRootInActiveWindow();
        if (!AchatLayout.clickSendMultiMedia(rootNode)) {
            return false;
        }
        // Click Gallery Icon
        success = false;
        for (int i=0; i<8; i++) {
            Tools.sleep(3500);
            rootNode = getRootInActiveWindow();
            if (AchatLayout.clickGalleryIcon(rootNode)) {
                success = true;
                break;
            }
        }
        if (!success) {
            return false;
        }
        // Click First Image
        success = false;
        for (int i=0; i<8; i++) {
            Tools.sleep(2000);
            rootNode = getRootInActiveWindow();
            if (AchatLayout.clickFirstImageChecked(rootNode)) {
                success = true;
                break;
            }
        }
        if (!success) {
            return false;
        }
        // Click Send Image Button
        success = false;
        for (int i=0; i<8; i++) {
            Tools.sleep(2000);
            rootNode = getRootInActiveWindow();
            if (AchatLayout.clickSendImageBtn(rootNode)) {
                success = true;
                break;
            }
        }
        if (!success) {
            return false;
        }

        return true;
    }

    private void initMessageList() {
        mMessageListReady = false;
        mDbHelper = new DbHelper(this);
        mDbHelper.start();
    }

    static class WebReportHandler extends Handler {
        private AAService pthis;

        WebReportHandler(AAService obj) {
            pthis = obj;
        }

        @Override
        public void handleMessage(android.os.Message threadMsg) {
            String text = "";
            switch (threadMsg.what) {
                case Schedule.STEP_LAST_TERM:
                    text = pthis.mWebReport.getSendText();
                    if (!text.isEmpty()) {
                        pthis.sendChatMessage(text);
                    }
                    if (threadMsg.arg1 == 1) {
                        // Send image
                        Tools.sleep(300);
                        pthis.sendIssuePicture();
                    }
                    break;
                case Schedule.STEP_WELCOME:
                case Schedule.STEP_END_TIP:
                case Schedule.STEP_END:
                case Schedule.STEP_CHECK:
                case Schedule.STEP_ISSUE:
                case Schedule.STEP_TURN:
                    text = pthis.mWebReport.getSendText();
                    if (!text.isEmpty()) {
                        pthis.sendChatMessage(text);
                    }
                    break;
                case Schedule.STEP_CLASS:
                    text = pthis.mWebReport.getSendText();
                    if (!text.isEmpty()) {
                        pthis.sendChatMessage(text);
                    }
                    break;
                case DbHelper.MSG_INIT_DONE:
                    pthis.mMessageListReady = true;
                    pthis.showToastMessage("服务初始化完成");
                    break;
                case Schedule.STEP_INIT_ENV:
                    pthis.showToastMessage("同步时间差: " + Integer.toString(threadMsg.arg1) + " 秒");
                    break;
                default:
                    break;
            }
            super.handleMessage(threadMsg);
        }
    }
    public Handler mHandle = new WebReportHandler(this);
}
