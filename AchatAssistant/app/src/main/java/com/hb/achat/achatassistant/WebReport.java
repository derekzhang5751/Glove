package com.hb.achat.achatassistant;

import android.os.Handler;
import android.os.Message;
import android.text.TextUtils;
import android.util.Base64;
import android.util.Log;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

public class WebReport implements Runnable {
    public static final String SERVER_HOST = "http://205.209.167.174:8089";
    public boolean mPauseFlag;
    private boolean mExitFlag;
    private Thread mThread;
    private String mThreadName;

    private String mSendText;
    private AchatDao mAchatDao;
    private Handler mHandler;
    private String mIssueNum;

    public static class ThreadParameter {
        public AchatDao mAchatDao;
        public Handler mHandle;
    }

    WebReport(ThreadParameter tp) {
        mExitFlag = false;
        mPauseFlag = true;
        mThread = null;
        mThreadName = "WebReportThread";
        mAchatDao = tp.mAchatDao;
        mHandler = tp.mHandle;
    }

    @Override
    public void run() {
        Log.d("AASERVICE", "WebReportThread start");
        Schedule schedule = new Schedule();
        int lastStep = Schedule.STEP_NULL;
        int wait = schedule.getNextWait();

        while (!mExitFlag) {
            if (mPauseFlag) {
                try {
                    Thread.sleep(1000);
                } catch (InterruptedException ex) {
                    Thread.currentThread().interrupt();
                }
                continue;
            }

            int step = schedule.next();
            //Log.d("AASERVICE", "WebReportThread, step=" + Integer.toString(step));
            if (step == lastStep) {
                if (step == Schedule.STEP_CLASS || step == Schedule.STEP_END_TIP) {
                    doOrder();
                }
                try {
                    Thread.sleep(wait * 1000);
                } catch (InterruptedException ex) {
                    Thread.currentThread().interrupt();
                }
                continue;
            }

            lastStep = step;
            switch (step) {
                case Schedule.STEP_INIT_ENV:
                    doInitEnv();
                    break;
                case Schedule.STEP_LAST_TERM:
                    doLastTerm();
                    break;
                case Schedule.STEP_WELCOME:
                    doWelcome();
                    break;
                case Schedule.STEP_CLASS:
                    doOrder();
                    break;
                case Schedule.STEP_END_TIP:
                    doEndTip();
                    break;
                case Schedule.STEP_END:
                    doEnd();
                    break;
                case Schedule.STEP_CHECK:
                    doCheck();
                    break;
                case Schedule.STEP_ISSUE:
                    doIssue();
                    break;
                default:
                    try {
                        Thread.sleep(1000);
                    } catch (InterruptedException ex) {
                        Thread.currentThread().interrupt();
                    }
                    break;
            }
            try {
                Thread.sleep(wait * 1000);
            } catch (InterruptedException ex) {
                Thread.currentThread().interrupt();
            }
        }
        // Working thread exits
        Log.d("AASERVICE", "WebReportThread exit");
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
        //
    }

    private void doLastTerm() {
        mSendText = "上期回顾\n具体内容在开发中...";
        Message msg = new Message();
        msg.what = Schedule.STEP_LAST_TERM;
        mHandler.sendMessage(msg);
    }

    private void doWelcome() {
        mSendText = "近期骗子很多，我们财务不主动加人，主动加你们的都是骗子\n欢迎大家玩的开心\n注：请注意自己下的注，7个以上的后台没结流水，无需纠结！";
        Message msg = new Message();
        msg.what = Schedule.STEP_WELCOME;
        mHandler.sendMessage(msg);
    }

    private void doOrder() {
        com.hb.achat.achatassistant.Message[] arrayMsg = mAchatDao.selectMessageUnprocessed(5);
        //Log.d("AASERVICE", "WebReport, do order get message unprocessed " + Integer.toString(arrayMsg.length));
        for (int i=0; i<arrayMsg.length; i++) {
            com.hb.achat.achatassistant.Message msg = arrayMsg[i];
            reportMessage(msg);
            if (msg.status != 0) {
                mAchatDao.updateMessage(msg);
                if (!TextUtils.isEmpty(msg.reply)) {
                    mSendText = "@" + msg.fromUserNick + " " + msg.reply;
                    Message threadMsg = new Message();
                    threadMsg.what = Schedule.STEP_CLASS;
                    mHandler.sendMessage(threadMsg);
                }
            }
        }
    }

