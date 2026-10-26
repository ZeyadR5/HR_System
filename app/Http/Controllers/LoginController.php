<?php


namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AuditLog;

class LoginController extends Controller
{
    function login(){
        return view('login');
    }

function login_post(Request $request){
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    $credentials = $request->only('email', 'password');

    if(Auth::attempt($credentials)){
        $request->session()->regenerate();
        
        AuditLog::log(Auth::user()->name, 'Login', 'Auth', 'System');

        if(Auth::user()->role === 'admin'){
            return redirect()->route('home');
        } else {
            return redirect()->route('home');
        }
    }

    // تسجيل محاولة الدخول الفاشلة
    $attemptedEmail = $request->email;
    AuditLog::log($attemptedEmail, 'Failed Login', 'Auth', 'System', 'محاولة تسجيل دخول فاشلة للإيميل: ' . $attemptedEmail);

    return back()->withErrors(['email' => 'Invalid credentials.']);
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