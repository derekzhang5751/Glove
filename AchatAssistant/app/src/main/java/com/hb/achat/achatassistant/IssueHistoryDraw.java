package com.hb.achat.achatassistant;

import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.os.Environment;
import android.util.Log;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.List;

public class IssueHistoryDraw {
    public static final int TITLE_HEIGHT = 50;
    public static final int PICTURE_WIDTH = 900;
    public static final int ISSUE_WIDTH = 130;
    public static final int NUM_WIDTH = 60;
    public static final int NUM_HEIGHT = 60;
    public static final int ROW_HEIGHT = NUM_HEIGHT + 10;

    private List<Issue> mIssueList;
    private int mRowSize;
    private int mPictureHeight;
    private Bitmap mBitmap;
    private Paint mPaintLine;
    private Paint mPaintTitleBk;
    private Paint mPaintTitlePen;
    private Paint mPaintTextPen;
    private Paint mPaintNumPen;
    private Paint[] mPaintNumBk;
    private Paint mPaintSumPen;
    private Paint mPaintZPen;
    private Paint mPaintAPen;

    public boolean init(List<Issue> issueList) {
        mIssueList = issueList;
        if (mIssueList == null) {
            mRowSize = 0;
            mPictureHeight = TITLE_HEIGHT;
        } else {
            mRowSize = mIssueList.size();
            mPictureHeight = TITLE_HEIGHT + (ROW_HEIGHT * mRowSize);
        }

        mPaintLine = new Paint();
        mPaintLine.setColor(Color.LTGRAY);
        mPaintLine.setStrokeWidth(1);

        mPaintTitleBk = new Paint();
        mPaintTitleBk.setColor(0xFFE6E6E6);

        mPaintTitlePen = new Paint();
        mPaintTitlePen.setColor(Color.BLACK);
        mPaintTitlePen.setFakeBoldText(true);
        mPaintTitlePen.setTextSize(20);

        mPaintTextPen = new Paint();
        mPaintTextPen.setColor(Color.BLACK);
        mPaintTextPen.setFakeBoldText(false);
        mPaintTextPen.setTextSize(18);

        mPaintNumPen = new Paint();
        mPaintNumPen.setColor(Color.WHITE);
        mPaintNumPen.setFakeBoldText(true);
        mPaintNumPen.setTextSize(26);

        mPaintSumPen = new Paint();
        mPaintSumPen.setColor(0xFF802B00);
        mPaintSumPen.setFakeBoldText(false);
        mPaintSumPen.setTextSize(26);

        mPaintZPen = new Paint();
        mPaintZPen.setColor(0xFFFF4D4D);
        mPaintZPen.setFakeBoldText(false);
        mPaintZPen.setTextSize(26);

        mPaintAPen = new Paint();
        mPaintAPen.setColor(0xFF9999FF);
        mPaintAPen.setFakeBoldText(false);
        mPaintAPen.setTextSize(26);

        mPaintNumBk = new Paint[10];
        for (int i=0; i<10; i++) {
            mPaintNumBk[i] = new Paint();
            mPaintNumBk[i].setStrokeWidth(1);
            switch (i) {
                case 0:
                    mPaintNumBk[i].setColor(0xFFFFC266);
                    break;
                case 1:
                    mPaintNumBk[i].setColor(0xFF99B3FF);
                    break;
                case 2:
                    mPaintNumBk[i].setColor(0xFFA9C653);
                    break;
                case 3:
                    mPaintNumBk[i].setColor(0xFFFF8C1A);
                    break;
                case 4:
                    mPaintNumBk[i].setColor(0xFF00CCFF);
                    break;
                case 5:
                    mPaintNumBk[i].setColor(0xFF6666FF);
                    break;
                case 6:
                    mPaintNumBk[i].setColor(0xFF737373);
                    break;
                case 7:
                    mPaintNumBk[i].setColor(0xFFFF80AA);
                    break;
                case 8:
                    mPaintNumBk[i].setColor(0xFFB37700);
                    break;
                case 9:
                    mPaintNumBk[i].setColor(0xFF009933);
                    break;
                default:
                    mPaintNumBk[i].setColor(Color.BLACK);
                    break;
            }
        }

        return true;
    }

