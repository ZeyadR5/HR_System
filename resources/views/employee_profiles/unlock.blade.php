@extends('layout')

@section('title', 'قفل ملفات الموظفين')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg text-center p-4 p-md-5" style="border-radius: 20px; background: #ffffff;">
                <div class="mb-3">
                    <div style="width: 76px; height: 76px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; font-size: 2.2rem; box-shadow: 0 4px 15px rgba(37,99,235,0.15);">
                        🔒
                    </div>
                </div>
                <h4 class="fw-bold text-slate-800 mb-2" style="color: #1e293b;">ملفات الموظفين محمية</h4>
                <p class="text-muted small mb-4">يُرجى إدخال كلمة مرور النظام للوصول إلى بيانات وملفات الموظفين</p>

                @if($errors->has('system_password'))
                    <div class="alert alert-danger py-2 px-3 small mb-3 border-0 d-flex align-items-center justify-content-center gap-2" style="border-radius: 10px;">
                        <span>⚠️</span>
                        <span>{{ $errors->first('system_password') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('employee_profiles.unlock_module') }}">
                    @csrf
                    <div class="mb-4">
                        <input type="password" name="system_password" id="unlock_system_password" 
                               class="form-control form-control-lg text-center fw-bold" 
                               required autofocus placeholder="••••••" 
                               style="border-radius: 12px; letter-spacing: 4px; font-size: 1.3rem;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: 0; padding: 12px;">
                        🔓 تأكيد ودخول
                    </button>
                </form>

                <div class="mt-4">
                    <a href="{{ route('home') }}" class="text-decoration-none text-muted small">
                        🔙 العودة للرئيسية
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
