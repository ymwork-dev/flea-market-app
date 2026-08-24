@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="auth-container">
    <h1 class="auth-title">ログイン</h1>
    <form action="{{ route('login') }}" method="POST" class="auth-form"novalidate>
        @csrf

        <div class="form-group">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" name="email" id="email" class="form-input" value="{{ old('email') }}">
            @error('email')
                    <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" name="password" id="password" class="form-input">
            @error('password')
                    <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-button">
            <button type="submit" class="btn-submit">ログインする</button>
        </div>
    </form>

    <div class="auth-footer">
        <a href="{{ route('register') }}" class="link-register">会員登録はこちら</a>
    </div>
</div>
@endsection
