package com.gamma.hrsystem;

import android.Manifest;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.content.Context;
import android.content.pm.PackageManager;
import android.media.AudioAttributes;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import com.getcapacitor.BridgeActivity;
import com.onesignal.OneSignal;
import com.onesignal.debug.LogLevel;

public class MainActivity extends BridgeActivity {
    private static final String ONESIGNAL_APP_ID = "9d306f1d-5e6d-4d06-91ed-0395ed04a46a";

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        createNotificationChannel();
        requestNotificationPermission();

        // تهيئة OneSignal على مستوى نظام أندرويد ليعمل والتطبيق مغلق
        try {
            OneSignal.getDebug().setLogLevel(LogLevel.VERBOSE);
            OneSignal.initWithContext(this, ONESIGNAL_APP_ID);
            OneSignal.getNotifications().requestPermission(false, com.onesignal.Continue.none());
        } catch (Throwable t) {
            t.printStackTrace();
        }

        // جسر جافاسكربت لربط User ID بـ OneSignal عند تسجيل الدخول
        try {
            if (this.bridge != null && this.bridge.getWebView() != null) {
                this.bridge.getWebView().addJavascriptInterface(new Object() {
                    @android.webkit.JavascriptInterface
                    public void login(String userId) {
                        if (userId != null && !userId.trim().isEmpty()) {
                            OneSignal.login(userId.trim());
                        }
                    }

                    @android.webkit.JavascriptInterface
                    public String getSubscriptionId() {
                        try {
                            String subId = OneSignal.getUser().getPushSubscription().getId();
                            return subId != null ? subId : "";
                        } catch (Throwable t) {
                            return "";
                        }
                    }

                    @android.webkit.JavascriptInterface
                    public void logout() {
                        OneSignal.logout();
                    }
                }, "NativeOneSignal");
            }
        } catch (Throwable t) {
            t.printStackTrace();
        }
    }

    private void createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            String channelId = "hr_channel";
            CharSequence name = "إشعارات الموارد البشرية";
            String description = "إشعارات الإجازات والإضافي والجزاءات";
            int importance = NotificationManager.IMPORTANCE_HIGH;

            NotificationChannel channel = new NotificationChannel(channelId, name, importance);
            channel.setDescription(description);
            channel.enableLights(true);
            channel.enableVibration(true);
            channel.setLockscreenVisibility(android.app.Notification.VISIBILITY_PUBLIC);

            Uri defaultSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
            AudioAttributes audioAttributes = new AudioAttributes.Builder()
                .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                .build();
            channel.setSound(defaultSoundUri, audioAttributes);

            NotificationManager notificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
            if (notificationManager != null) {
                notificationManager.createNotificationChannel(channel);
            }
        }
    }

    private void requestNotificationPermission() {
        if (Build.VERSION.SDK_INT >= 33) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                ActivityCompat.requestPermissions(this, new String[]{Manifest.permission.POST_NOTIFICATIONS}, 101);
            }
        }
    }
}
