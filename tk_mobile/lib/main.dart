import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

class MyHttpOverrides extends HttpOverrides {
  @override
  HttpClient createHttpClient(SecurityContext? context) {
    return super.createHttpClient(context)
      ..badCertificateCallback = (X509Certificate cert, String host, int port) => true;
  }
}

final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin = FlutterLocalNotificationsPlugin();

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  HttpOverrides.global = MyHttpOverrides();
  
  // Initialize Local Notifications
  const AndroidInitializationSettings initializationSettingsAndroid = AndroidInitializationSettings('@mipmap/ic_launcher');
  const InitializationSettings initializationSettings = InitializationSettings(android: initializationSettingsAndroid);
  await flutterLocalNotificationsPlugin.initialize(initializationSettings);
  
  // Request permission for Android 13+
  flutterLocalNotificationsPlugin.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()?.requestNotificationsPermission();
  
  // Set elegant translucent system navigation bar and status bar style
  SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
    statusBarColor: Colors.transparent,
    statusBarIconBrightness: Brightness.light,
    systemNavigationBarColor: Color(0xFF0F172A),
    systemNavigationBarIconBrightness: Brightness.light,
  ));
  
  SystemChrome.setPreferredOrientations([DeviceOrientation.portraitUp]);
  runApp(const TkWebViewApp());
}

class TkWebViewApp extends StatelessWidget {
  const TkWebViewApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'GAMA - Timekeeping',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        fontFamily: 'Inter',
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0F172A), // Premium Slate Black
          primary: const Color(0xFF1E293B),
          secondary: const Color(0xFF3B82F6), // Vibrant Accent Blue
          background: const Color(0xFF0F172A),
        ),
      ),
      home: const SplashScreen(),
    );
  }
}

