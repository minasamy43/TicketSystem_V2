<div class="toast-container">
    @if(session('success'))
        <div class="custom-toast toast-success" id="toast-success">
            <div class="toast-icon">
                <i class="fa-solid fa-check"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">Success!</div>
                <div class="toast-message">{{ session('success') }}</div>
            </div>
            <button class="toast-close" onclick="dismissToast('toast-success')">&times;</button>
            <div class="toast-progress">
                <div class="toast-progress-bar"></div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="custom-toast toast-error" id="toast-error">
            <div class="toast-icon">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">Error!</div>
                <div class="toast-message">{{ session('error') }}</div>
            </div>
            <button class="toast-close" onclick="dismissToast('toast-error')">&times;</button>
            <div class="toast-progress">
                <div class="toast-progress-bar"></div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="custom-toast toast-error" id="toast-validation">
            <div class="toast-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">Validation Error</div>
                <div class="toast-message">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button class="toast-close" onclick="dismissToast('toast-validation')">&times;</button>
            <div class="toast-progress">
                <div class="toast-progress-bar"></div>
            </div>
        </div>
    @endif
</div>

<script>
    function dismissToast(id) {
        const toast = document.getElementById(id);
        if (toast) {
            toast.classList.add('hide');
            setTimeout(() => {
                toast.remove();
            }, 400);
        }
    }

    // Auto-dismiss after 5 seconds
    document.addEventListener('DOMContentLoaded', function () {
        const toasts = document.querySelectorAll('.custom-toast');
        toasts.forEach(toast => {
            setTimeout(() => {
                dismissToast(toast.id);
            }, 3000); // continue it in the layout.css file at line 957
        });
    });
</script>