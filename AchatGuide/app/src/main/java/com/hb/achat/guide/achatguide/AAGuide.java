package com.hb.achat.guide.achatguide;

import android.accessibilityservice.AccessibilityService;
import android.content.Intent;
import android.os.Handler;
import android.util.Log;
import android.view.accessibility.AccessibilityEvent;
import android.view.accessibility.AccessibilityNodeInfo;
import android.widget.Toast;

public class AAGuide extends AccessibilityService {
    private String mGroupName = Tools.GROUP_NAME;
    private WebReport mWebReport;

    public AAGuide() {
    }

    @Override
    public void onAccessibilityEvent(AccessibilityEvent event) {
        int eventType = event.getEventType();
        //String msg = "";
        switch (eventType) {
            case AccessibilityEvent.TYPE_WINDOW_CONTENT_CHANGED:
                onAchatActivated();
                break;
            default:
                break;
        }
    }

    @Override
    public void onServiceConnected() {
        super.onServiceConnected();
        showToastMessage("向导已连接，正在对初始化");
        // start up web report thread
        WebReport.ThreadParameter tp = new WebReport.ThreadParameter();
        tp.mHandle = mHandle;
        tp.mContext = getApplicationContext();
        mWebReport = new WebReport(tp);
        mWebReport.start();
    }

    @Override
    public void onInterrupt() {
        mWebReport.stop();
        mWebReport = null;
        showToastMessage("我要被终结啦！！！");
    }

    @Override
    public boolean onUnbind(Intent intent) {
        mWebReport.stop();
        mWebReport = null;
        showToastMessage("向导被关闭");
        return super.onUnbind(intent);
    }

    private void showToastMessage(String msg) {
        Toast.makeText(this, msg, Toast.LENGTH_SHORT).show();
    }

    private void onAchatActivated() {
        //Log.d("AASERVICE", "=============== AAGuide, achat actived ==============");
        AccessibilityNodeInfo rootNode = getRootInActiveWindow();
        if (AchatLayout.isTargetGroupByName(rootNode, mGroupName)) {
            //Log.d("AASERVICE", "AAService, DOES IN achat");
            if (mWebReport.mPauseFlag) {
                mWebReport.mPauseFlag = false;
            }
        } else {
            //Log.d("AASERVICE", "AAService, NOT IN achat");
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

    static class WebReportHandler extends Handler {
        private AAGuide pthis;

        WebReportHandler(AAGuide obj) {
            pthis = obj;
        }

        @Override
        public void handleMessage(android.os.Message threadMsg) {
            String text = "";
            switch (threadMsg.what) {
                case Schedule.STEP_CLASS:
                    text = pthis.mWebReport.getSendText();
                    if (!text.isEmpty()) {
                        pthis.sendChatMessage(text);
                    }
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
