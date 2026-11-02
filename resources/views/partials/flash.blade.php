@if (session('status'))
    <div class="alert alert-success" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>{{ session('status') }}</span></div>
@endif
@if (session('warning'))
    <div class="alert" style="background:#FBF3DE;border-color:#D29D29;color:#6B5210" role="alert"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><span>{{ session('warning') }}</span></div>
@endif
@if (session('error'))
    <div class="alert alert-error" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>{{ session('error') }}</span></div>
@endif
