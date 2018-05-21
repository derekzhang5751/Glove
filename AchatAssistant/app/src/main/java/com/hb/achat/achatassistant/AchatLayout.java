package com.hb.achat.achatassistant;

import android.os.Bundle;
import android.text.TextUtils;
import android.util.Log;
import android.view.accessibility.AccessibilityNodeInfo;

import java.util.List;

public class AchatLayout {

    public static boolean isTargetGroupByName(AccessibilityNodeInfo node, String targetGroupName) {
        String groupName = "";

        for (int i=0; i<node.getChildCount(); i++) {
            AccessibilityNodeInfo subNode = node.getChild(i);
            if (subNode == null) {
                continue;
            }
            if ("android.widget.TextView".equals(subNode.getClassName().toString())) {
                if (!TextUtils.isEmpty(subNode.getText())) {
                    groupName = subNode.getText().toString();
                    if (groupName.contains(targetGroupName)) {
                        //String str = "TextView: " + groupName;
                        //showToastMessage(str);
                        return true;
                    }
                }
            }

            if (isTargetGroupByName(subNode, targetGroupName)) {
                return true;
            }
        }
        return false;
    }

    public static void findMessageItem(AccessibilityNodeInfo node, Message msg) {
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

    public static void fetchMessageList(AccessibilityNodeInfo node, List<Message> msgList) {
        String viewId = "com.tencent.mm:id/p";  // "com.tencent.mm:id/if";
        List<AccessibilityNodeInfo> msgNodeList = node.findAccessibilityNodeInfosByViewId(viewId);
        if (msgNodeList == null) {
            //showToastMessage("Message list is null");
        } else {
            int len = msgNodeList.size();
            //Log.d("AASERVICE", "fetchMessageList: Message list size is " + Integer.toString(len));
            for (int i=0; i<len; i++) {
                AccessibilityNodeInfo subNode = msgNodeList.get(i);
                if (subNode != null) {
                    Message msg = new Message();
                    findMessageItem(subNode, msg);
                    if (!TextUtils.isEmpty(msg.fromUserNick) && !TextUtils.isEmpty(msg.content)) {
                        //Log.d("AASERVICE", "fetchMessageList: " + msg.fromUserNick + ": " + msg.content);
                        msg.id = 0;
                        msg.status = -1;
                        msg.toUserNick = "";
                        msg.reply = "";
                        msg.recvTime = Tools.getCurDateTimeFormatted();
                        msgList.add(msg);
                    }
                }
            }
        }
    }

    public static boolean pasteChatMessage(AccessibilityNodeInfo rootNode, String msg) {
        if (rootNode == null) {
            return false;
        }
        String viewId = "com.tencent.mm:id/a2v";
        List<AccessibilityNodeInfo> editNodeList = rootNode.findAccessibilityNodeInfosByViewId(viewId);
        if (editNodeList == null) {
            Log.d("AASERVICE", "AchatLayout, Not found input control !");
            return false;
        } else {
            int len = editNodeList.size();
            if (len > 0) {
                AccessibilityNodeInfo editNode = editNodeList.get(0);
                if (editNode != null) {
                    Bundle arguments = new Bundle();
                    arguments.putCharSequence(AccessibilityNodeInfo.ACTION_ARGUMENT_SET_TEXT_CHARSEQUENCE, msg);
                    editNode.performAction(AccessibilityNodeInfo.ACTION_SET_TEXT, arguments);
                    return true;
                } else {
                    Log.d("AASERVICE", "AchatLayout, the first input control is null");
                }
            } else {
                Log.d("AASERVICE", "AchatLayout, Input control is empty");
            }
        }
        return false;
    }

    public static boolean clickSendButton(AccessibilityNodeInfo rootNode) {
        if (rootNode == null) {
            return false;
        }
        String viewId = "com.tencent.mm:id/a31";
        List<AccessibilityNodeInfo> btnNodeList = rootNode.findAccessibilityNodeInfosByViewId(viewId);
        if (btnNodeList == null) {
            Log.d("AASERVICE", "AchatLayout, Not found send button !");
            return false;
        } else {
            int len = btnNodeList.size();
            if (len > 0) {
                AccessibilityNodeInfo btnNode = btnNodeList.get(0);
                if (btnNode != null) {
                    btnNode.performAction(AccessibilityNodeInfo.ACTION_CLICK);
                    return true;
                }
            }
        }
        return false;
    }

}