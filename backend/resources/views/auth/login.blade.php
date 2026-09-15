@extends('layouts.app')

@section('title', 'Login - SessionVault')

@section('content')
<div class="card">
    <h1>Login</h1>
    <p>Use lab credentials to access the application.</p>
    <form method="POST" action="/login">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
@endsection
