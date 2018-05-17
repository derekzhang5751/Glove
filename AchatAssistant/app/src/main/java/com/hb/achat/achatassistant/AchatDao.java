package com.hb.achat.achatassistant;

import android.arch.persistence.room.Dao;
import android.arch.persistence.room.Insert;
import android.arch.persistence.room.Query;
import android.arch.persistence.room.Update;

@Dao
public interface AchatDao {
    @Insert
    long insertMessage(Message msg);

    @Update
    int updateMessage(Message msg);

    @Query("SELECT * FROM messages WHERE status=0 ORDER BY id LIMIT :maxSize")
    Message[] selectMessageUnprocessed(int maxSize);

    @Query("SELECT * FROM messages ORDER BY id DESC LIMIT :maxSize")
    Message[] selectMessageLatest(int maxSize);
}
