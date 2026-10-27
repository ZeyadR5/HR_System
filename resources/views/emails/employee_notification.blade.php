<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $subjectText }}</title>
</head>
<body style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color:#f1f5f9; margin:0; padding:20px; direction:rtl; text-align:right;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px; margin:0 auto; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.06); border:1px solid #e2e8f0;">
        <tr>
            <td style="background:linear-gradient(135deg, #0f172a, #1e293b); padding:24px 30px; text-align:center; color:#ffffff;">
                <div style="font-size:26px; font-weight:bold; letter-spacing:1px; margin-bottom:4px;">HR SYSTEM</div>
                <div style="font-size:13px; color:#94a3b8;">إشعار رسمي من إدارة الموارد البشرية</div>
            </td>
        </tr>
        <tr>
            <td style="padding:32px 30px; color:#1e293b;">
                <h2 style="font-size:20px; font-weight:bold; color:#0f172a; margin-top:0; margin-bottom:16px;">مرحباً {{ $recipientName }}،</h2>
                
                <div style="background-color:#f8fafc; border-right:4px solid #6366f1; padding:18px; border-radius:8px; margin-bottom:24px; font-size:15px; line-height:1.7; color:#334155;">
                    {{ $messageText }}
                </div>

                @if(!empty($actionedBy))
                <p style="font-size:13px; color:#64748b; margin-bottom:24px;">
                    <strong>تم اتخاذ الإجراء بواسطة:</strong> {{ $actionedBy }}
                </p>
                @endif

                <div style="text-align:center; margin-top:28px; margin-bottom:12px;">
                    <a href="{{ url('/') }}" style="display:inline-block; background:linear-gradient(135deg, #4f46e5, #6366f1); color:#ffffff; padding:12px 32px; border-radius:10px; font-size:14px; font-weight:bold; text-decoration:none; box-shadow:0 4px 12px rgba(79,70,229,0.3);">
                        فتح نظام الموارد البشرية
                    </a>
                </div>
            </td>
        </tr>
        <tr>
            <td style="background-color:#f8fafc; padding:16px 30px; text-align:center; font-size:12px; color:#94a3b8; border-top:1px solid #e2e8f0;">
                هذا البريد مرسل تلقائياً من نظام الموارد البشرية - Gamma
            </td>
        </tr>
    </table>
</body>
</html>
