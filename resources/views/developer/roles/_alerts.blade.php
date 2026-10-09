@if(session('success'))
    <div class="dv-alert dv-alert-ok">✅ {{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="dv-alert dv-alert-err">⛔ {{ $errors->first() }}</div>
@endif
