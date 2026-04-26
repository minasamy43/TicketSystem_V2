<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-premium">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="editUserModalLabel">Edit <span id="editUserNameDisplay"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="edit_name" class="form-label small fw-bold text-muted">Full Name</label>
                        <input type="text" class="form-control form-control-premium shadow-none" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label small fw-bold text-muted">Email Address</label>
                        <input type="email" class="form-control form-control-premium shadow-none" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label small fw-bold text-muted">Role</label>
                        <select class="form-select form-control-premium shadow-none" id="edit_role" name="role" required>
                            <option value="0">User</option>
                            <option value="1">Technical</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600;">Cancel</button>
                <button type="submit" form="editUserForm" class="btn-gold-action shadow-none">Commit Changes</button>
            </div>
        </div>
    </div>
</div>
