package com.hb.achat.guide.achatguide;

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

    public static String getCurTimeFormatted(long timeOffset) {
        long lt = new Date().getTime();
        lt = lt + timeOffset;
        Date nowTime = new Date(lt);
        SimpleDateFormat timeFormat = new SimpleDateFormat("HH:mm:ss");
        return timeFormat.format(nowTime);
    }

    public static String getCurDateTimeFormatted() {
        Date nowTime = new Date();
        SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        return timeFormat.format(nowTime);
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
