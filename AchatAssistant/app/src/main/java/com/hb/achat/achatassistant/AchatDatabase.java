package com.hb.achat.achatassistant;

import android.arch.persistence.room.Database;
import android.arch.persistence.room.RoomDatabase;

@Database(entities = {Message.class}, version = 1, exportSchema = false)
public abstract class AchatDatabase extends RoomDatabase {
    public abstract AchatDao achatDao();
}
