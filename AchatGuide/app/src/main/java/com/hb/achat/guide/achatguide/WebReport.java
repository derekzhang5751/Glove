package com.hb.achat.guide.achatguide;

import android.content.Context;
import android.os.Handler;
import android.os.Message;
import android.text.TextUtils;
import android.util.Base64;
import android.util.Log;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.Date;

public class WebReport implements Runnable {
    public static final String SERVER_HOST = "http://205.209.167.174:8089";
    public long mServerTimeOffset = 0;
    public boolean mPauseFlag;
    private boolean mExitFlag;
    private Thread mThread;
    private String mThreadName;

    private String mSendText;
    private Handler mHandler;

    public static class ThreadParameter {
        public Handler mHandle;
        public Context mContext;
    }

    WebReport(ThreadParameter tp) {
        mExitFlag = false;
        mPauseFlag = true;
        mThread = null;
        mThreadName = "AAGuideThread";
        mHandler = tp.mHandle;
    }

    @Override
    public void run() {
        Log.d("AAGUIDE", "AAGuideThread start");
        Schedule schedule = new Schedule();
        int lastStep = Schedule.STEP_NULL;
        int wait;

        while (!mExitFlag) {
            if (mPauseFlag) {
                Tools.sleep(1000);
                continue;
            }

            int step = schedule.next();
            //wait = schedule.getNextWait();
            //Log.d("AAGUIDE", "WebReportThread, step=" + Integer.toString(step));
            if (step == lastStep) {
                Tools.sleep(5000);
                continue;
            }

            lastStep = step;
            switch (step) {
                case Schedule.STEP_INIT_ENV:
                    doInitEnv();
                    if (mServerTimeOffset > 0) {
                        schedule.setTimeOffset(mServerTimeOffset);
                    }
                    break;
                case Schedule.STEP_CLASS:
                    doOrder();
                    break;
                default:
                    Tools.sleep(2000);
                    break;
            }
            Tools.sleep(2000);
        }
        // Working thread exits
        Log.d("AAGUIDE", "AAGuideThread exit");
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

    public String getSendText() {
        return mSendText;
    }

    private void doInitEnv() {
        // sync time with server
        long tBegin = new Date().getTime();
        //Log.d("AAGUIDE", "Time begin: " + Long.toString(tBegin));
        long serverUTC = getServerUTC();
        //Log.d("AAGUIDE", "Server UTC: " + Long.toString(serverUTC));
        long tEnd = new Date().getTime();
        //Log.d("AAGUIDE", "Time end: " + Long.toString(tEnd));

        if (serverUTC > 0) {
            long cost = (tEnd - tBegin) / 2;
            long offset = serverUTC - tBegin - cost;
            mServerTimeOffset = offset;
        } else {
            mServerTimeOffset = 0;
        }

        Message msg = new Message();
        msg.what = Schedule.STEP_INIT_ENV;
        msg.arg1 = (int)(mServerTimeOffset/1000);
        mHandler.sendMessage(msg);
    }

    private void doOrder() {
        String preOrder = requestPreOrder();

        if (!TextUtils.isEmpty(preOrder)) {
            mSendText = preOrder;
            Message msg = new Message();
            msg.what = Schedule.STEP_CLASS;
            mHandler.sendMessage(msg);
        }
    }

    private long getServerUTC() {
        long serverUTC = 0;

        String json = "";
        JSONObject jsonObject = new JSONObject();
        try {
            jsonObject.put("action", "GET_UTC");
            jsonObject.put("achat_name", Tools.ACHAT_NAME);
            jsonObject.put("group_name", Tools.GROUP_NAME);

            json = jsonObject.toString();
        } catch (JSONException e) {
            e.printStackTrace();
        }

        String sUrl = SERVER_HOST + "/Achat/SyncTime/do.php";
        String postData = "";
        String base64 = Base64.encodeToString(json.getBytes(), Base64.DEFAULT);
        base64 = base64.replace("\r", "");
        base64 = base64.replace("\n", "");
        String md5 = Tools.md5(base64 + Tools.MD5_KEY);

        String urlEncode = "";
        try {
            urlEncode = URLEncoder.encode(base64, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            e.printStackTrace();
        }

        postData = "version=1.0&DeviceType=1&md5=" + md5 + "&data=" + urlEncode;
        String response = httpPost(sUrl, postData);
        //Log.d("AASERVICE", "Server UTC response: " + response);
        if (!TextUtils.isEmpty(response)) {
            try {
                jsonObject = new JSONObject(response);
                double dd = jsonObject.getJSONObject("data").getDouble("utc");
                serverUTC = (long)(dd * 1000);
            } catch (JSONException e) {
                serverUTC = 0;
                e.printStackTrace();
            }
        }

        return serverUTC;
    }

    private String requestPreOrder() {
        String preOrder = "";
        String sUrl = SERVER_HOST + "/Achat/Guide/do.php";
        String postData = "";
        String json = getPostData();
        String base64 = Base64.encodeToString(json.getBytes(), Base64.DEFAULT);
        base64 = base64.replace("\r", "");
        base64 = base64.replace("\n", "");
        String md5 = Tools.md5(base64 + Tools.MD5_KEY);

        String urlEncode = "";
        try {
            urlEncode = URLEncoder.encode(base64, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            e.printStackTrace();
        }

        postData = "version=1.0&DeviceType=1&md5=" + md5 + "&data=" + urlEncode;
        String response = httpPost(sUrl, postData);
        if (!TextUtils.isEmpty(response)) {
            try {
                JSONObject jsonObject = new JSONObject(response);
                boolean success = jsonObject.getBoolean("success");
                if (success) {
                    //issue = jsonObject.getJSONObject("data").getLong("issue");
                    preOrder = jsonObject.getJSONObject("data").getString("order");
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }

        return preOrder;
    }

    private String getPostData() {
        String json = "";
        JSONObject jsonObject = new JSONObject();
        try {
            jsonObject.put("action", "GetPreOrder");
            jsonObject.put("achat_name",  Tools.ACHAT_NAME);
            jsonObject.put("group_name",  Tools.GROUP_NAME);
            json = jsonObject.toString();
        } catch (JSONException e) {
            e.printStackTrace();
        }

        return json;
    }

    private String httpPost(String sUrl, String postData) {
        //Log.d("AASERVICE", "WebReport access: " + sUrl);
        String response = "";
        HttpURLConnection urlConnection = null;
        try {
            URL url = new URL(sUrl);
            urlConnection = (HttpURLConnection)url.openConnection();
            urlConnection.setConnectTimeout(5000);
            urlConnection.setReadTimeout(5000);
            urlConnection.setDoInput(true);
            urlConnection.setDoOutput(true);
            urlConnection.setUseCaches(false);
            urlConnection.setRequestMethod("POST");
            urlConnection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded;charset=utf-8");
            //urlConnection.setRequestProperty("Content-Type", "multipart/form-data");
            urlConnection.setRequestProperty("Charset", "utf-8");
            // Connect to server
            urlConnection.connect();
            // Send data
            DataOutputStream dos = new DataOutputStream(urlConnection.getOutputStream());
            dos.writeBytes(postData);
            dos.flush();
            dos.close();
            // Receive data
            BufferedReader br = new BufferedReader(new InputStreamReader(urlConnection.getInputStream()));

            String readLine = null;
            while ((readLine = br.readLine()) != null) {
                response += readLine;
            }
            br.close();
            urlConnection.disconnect();
            //
        } catch (Exception ex) {
            //Log.d("AASERVICE", "WebReport HTTP exception: " + ex.getMessage());
            ex.printStackTrace();
        } finally {
            if (urlConnection != null) {
                urlConnection.disconnect();
            }
        }
        //Log.d("AASERVICE", "WebReport response: " + response);
        return response;
    }
}
