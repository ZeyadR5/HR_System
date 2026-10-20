<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - حدث خطأ غير متوقع</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Cairo', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #1e293b;
        }
        .error-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08), 0 0 1px 1px rgba(0, 0, 0, 0.02);
            max-width: 540px;
            width: 100%;
            padding: 45px 35px;
            text-align: center;
            border: 1px solid #f1f5f9;
        }
        .error-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: #fef2f2;
            color: #ef4444;
            font-size: 2.5rem;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.15);
        }
        .error-code {
            color: #94a3b8;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .error-desc {
            color: #64748b;
            font-size: 0.98rem;
            line-height: 1.7;
            margin-bottom: 30px;
        }
        .log-note {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.88rem;
            color: #475569;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-badge">⚠️</div>
        <div class="error-code">ERROR 500</div>
        <h1 class="error-title">عذراً، حدث خطأ غير متوقع!</h1>
        <p class="error-desc">
            واجه النظام مشكلة أثناء معالجة طلبك، لا داعي للقلق فبياناتك آمنة تماماً.
        </p>

        <div class="log-note">
            <span>📝</span>
            <span>تم تسجيل تفاصيل هذا الخطأ في سجلات النظام وسيقوم الدعم الفني بمراجعته.</span>
        </div>

        <div class="btn-group">
            <a href="{{ url('/') }}" class="btn btn-primary">
                <span>🏠</span>
                <span>العودة للرئيسية</span>
            </a>
            <button onclick="window.location.reload();" class="btn btn-secondary">
                <span>🔄</span>
                <span>إعادة المحاولة</span>
            </button>
        </div>
    </div>
</body>
</html>
