<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * يمنع تكرار نفس الطلب (POST) لو اتبعت أكتر من مرة في ثواني قليلة
 * (دوسة مزدوجة على الزرار / ضعف نت / refresh أثناء الإرسال).
 */
class PreventDuplicateSubmission
{
    /** مدة القفل بالثواني */
    private const LOCK_SECONDS = 8;

    /** مسارات مستثناة (بتتكرر بشكل طبيعي) */
    private const EXCEPT = ['login', 'logout', 'poll_notifications', 'api/*'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST') || $request->is(self::EXCEPT)) {
            return $next($request);
        }

        $actor = $request->user()?->id ?? $request->ip();
        $key = 'dup_submit:' . sha1(
            $actor . '|' . $request->path() . '|' . json_encode($request->except('_token'))
        );

        // Cache::add ذرّية: ترجع false لو المفتاح موجود بالفعل
        if (! Cache::add($key, true, self::LOCK_SECONDS)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'تم استلام طلبك بالفعل، برجاء الانتظار.',
                ], 429);
            }

            return redirect()->back()->with('alert_error', '⚠️ تم استلام طلبك بالفعل، لا داعي للضغط أكثر من مرة.');
        }

        return $next($request);
    }
}
