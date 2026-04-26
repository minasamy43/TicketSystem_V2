@extends('layouts.app')
@section('content')

    <div class="container mt-4">
        <h3>Create New Ticket</h3>
        <div class="card mt-3">
            <div class="card-body">

                <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" rows="5" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Images (Optional)</label>
                        <div class="images-container">
                            <div class="image-input mb-2">
                                <input type="file" name="images[]" class="form-control" accept="image/*">
                                <div class="image-preview-wrap" style="display:none; margin-top:8px;"></div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addImage">Add Another
                            Image</button>
                        <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG, GIF (Max 2MB each)</small>
                    </div>

                    <style>
                        .image-preview-wrap {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 10px;
                        }

                        .img-thumb-box {
                            position: relative;
                            width: 90px;
                            height: 90px;
                            border-radius: 8px;
                            overflow: hidden;
                            border: 1px solid #dee2e6;
                            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
                        }

                        .img-thumb-box img {
                            width: 100%;
                            height: 100%;
                            object-fit: cover;
                            display: block;
                        }
                    </style>

                    <button class="btn btn-success" id="submitTicketBtn">Submit Ticket</button>

                </form>

            </div>
        </div>

    </div>

    <script>
        // Prevent double / triple submission
        document.querySelector('form[action="{{ route('tickets.store') }}"]').addEventListener('submit', function () {
            const btn = document.getElementById('submitTicketBtn');
            btn.disabled = true;
            btn.textContent = 'Submitting…';
        });
        function attachPreview(fileInput) {
            fileInput.addEventListener('change', function () {
                const wrap = this.closest('.image-input').querySelector('.image-preview-wrap');
                wrap.innerHTML = '';
                if (!this.files || !this.files.length) {
                    wrap.style.display = 'none';
                    return;
                }
                wrap.style.display = 'flex';
                Array.from(this.files).forEach(function (file) {
                    if (!file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const box = document.createElement('div');
                        box.className = 'img-thumb-box';
                        box.innerHTML = '<img src="' + e.target.result + '" alt="preview">';
                        wrap.appendChild(box);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Attach preview to the initial input
            document.querySelectorAll('.image-input input[type="file"]').forEach(attachPreview);

            document.getElementById('addImage').addEventListener('click', function () {
                const imagesContainer = document.querySelector('.images-container');
                const newImageInput = document.createElement('div');
                newImageInput.className = 'image-input mb-2';
                newImageInput.innerHTML =
                    '<input type="file" name="images[]" class="form-control" accept="image/*">'
                    + ' <button type="button" class="btn btn-sm btn-outline-danger remove-image">Remove</button>'
                    + '<div class="image-preview-wrap" style="display:none; margin-top:8px;"></div>';
                imagesContainer.appendChild(newImageInput);
                attachPreview(newImageInput.querySelector('input[type="file"]'));
            });

            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-image')) {
                    e.target.closest('.image-input').remove();
                }
            });
        });
    </script>
@endsection