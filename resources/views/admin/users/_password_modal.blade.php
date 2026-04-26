<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-premium">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="changePasswordModalLabel">Reset Password: 
                    <span id="userName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="changePasswordForm" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="password" class="form-label small fw-bold text-muted">New Password</label>
                        <input type="password" class="form-control form-control-premium shadow-none" id="password"
                            name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label small fw-bold text-muted">Confirm New
                            Password</label>
                        <input type="password" class="form-control form-control-premium shadow-none"
                            id="password_confirmation" name="password_confirmation" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                    style="border-radius: 10px; font-weight: 600;">Cancel</button>
                <button type="submit" form="changePasswordForm" class="btn-gold-action shadow-none"
                    style="background: #1a5131;">Verify & Update</button>
            </div>
        </div>
    </div>
</div>