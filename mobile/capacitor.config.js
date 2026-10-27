/**
 * رابط النظام الذي يفتحه التطبيق.
 *  - الإنتاج:   CAP_SERVER_URL=http://81.10.14.216:8080  (الافتراضي)
 *  - الديمو:    CAP_SERVER_URL=http://10.0.2.2:8088     (للمحاكي) أو http://IP-جهازك:8088 للموبايل الحقيقي
 */
const serverUrl = process.env.CAP_SERVER_URL || 'http://81.10.14.216:8080';

/** @type {import('@capacitor/cli').CapacitorConfig} */
const config = {
  appId: 'com.gamma.hrsystem',
  appName: 'HR System',
  webDir: 'www',
  server: {
    url: serverUrl,
    cleartext: serverUrl.startsWith('http://'),
    androidScheme: serverUrl.startsWith('https://') ? 'https' : 'http',
    allowNavigation: [new URL(serverUrl).host],
  },
  android: {
    allowMixedContent: true,
  },
  plugins: {
    SplashScreen: { launchShowDuration: 1200, backgroundColor: '#0f172a', showSpinner: false },
    StatusBar: { style: 'DARK', backgroundColor: '#0f172a' },
  },
};

module.exports = config;
