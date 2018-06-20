package com.hb.achat.guide.achatguide;

import android.os.Bundle;
import android.text.TextUtils;
import android.util.Log;
import android.view.accessibility.AccessibilityNodeInfo;

import java.util.List;

public class AchatLayout {

    public static boolean isTargetGroupByName(AccessibilityNodeInfo node, String targetGroupName) {
        String groupName = "";

        if (node == null) {
            return false;
        }

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
            }/* else {
                Log.d("AASERVICE", "AchatLayout, Input control is empty");
            }*/
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

    public static boolean clickSendMultiMedia(AccessibilityNodeInfo rootNode) {
        if (rootNode == null) {
            return false;
        }
        String viewId = "com.tencent.mm:id/a30";
        List<AccessibilityNodeInfo> btnNodeList = rootNode.findAccessibilityNodeInfosByViewId(viewId);
        if (btnNodeList == null) {
            Log.d("AASERVICE", "AchatLayout, Not found Multi Media button !");
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

    public static boolean clickGalleryIcon(AccessibilityNodeInfo rootNode) {
        if (rootNode == null) {
            return false;
        }
        String viewId = "com.tencent.mm:id/kp";
        List<AccessibilityNodeInfo> gridNodeList = rootNode.findAccessibilityNodeInfosByViewId(viewId);
        if (gridNodeList == null) {
            Log.d("AASERVICE", "AchatLayout, Not found Gallery Icon !");
        } else {
            if (gridNodeList.size() > 0) {
                AccessibilityNodeInfo gridNode = gridNodeList.get(0);
                int size = gridNode.getChildCount();
                Log.d("AASERVICE", "AchatLayout, Liner Node Size: " + Integer.toString(size));
                if (size > 0) {
                    AccessibilityNodeInfo linerNode = gridNode.getChild(0);
                    if (linerNode != null) {
                        linerNode.performAction(AccessibilityNodeInfo.ACTION_CLICK);
                        Log.d("AASERVICE", "AchatLayout, Click Gallery Icon is OK !");
                        return true;
                    }
                }
            } else {
                Log.d("AASERVICE", "AchatLayout, Grid List is empty !");
            }
        }
        return false;
    }

    public static boolean clickFirstImageChecked(AccessibilityNodeInfo rootNode) {
        if (rootNode == null) {
            return false;
        }
        String viewId = "com.tencent.mm:id/c_w";
        List<AccessibilityNodeInfo> gridNodeList = rootNode.findAccessibilityNodeInfosByViewId(viewId);
        if (gridNodeList == null) {
            Log.d("AASERVICE", "AchatLayout, Not found First Image List !");
        } else {
            if (gridNodeList.size() > 0) {
                AccessibilityNodeInfo gridNode = gridNodeList.get(0);
                int size = gridNode.getChildCount();
                Log.d("AASERVICE", "AchatLayout, Relative Node Size: " + Integer.toString(size));
                if (size > 0) {
                    AccessibilityNodeInfo linerNode = gridNode.getChild(0);
                    if (linerNode != null) {
                        linerNode.performAction(AccessibilityNodeInfo.ACTION_CLICK);
                        Log.d("AASERVICE", "AchatLayout, Click First Image is OK !");
                        return true;
                    }
                }
            } else {
                Log.d("AASERVICE", "AchatLayout, First Image List is empty !");
            }
        }
        return false;
    }
    public static boolean clickFirstImageCheckedOld(AccessibilityNodeInfo rootNode) {
        if (rootNode == null) {
            return false;
        }
        String viewId = "com.tencent.mm:id/b5b";
        List<AccessibilityNodeInfo> btnNodeList = rootNode.findAccessibilityNodeInfosByViewId(viewId);
        if (btnNodeList == null) {
            Log.d("AASERVICE", "AchatLayout, Not found First Image From Gallery !");
            return false;
        } else {
            int len = btnNodeList.size();
            if (len > 0) {
                AccessibilityNodeInfo btnNode = btnNodeList.get(0);
                if (btnNode != null) {
                    btnNode.performAction(AccessibilityNodeInfo.ACTION_SELECT);
                    Log.d("AASERVICE", "AchatLayout, Click Image Check is OK !");
                    return true;
                }
            } else {
                Log.d("AASERVICE", "AchatLayout, Image List is empty !");
            }
        }
        return false;
    }

    public static boolean clickSendImageBtn(AccessibilityNodeInfo rootNode) {
        if (rootNode == null) {
            return false;
        }
        String viewId = "com.tencent.mm:id/gd";
        List<AccessibilityNodeInfo> btnNodeList = rootNode.findAccessibilityNodeInfosByViewId(viewId);
        if (btnNodeList == null) {
            Log.d("AASERVICE", "AchatLayout, Not found Image Send Button !");
            return false;
        } else {
            int len = btnNodeList.size();
            if (len > 0) {
                AccessibilityNodeInfo btnNode = btnNodeList.get(0);
                if (btnNode != null) {
                    btnNode.performAction(AccessibilityNodeInfo.ACTION_CLICK);
                    return true;
                }
            } else {
                Log.d("AASERVICE", "AchatLayout, Image List is empty !");
            }
        }
        return false;
    }

}
