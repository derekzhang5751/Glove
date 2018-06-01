package com.hb.achat.achatassistant;

import android.text.TextUtils;

import java.nio.charset.Charset;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;

public class Tools {

    public static final String ACHAT_NAME = "Android-001";
    public static final String GROUP_NAME = "内部群2";
    public static final String MD5_KEY = "ikenviWCkkiCvk8834701lfkdjfd";

    public static String getCurTimeFormatted() {
        Date nowTime = new Date();
        SimpleDateFormat timeFormat = new SimpleDateFormat("HH:mm:ss");
        return timeFormat.format(nowTime);
    }

    public static String getCurDateTimeFormatted() {
        Date nowTime = new Date();
        SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        return timeFormat.format(nowTime);
    }

    public static boolean isRepeatPosition(List<Message> msgList, List<Message> oldList, int pos) {
        int msgLen = msgList.size();
        int oldLen = oldList.size();

        if (oldLen <= 0) {
            return false;
        }

        Message msg1 = msgList.get(pos);
        Message msg2 = oldList.get(oldLen-1);

        if (msg1.same(msg2)) {
            if (pos == 0) {
                return true;
            } else {
                if (oldLen == 1) {
                    return false;
                } else {
                    Message msg3 = msgList.get(pos-1);
                    Message msg4 = oldList.get(oldLen-2);
                    if (msg3.same(msg4)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        } else {
            return false;
        }
    }

    public static void getNewMessageList(List<Message> msgList, List<Message> oldList, List<Message> newList) {
        newList.clear();
        int newMsgPosition = 0;
        int msgLen = msgList.size();

        for (int i=msgLen-1; i>=0; i--) {
            if (isRepeatPosition(msgList, oldList, i)) {
                newMsgPosition = i + 1;
                break;
            }
        }

        if (newMsgPosition < msgLen) {
            for (int i=newMsgPosition; i<msgLen; i++) {
                Message newMsg = msgList.get(i);
                newList.add(newMsg);
                oldList.add(newMsg);
            }
        }
    }

    public static String md5(String string) {
        if (TextUtils.isEmpty(string)) {
            return "";
        }

        byte[] source = string.getBytes(Charset.forName("UTF-8"));

        String result = "";
        try {
            MessageDigest md5 = MessageDigest.getInstance("MD5");
            md5.reset();
            md5.update(source);

            StringBuffer buf = new StringBuffer();
            for (byte b:md5.digest()) {
                buf.append( String.format("%02x", b&0xff) );
            }
            result = buf.toString();

        } catch (NoSuchAlgorithmException e) {
            e.printStackTrace();
        }

        return result;
    }

    public static void sleep(long wait) {
        try {
            Thread.sleep(wait);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }

}
