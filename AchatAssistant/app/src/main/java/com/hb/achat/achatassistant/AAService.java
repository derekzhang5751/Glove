package com.hb.achat.achatassistant;

import android.accessibilityservice.AccessibilityService;
import android.arch.persistence.room.Room;
import android.content.Intent;
import android.os.Handler;
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
            case AccessibilityEvent.TYPE_WINDOW_CONTENT_CHANGED:
                if (mMessageListReady) {
                    refreshMessageInGroup();
                }
                //msg = "TYPE_WINDOW_CONTENT_CHANGED " + event.getClassName().toString();
                //showToastMessage(msg);
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
        AccessibilityNodeInfo rootNode = getRootInActiveWindow();
        if (AchatLayout.isTargetGroupByName(rootNode, mGroupName)) {
            //showToastMessage("已打开服务页面");
            List<Message> msgList = new ArrayList<>();
            AchatLayout.fetchMessageList(rootNode, msgList);

            if (msgList.size() > 0) {
                List<Message> newList = new ArrayList<>();
                Tools.getNewMessageList(msgList, mMessageList, newList);

                String str = "";
                for (int i=0; i<newList.size(); i++) {
                    Message msg = newList.get(i);
                    str = str + "[" + msg.fromUserRemark + "] says [" + msg.content + "]\n";
                }
                if (!str.isEmpty()) {
                    showToastMessage(str);
                }
            }

            /*mIndex++;
            if (mIndex < 1) {
                String msg = "测试消息 " + Integer.toString(mIndex);
                sendChatMessage(msg);
            }*/
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

    private void setAccessibilityInfo() {
        /*String[] packageNames = {"com.tencent.mm"};
        AccessibilityServiceInfo asi = new AccessibilityServiceInfo();

        asi.eventTypes = AccessibilityEvent.TYPE_WINDOW_STATE_CHANGED
            | AccessibilityEvent.TYPE_WINDOW_CONTENT_CHANGED
            | AccessibilityEvent.TYPE_WINDOWS_CHANGED;
        asi.feedbackType = AccessibilityServiceInfo.FEEDBACK_GENERIC;
        asi.notificationTimeout = 100;
        asi.packageNames = packageNames;

        setServiceInfo(asi);*/
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
                case Schedule.STEP_WELCOME:
                case Schedule.STEP_END_TIP:
                case Schedule.STEP_END:
                case Schedule.STEP_CHECK:
                case Schedule.STEP_ISSUE:
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
                    //
                case DbHelper.MSG_INIT_DONE:
                    pthis.mMessageListReady = true;
                    pthis.showToastMessage("服务初始化完成");
                    break;
                default:
                    break;
            }
            super.handleMessage(threadMsg);
        }
    }
    public Handler mHandle = new WebReportHandler(this);
}