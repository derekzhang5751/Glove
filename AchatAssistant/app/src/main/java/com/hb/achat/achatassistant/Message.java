package com.hb.achat.achatassistant;

import android.arch.persistence.room.Entity;
import android.arch.persistence.room.PrimaryKey;
import android.util.Log;

import org.json.JSONException;
import org.json.JSONObject;

@Entity(tableName = "messages")
public class Message {
    @PrimaryKey(autoGenerate = true)
    public int id;

    public int status;
    public String fromUserNick;
    public String fromUserRemark;
    public String toUserNick;
    public String content;
    public String reply;
    public String recvTime;

    public String toJsonString() {
        String json = "";
        JSONObject jsonObject = new JSONObject();
        try {
            jsonObject.put("id",          id);
            jsonObject.put("achat_name",  Tools.ACHAT_NAME);
            jsonObject.put("group_name",  Tools.GROUP_NAME);
            jsonObject.put("from_nick",   fromUserNick);
            jsonObject.put("from_remark", fromUserRemark);
            jsonObject.put("to_nick",     toUserNick);
            jsonObject.put("content",     content);
            jsonObject.put("recvtime",    recvTime);
            jsonObject.put("status",      status);
            json = jsonObject.toString();
        } catch (JSONException e) {
            e.printStackTrace();
        }

        return json;
    }

    public boolean same(Message msg) {
        Log.d("AASERVICE", fromUserNick + "<=>" + msg.fromUserNick);
        Log.d("AASERVICE", content + "<=>" + msg.content);
        if (fromUserNick.equals(msg.fromUserNick) && content.equals(msg.content)) {
            Log.d("AASERVICE","return true");
            return true;
        } else {
            Log.d("AASERVICE","return false");
            return false;
        }
    }
}