// ========================================================
// SPLASH SCREEN (شاشة ترحيبية أنيقة بشعار النظام)
// ========================================================
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    
    _animationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    );
    
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeIn),
    );
    
    _animationController.forward();

    // Navigate to WebViewerScreen after 3 seconds
    Timer(const Duration(seconds: 3), () {
      if (mounted) {
        Navigator.of(context).pushReplacement(
          PageRouteBuilder(
            pageBuilder: (_, __, ___) => const WebViewerScreen(),
            transitionsBuilder: (_, animation, __, child) {
              return FadeTransition(opacity: animation, child: child);
            },
            transitionDuration: const Duration(milliseconds: 800),
          ),
        );
      }
    });
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A), // Dark elegant background
      body: Stack(
        children: [
          // Background elegant ambient glow
          Positioned(
            top: -100,
            right: -100,
            child: Container(
              width: 300,
              height: 300,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF3B82F6).withOpacity(0.15),
                    blurRadius: 100,
                    spreadRadius: 50,
                  ),
                ],
              ),
            ),
          ),
          Center(
            child: FadeTransition(
              opacity: _fadeAnimation,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // App Emblem
                  Container(
                    width: 120,
                    height: 120,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.05),
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: const Color(0xFF3B82F6).withOpacity(0.3),
                        width: 2,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF3B82F6).withOpacity(0.2),
                          blurRadius: 30,
                          spreadRadius: 5,
                        ),
                      ],
                    ),
                    child: const Center(
                      child: Icon(
                        Icons.shield_outlined,
                        size: 65,
                        color: Color(0xFF60A5FA), // Light Blue
                      ),
                    ),
                  ),
                  const SizedBox(height: 30),
                  // App Title (Arabic/English Elegant design)
                  const Text(
                    'نظام إدارة الحضور والانصراف',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'GAMA - HR & TIMEKEEPING SYSTEM',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF94A3B8),
                      letterSpacing: 2.0,
                    ),
                  ),
                  const SizedBox(height: 60),
                  // Glowing loading circular indicator
                  const SizedBox(
                    width: 32,
                    height: 32,
                    child: CircularProgressIndicator(
                      strokeWidth: 3,
                      valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF3B82F6)),
                    ),
                  ),
                ],
              ),
            ),
          ),
          // Footer copyright
          const Positioned(
            bottom: 30,
            left: 0,
            right: 0,
            child: Text(
              'جميع الحقوق محفوظة © GAMA',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 11,
                color: Color(0xFF475569),
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ========================================================
// MAIN WEBVIEWER SCREEN (مستعرض الويب المتقدم بجميع خصائصه)
// ========================================================
class WebViewerScreen extends StatefulWidget {
  const WebViewerScreen({super.key});

  @override
  State<WebViewerScreen> createState() => _WebViewerScreenState();
}

class _WebViewerScreenState extends State<WebViewerScreen> {
  late final WebViewController _controller;
  double _loadingProgress = 0.0;
  bool _isOffline = false;
  bool _isPageLoading = true;
  
  // The Static Public IP and port configured by the user
  static const String targetUrl = 'http://81.10.14.216:8080/';

  @override
  void initState() {
    super.initState();
    _initializeController();
  }

  void _initializeController() {
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(const Color(0xFF0F172A))
      ..addJavaScriptChannel('NativeBridge', onMessageReceived: (JavaScriptMessage message) async {
        if (message.message.startsWith('notify:')) {
           List<String> parts = message.message.substring(7).split('|');
           String title = parts.isNotEmpty ? parts[0] : 'إشعار جديد';
           String body = parts.length > 1 ? parts[1] : '';
           
           const AndroidNotificationDetails androidPlatformChannelSpecifics = AndroidNotificationDetails(
             'tk_notifications', 'إشعارات النظام',
             importance: Importance.max,
             priority: Priority.high,
             playSound: true,
             icon: '@mipmap/ic_launcher'
           );
           const NotificationDetails platformChannelSpecifics = NotificationDetails(android: androidPlatformChannelSpecifics);
           await flutterLocalNotificationsPlugin.show(
             DateTime.now().millisecond,
             title,
             body,
             platformChannelSpecifics,
           );
        }
      })
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (int progress) {
            setState(() {
              _loadingProgress = progress / 100.0;
            });
          },
          onPageStarted: (String url) {
            setState(() {
              _isPageLoading = true;
              _isOffline = false;
            });
          },
          onPageFinished: (String url) {
            setState(() {
              _isPageLoading = false;
              _loadingProgress = 0.0;
            });
          },
          onWebResourceError: (WebResourceError error) {
            // Trigger offline/retry screen on any network or connection error
            if (error.isForMainFrame ?? true) {
              setState(() {
                _isOffline = true;
                _isPageLoading = false;
              });
            }
          },
        ),
      )
      ..loadRequest(Uri.parse(targetUrl));
  }

  // Reload action triggered by manual refresh or retry button
  Future<void> _handleReload() async {
    setState(() {
      _isOffline = false;
      _isPageLoading = true;
      _loadingProgress = 0.05;
    });
    await _controller.loadRequest(Uri.parse(targetUrl));
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvoked: (bool didPop) async {
        if (didPop) return;
        if (await _controller.canGoBack()) {
          await _controller.goBack();
        } else {
          // Double tap or confirm back to close the application safely
          _showExitConfirmation();
        }
      },
      child: Scaffold(
        backgroundColor: const Color(0xFF0F172A),
        body: SafeArea(
          child: Stack(
            children: [
              // 1. The Main WebView
              if (!_isOffline)
                WebViewWidget(controller: _controller),
              
              // 2. Premium Linear Progress Bar during loads
              if (_isPageLoading && _loadingProgress > 0.0 && _loadingProgress < 1.0)
                Positioned(
                  top: 0,
                  left: 0,
                  right: 0,
                  child: LinearProgressIndicator(
                    value: _loadingProgress,
                    backgroundColor: const Color(0xFF1E293B),
                    valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF3B82F6)),
                    minHeight: 3.5,
                  ),
                ),

              // 3. Modern Offline Screen (شاشة انقطاع الاتصال الراقية)
              if (_isOffline)
                _buildOfflineScreen(),
            ],
          ),
        ),
      ),
    );
  }

  // Dialog box to confirm exiting the application
  void _showExitConfirmation() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: const Color(0xFF1E293B),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'تأكيد الخروج',
          textAlign: TextAlign.right,
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
        ),
        content: const Text(
          'هل تريد إغلاق تطبيق الحضور والانصراف؟',
          textAlign: TextAlign.right,
          style: TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('إلغاء', style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.bold)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () {
              SystemNavigator.pop();
            },
            child: const Text('خروج', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  // Build high quality visual representation of Offline state
  Widget _buildOfflineScreen() {
    return Container(
      width: double.infinity,
      height: double.infinity,
      color: const Color(0xFF0F172A),
      padding: const EdgeInsets.symmetric(horizontal: 30),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          // Offline glowing icon
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: const Color(0xFFEF4444).withOpacity(0.1),
              shape: BoxShape.circle,
              border: Border.all(
                color: const Color(0xFFEF4444).withOpacity(0.3),
                width: 1.5,
              ),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFFEF4444).withOpacity(0.05),
                  blurRadius: 40,
                  spreadRadius: 10,
                ),
              ],
            ),
            child: const Icon(
              Icons.wifi_off_rounded,
              size: 70,
              color: Color(0xFFF87171),
            ),
          ),
          const SizedBox(height: 35),
          // Heading
          const Text(
            'لا يوجد اتصال بالشبكة',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 12),
          // Sub-heading
          const Text(
            'تعذر الاتصال بخادم الحضور والانصراف، يرجى التأكد من تشغيل البيانات أو الواي فاي، وتوفر الخادم.',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 13,
              color: Color(0xFF94A3B8),
              height: 1.6,
            ),
          ),
          const SizedBox(height: 45),
          // Interactive retry button
          SizedBox(
            width: 200,
            height: 50,
            child: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF3B82F6),
                foregroundColor: Colors.white,
                elevation: 3,
                shadowColor: const Color(0xFF3B82F6).withOpacity(0.4),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              onPressed: _handleReload,
              icon: const Icon(Icons.refresh_rounded, size: 22),
              label: const Text(
                'إعادة المحاولة',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 0.5,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
