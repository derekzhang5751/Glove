package com.hb.achat.achatassistant;

import android.accessibilityservice.AccessibilityService;
import android.view.accessibility.AccessibilityEvent;
import android.widget.Toast;

public class AAService extends AccessibilityService {
    public AAService() {
    }

    @Override
    public void onAccessibilityEvent(AccessibilityEvent event) {
        int eventType = event.getEventType();
        switch (eventType) {
            case AccessibilityEvent.TYPE_WINDOW_STATE_CHANGED:
                showToastMessage("TYPE_WINDOW_STATE_CHANGED");
                break;
            case AccessibilityEvent.TYPE_WINDOW_CONTENT_CHANGED:
                showToastMessage("TYPE_WINDOW_CONTENT_CHANGED");
                break;
            case AccessibilityEvent.TYPE_WINDOWS_CHANGED:
                showToastMessage("TYPE_WINDOWS_CHANGED");
                break;
            default:
                break;
        }
    }

    @Override
    public void onInterrupt() {

    }

    @Override
    public void onServiceConnected() {
        super.onServiceConnected();
    }

    private void showToastMessage(String msg) {
        Toast.makeText(this, msg, Toast.LENGTH_SHORT).show();
    }

    private void setAccessibilityInfo() {
        /*String[] packageNames = {"com.tencent.mm"};
        AccessibilityServiceInfo asi = new AccessibilityServiceInfo();

        asi.eventTypes = AccessibilityEvent.TYPE_WINDOW_STATE_CHANGED
            | AccessibilityEvent.TYPE_WINDOW_CONTENT_CHANGED
            | AccessibilityEvent.TYPE_WINDOWS_CHANGED;
        asi.feedbackType = AccessibilityServiceInfo.FEEDBACK_GENERIC;
        asi.notificationTimeout = 100;
        asi.packageNames = packageNames;

        setServiceInfo(asi);*/
    }
}
