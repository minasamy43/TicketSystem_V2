@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Add New User</h2>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
            @error('password')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role" required>
                <option value="0" {{ old('role') == '0' ? 'selected' : '' }}>User</option>
                <option value="1" {{ old('role') == '1' ? 'selected' : '' }}>Technical</option>
            </select>
            @error('role')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="btn btn-success">Create User</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
