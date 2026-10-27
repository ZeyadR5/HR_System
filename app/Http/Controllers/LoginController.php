<?php


namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\AuditLog;

class LoginController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function login_post(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Rate Limiting: 5 attempts per minute per email + IP
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            AuditLog::log(
                $request->input('email', 'Guest'),
                'Rate Limit Exceeded',
                'Auth',
                'System',
                "تم حظر محاولات الدخول مؤقتاً لتجاوز الحد المسموح (المتبقي: {$seconds} ثانية) من IP: " . $request->ip()
            );

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => "تم حظر محاولات الدخول مؤقتاً لكثرة المحاولات الخاطئة. يرجى الانتظار {$seconds} ثانية ثم المحاولة مجدداً."
                ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Clear rate limiter upon successful login
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();
            
            AuditLog::log(Auth::user()->name, 'Login', 'Auth', 'System');

            return redirect()->route('home');
        }

        // Increment rate limiter on failure
        RateLimiter::hit($throttleKey, 60);

        // تسجيل محاولة الدخول الفاشلة
        $attemptedEmail = $request->email;
        AuditLog::log($attemptedEmail, 'Failed Login', 'Auth', 'System', 'محاولة تسجيل دخول فاشلة للإيميل: ' . $attemptedEmail);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.']);
    }

public function reset_password_index()
{
    if (!Auth::user()->can('employees.reset_password')) {
        abort(403, 'غير مصرح لك باستعادة كلمة السر');
    }
    $users = User::orderBy('name')->get();
    return view('admin_reset_password', compact('users'));
}

public function reset_password_post(Request $request)
{
    if (!Auth::user()->can('employees.reset_password')) {
        abort(403, 'غير مصرح لك باستعادة كلمة السر');
    }
    $request->validate([
        'user_id'  => 'required|exists:users,id',
        'password' => 'required|string|min:6|confirmed',
    ]);

    $u = User::findOrFail($request->user_id);
    $u->update([
        'password' => Hash::make($request->password),
    ]);

    AuditLog::log(Auth::user()->name, 'Changed Password', 'Auth', $u->name);

    return redirect()->back()->with('success', 'Password reset successfully!');
}

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::log(Auth::user()->name, 'Logout', 'Auth', 'System');
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }


}

// change password
// $user = App\Models\User::where('email', 'email@email.com')->first();
// $user->password = Hash::make('newpass123');
// $user->save();