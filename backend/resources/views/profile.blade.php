@extends('layouts.app')

@section('title', 'Profile - SessionVault')

@section('content')
<div class="card">
    <h1>Profile</h1>
    <form method="POST" action="/profile">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ auth()->user()->name }}" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ auth()->user()->email }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
@endsection
