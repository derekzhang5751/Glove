package com.hb.achat.achatassistant;

import org.json.JSONException;
import org.json.JSONObject;

public class Inquiry {
    public String mAction = "";
    public String mIssueNum = "";

    public String toJsonString() {
        String json = "";
        JSONObject jsonObject = new JSONObject();
        try {
            jsonObject.put("action",     mAction);
            jsonObject.put("achat_name", Tools.ACHAT_NAME);
            jsonObject.put("group_name", Tools.GROUP_NAME);
            jsonObject.put("issue_num",  mIssueNum);

            json = jsonObject.toString();
        } catch (JSONException e) {
            e.printStackTrace();
        }

        return json;
    }
}