    public Bitmap draw() {
        mBitmap = Bitmap.createBitmap(PICTURE_WIDTH, mPictureHeight, Bitmap.Config.ARGB_4444);
        Canvas canvas = new Canvas(mBitmap);

        // Draw background
        canvas.drawColor(Color.WHITE);

        // Draw title
        canvas.drawRect(0, 0, PICTURE_WIDTH, TITLE_HEIGHT, mPaintTitleBk);
        canvas.drawLine(0, TITLE_HEIGHT, PICTURE_WIDTH, TITLE_HEIGHT, mPaintLine);
        canvas.drawLine(ISSUE_WIDTH, 0, ISSUE_WIDTH, mPictureHeight, mPaintLine);
        int w = ISSUE_WIDTH + (NUM_WIDTH * 10) + 20;
        canvas.drawLine(w, 0, w, mPictureHeight, mPaintLine);

        canvas.drawText("奖期", 40, 35, mPaintTitlePen);
        canvas.drawText("开奖号码", 400, 35, mPaintTitlePen);
        canvas.drawText("冠亚和", 800, 35, mPaintTitlePen);

        // Draw issue data
        for (int i=0; i<mRowSize; i++) {
            Issue issue = mIssueList.get(i);
            int h = TITLE_HEIGHT + (ROW_HEIGHT * (i+1));
            canvas.drawLine(0, h, PICTURE_WIDTH, h, mPaintLine);
            canvas.drawText(issue.issueNum, 10, h-30, mPaintTextPen);

            for (int j=0; j<10; j++) {
                int index = issue.num[j];
                int x = ISSUE_WIDTH + 40 + (NUM_WIDTH * j);
                canvas.drawCircle(x, h-35, 25, mPaintNumBk[index-1]);
                if (index > 9) {
                    canvas.drawText(Integer.toString(index), x - 15, h - 25, mPaintNumPen);
                } else {
                    canvas.drawText(Integer.toString(index), x - 8, h - 25, mPaintNumPen);
                }
            }

            int sum = issue.num[0] + issue.num[1];
            canvas.drawText(Integer.toString(sum), 770, h - 25, mPaintSumPen);
            if (sum > 10) {
                canvas.drawText("大", 815, h - 25, mPaintZPen);
            } else {
                canvas.drawText("小", 815, h - 25, mPaintAPen);
            }
            if (sum % 2 == 0) {
                canvas.drawText("双", 850, h - 25, mPaintZPen);
            } else {
                canvas.drawText("单", 850, h - 25, mPaintAPen);
            }
        }

        //canvas.drawBitmap(mBitmap, 0, 0, null);
        return mBitmap;
    }

    public Bitmap getmBitmap() {
        return mBitmap;
    }

    public boolean saveToFile() {
        String fileName = "";
        if (mRowSize > 0) {
            Issue issue = mIssueList.get(0);
            fileName = issue.issueNum + ".jpg";
        } else {
            return false;
        }
        Log.d("IssueHistoryDraw", "FileName: " + fileName);

        String filePath = Environment.getExternalStorageDirectory().getPath();
        filePath = filePath + "/issuepic";
        filePath = "/data/data/com.hb.achat.drawissuehistory/files/issuepic";
        if (!makeSurePathExist(filePath)) {
            return false;
        }
        filePath = filePath + "/" + fileName;
        Log.d("IssueHistoryDraw", "FilePath: " + filePath);

        FileOutputStream out = null;
        boolean success = false;
        try {
            out = new FileOutputStream(filePath);
            mBitmap.compress(Bitmap.CompressFormat.JPEG, 100, out);
            success = true;
            Log.d("IssueHistoryDraw", "Save picture success");
        } catch (Exception e) {
            e.printStackTrace();
            success = false;
        } finally {
            try {
                if (out != null) {
                    out.close();
                }
            } catch (IOException ioe) {
                ioe.printStackTrace();
            }
        }

        return success;
    }

    private boolean makeSurePathExist(String filePath) {
        File file = new File(filePath);
        if (file.exists()) {
            Log.d("IssueHistoryDraw", "FilePath: " + filePath + ", exist");
            return true;
        } else {
            if (file.mkdir()) {
                Log.d("IssueHistoryDraw", "Create file path: " + filePath + ", success");
                return true;
            } else {
                Log.d("IssueHistoryDraw", "Create file path: " + filePath + ", failed");
                return false;
            }
        }
    }
}
