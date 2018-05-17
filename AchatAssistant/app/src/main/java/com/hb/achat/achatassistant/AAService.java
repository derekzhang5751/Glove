package com.hb.achat.achatassistant;

import android.accessibilityservice.AccessibilityService;
import android.arch.persistence.room.Room;
import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.accessibility.AccessibilityEvent;
import android.view.accessibility.AccessibilityNodeInfo;
import android.widget.Toast;

import java.util.ArrayList;
import java.util.List;

public class AAService extends AccessibilityService {
    private String mGroupName = "内部群2";
    private List<Message> mMessageList = new ArrayList<>();
    private int mIndex = 0;
    private AchatDatabase mAppDb = null;

    public AAService() {
    }

    @Override
    public void onAccessibilityEvent(AccessibilityEvent event) {
        int eventType = event.getEventType();
        String msg = "";
        switch (eventType) {
            case AccessibilityEvent.TYPE_WINDOW_CONTENT_CHANGED:
                refreshMessageInGroup();
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
    public void onInterrupt() {
        showToastMessage("我要被终结啦！！！");
    }

    @Override
    public void onServiceConnected() {
        super.onServiceConnected();
        showToastMessage("服务已连接");
        if (mAppDb == null) {
            mAppDb = Room.databaseBuilder(getApplicationContext(), AchatDatabase.class, "achat.db").build();
        }
    }

    @Override
    public boolean onUnbind(Intent intent) {
        showToastMessage("服务被关闭");
        return super.onUnbind(intent);
    }

    private void showToastMessage(String msg) {
        Toast.makeText(this, msg, Toast.LENGTH_SHORT).show();
    }

    private void refreshMessageInGroup() {
        AccessibilityNodeInfo rootNode = getRootInActiveWindow();
        if (isTargetGroupByName(rootNode)) {
            //showToastMessage("已打开服务页面");
            mMessageList.clear();
            getMessageList(rootNode);
            String str = "";
            for (int i=0; i<mMessageList.size(); i++) {
                Message msg = mMessageList.get(i);
                str = str + "[" + msg.fromUserRemark + "] says [" + msg.content + "]\n";
            }
            showToastMessage(str);

            /*mIndex++;
            if (mIndex < 1) {
                String msg = "测试消息 " + Integer.toString(mIndex);
                sendChatMessage(msg);
            }*/
        }
    }

    private boolean isTargetGroupByName(AccessibilityNodeInfo node) {
        String groupName = "";

        for (int i=0; i<node.getChildCount(); i++) {
            AccessibilityNodeInfo subNode = node.getChild(i);
            if (subNode == null) {
                continue;
            }
            if ("android.widget.TextView".equals(subNode.getClassName().toString())) {
                if (!TextUtils.isEmpty(subNode.getText())) {
                    groupName = subNode.getText().toString();
                    if (groupName.contains(mGroupName)) {
                        //String str = "TextView: " + groupName;
                        //showToastMessage(str);
                        return true;
                    }
                }
            }

            if (isTargetGroupByName(subNode)) {
                return true;
            }
        }
        return false;
    }

    private void getMessageList(AccessibilityNodeInfo node) {
        String viewId = "com.tencent.mm:id/p";  // "com.tencent.mm:id/if";
        List<AccessibilityNodeInfo> msgNodeList = node.findAccessibilityNodeInfosByViewId(viewId);
        if (msgNodeList == null) {
            showToastMessage("Message list is null");
        } else {
            int len = msgNodeList.size();
            //showToastMessage("Message list size is " + Integer.toString(len));
            for (int i=0; i<len; i++) {
                AccessibilityNodeInfo subNode = msgNodeList.get(i);
                if (subNode != null) {
                    Message msg = new Message();
                    findMessageItem(subNode, msg);
                    if (!msg.fromUserNick.isEmpty() && !msg.content.isEmpty()) {
                        //showToastMessage( msg.fromUserNick + ": " + msg.content);
                        mMessageList.add(msg);
                    }
                }
            }
        }
    }

    private void findMessageItem(AccessibilityNodeInfo node, Message msg) {
        String tmpName = "";

        for (int i=0; i<node.getChildCount(); i++) {
            AccessibilityNodeInfo subNode = node.getChild(i);
            if (subNode == null) {
                continue;
            }
            if ("android.widget.TextView".equals(subNode.getClassName().toString())) {
                if (subNode.isLongClickable()) {
                    // This is message content
                    if (TextUtils.isEmpty(subNode.getText())) {
                        // Empty message
                        continue;
                    } else {
                        msg.content = subNode.getText().toString();
                    }
                } else {
                    // This is nick or remark name
                    if (TextUtils.isEmpty(subNode.getText())) {
                        msg.fromUserNick = "";
                        msg.fromUserRemark = "";
                    } else {
                        msg.fromUserNick = subNode.getText().toString().trim();
                        msg.fromUserRemark = msg.fromUserNick;
                    }
                }
            }/* else if ("android.widget.ImageView".equals(subNode.getClassName().toString())) {
                if (subNode.isLongClickable() && !TextUtils.isEmpty(subNode.getContentDescription())) {
                    // This is head photo
                    tmpName = subNode.getContentDescription().toString();
                    if (tmpName.contains("头像")) {
                        tmpName = tmpName.replace("头像", "");
                        tmpName = tmpName.trim();
                        if (msg.fromUserNick.isEmpty()) {
                            msg.fromUserNick = tmpName;
                            msg.fromUserRemark = msg.fromUserNick;
                        }
                    }
                }
            }*/

            findMessageItem(subNode, msg);
        }
    }

    private void sendChatMessage(String msg) {
        AccessibilityNodeInfo rootNode = getRootInActiveWindow();
        String viewId = "com.tencent.mm:id/a2v";
        List<AccessibilityNodeInfo> editNodeList = rootNode.findAccessibilityNodeInfosByViewId(viewId);
        if (editNodeList == null) {
            showToastMessage("Not found input control !");
        } else {
            int len = editNodeList.size();
            if (len > 0) {
                AccessibilityNodeInfo editNode = editNodeList.get(0);
                if (editNode != null) {
                    Bundle arguments = new Bundle();
                    arguments.putCharSequence(AccessibilityNodeInfo.ACTION_ARGUMENT_SET_TEXT_CHARSEQUENCE, msg);
                    editNode.performAction(AccessibilityNodeInfo.ACTION_SET_TEXT, arguments);
                    clickSendButton();
                }
            }
        }
    }

    private void clickSendButton() {
        AccessibilityNodeInfo rootNode = getRootInActiveWindow();
        String viewId = "com.tencent.mm:id/a31";
        List<AccessibilityNodeInfo> btnNodeList = rootNode.findAccessibilityNodeInfosByViewId(viewId);
        if (btnNodeList == null) {
            showToastMessage("Not found send button !");
        } else {
            int len = btnNodeList.size();
            if (len > 0) {
                AccessibilityNodeInfo btnNode = btnNodeList.get(0);
                if (btnNode != null) {
                    btnNode.performAction(AccessibilityNodeInfo.ACTION_CLICK);
                }
            }
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
}