    private void doEndTip() {
        mSendText = "==== 距离下课还有60秒 ====\n请按规范格式写作业，否则无效，学费不足作业格式不对无效，名字未上功课表当期考试无效\n==== 距离下课还有60秒 ====";
        Message msg = new Message();
        msg.what = Schedule.STEP_END_TIP;
        mHandler.sendMessage(msg);
    }

    private void doEnd() {
        mSendText = "==== 下课 ====\n不加，不改，不取消\n先交学费，再上课\n名字上功课表下注才有效\n有问题请私聊老师咨询\n未在下课名单概不负责\n==== 下课 ====";
        Message msg = new Message();
        msg.what = Schedule.STEP_END;
        mHandler.sendMessage(msg);
    }

    private void doCheck() {
        mSendText = "==== 本期竞猜核对 ====";

        Inquiry inquiry = new Inquiry();
        inquiry.mAction = "verify";
        inquiry.mIssueNum = "";
        String response = reportInquery(inquiry);
        if (!TextUtils.isEmpty(response)) {
            try {
                JSONObject jsonObject = new JSONObject(response);
                inquiry.mIssueNum = jsonObject.getJSONObject("data").getString("issue_num");
                mIssueNum = inquiry.mIssueNum;
                mSendText = mSendText + "\n奖期：" + mIssueNum;

                JSONArray userList = jsonObject.getJSONObject("data").getJSONArray("user_list");
                for (int i=0; i<userList.length(); i++) {
                    JSONObject orderObj = userList.getJSONObject(i);
                    String nickName = orderObj.getString("nick_name");
                    int balance = orderObj.getInt("balance");
                    String tmp = String.format("\n\n(%s)积分： %d", nickName, balance);
                    mSendText += tmp;

                    JSONArray orderList = orderObj.getJSONArray("orders");
                    for (int j=0; j<orderList.length(); j++) {
                        String orderStr = orderList.getString(j);
                        mSendText = mSendText + "\n" + orderStr;
                    }
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        } else {
            mSendText += "\n空";
        }

        Message msg = new Message();
        msg.what = Schedule.STEP_CHECK;
        mHandler.sendMessage(msg);
    }

    private void doIssue() {
        mSendText = "==== 上期竞猜结果 ====";

        Inquiry inquiry = new Inquiry();
        inquiry.mAction = "result";
        inquiry.mIssueNum = mIssueNum;
        String response = reportInquery(inquiry);
        if (!TextUtils.isEmpty(response)) {
            try {
                JSONObject jsonObject = new JSONObject(response);
                inquiry.mIssueNum = jsonObject.getJSONObject("data").getString("issue_num");
                mSendText = mSendText + "\n奖期：" + inquiry.mIssueNum;

                JSONArray userList = jsonObject.getJSONObject("data").getJSONArray("user_list");
                int len = userList.length();
                if (len > 0) {
                    mSendText = mSendText + "\n中奖名单：";
                    for (int i = 0; i <len; i++) {
                        String orderStr = userList.getString(i);
                        mSendText = mSendText + "\n" + orderStr;
                    }
                } else {
                    mSendText = mSendText + "\n上期无人中奖";
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        } else {
            mSendText += " 空";
        }

        Message msg = new Message();
        msg.what = Schedule.STEP_ISSUE;
        mHandler.sendMessage(msg);
    }

    private void reportMessage(com.hb.achat.achatassistant.Message msg) {
        String sUrl = SERVER_HOST + "/Achat/Message/do.php";
        String postData = "";
        String json = msg.toJsonString();
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
                msg.reply = jsonObject.getJSONObject("data").getString("reply");
                msg.status = jsonObject.getJSONObject("data").getInt("status");
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    private String reportInquery(Inquiry inquiry) {
        String sUrl = SERVER_HOST + "/Achat/Inquiry/do.php";
        String postData = "";
        String json = inquiry.toJsonString();
        String base64 = Base64.encodeToString(json.getBytes(), Base64.DEFAULT);
        base64 = base64.replace("\r", "");
        base64 = base64.replace("\n", "");
        String md5 = Tools.md5(base64 + Tools.MD5_KEY);

        postData = "version=1.0&DeviceType=1&md5=" + md5 + "&data=" + base64;
        String response = httpPost(sUrl, postData);

        return response;
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
