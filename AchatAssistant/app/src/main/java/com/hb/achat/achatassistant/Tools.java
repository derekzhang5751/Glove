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

    public static boolean messageInTheList(Message targetMsg, List<Message> msgList) {
        int len = msgList.size();
        if (len < 1) {
            return false;
        }
        for (int i=len-1; i>=0; i--) {
            Message msg = msgList.get(i);
            if (msg.fromUserNick.equals(targetMsg.fromUserNick)
                    && msg.content.equals(targetMsg.content)) {
                return true;
            }
        }
        return false;
    }

    public static void getNewMessageList(List<Message> msgList, List<Message> oldList, List<Message> newList) {
        newList.clear();
        int newMsgPosition = -1;

        for (int i=0; i<msgList.size(); i++) {
            Message msg = msgList.get(i);

            if (newMsgPosition == -1 && messageInTheList(msg, oldList)) {
                continue;
            } else {
                newMsgPosition = i;
            }

            if (newMsgPosition >= 0) {
                newList.add(msg);
                oldList.add(msg);
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

}
