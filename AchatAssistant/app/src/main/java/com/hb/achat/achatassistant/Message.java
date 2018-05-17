package com.hb.achat.achatassistant;

import android.arch.persistence.room.Entity;
import android.arch.persistence.room.PrimaryKey;

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
}
