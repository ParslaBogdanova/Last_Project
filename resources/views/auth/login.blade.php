<x-guest-layout>
    <style>
        h1 {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            font-family: Sans-Serif;
        }

        .form-group {
            margin-top: 10px;
        }

        .info-input {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            padding: 10px;
            border: 2px solid #A50028;
            border-radius: 30px;
            margin-left: 5%;
            background-color: white;
        }

        .input-error {
            color: #949494;
            margin-top: 4px;
        }

        .button {
            background-color: #A50028;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            margin-top: 20px;
            width: 90%;
            margin-left: 5%;
            font-family: sans-serif;
            margin-bottom: 30px;
            justify-content: center;
        }

        .button:hover {
            background-color: #c91440;
        }

        .link {
            display: flex;
            justify-content: right;
            align-items: center;
            color: #A50028;
            margin-top: 16px;
            justify-content: right;
        }

        .account {
            margin-right: 5%;
            margin-top: -16px;
            font-family: Sans-Serif;
        }

        .forgot-password {
            margin-right: 5%;
            font-family: Sans-Serif;
        }

        .link:hover {
            text-decoration: underline;
        }

        .remember-group {
            display: flex;
            align-items: center;
            margin-left: 5%;
            width: 90%;
            color: #b5b4b3;
            font-family: Sans-Serif;
        }

        .remember-checkbox {
            border-color: #b5b4b3;
        }
    </style>

    <h1>Login</h1>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class = "form-group">
            <x-input-label for="email" />
            <x-text-input id="email" class="info-input" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" placeholder="Username" />
            <x-input-error :messages="$errors->get('email')" class="input-error" />
        </div>

        <!-- Password -->
        <div class = "form-group">
            <x-input-label for="password" class="input-label" />
            <x-text-input id="password" class="info-input" type="password" name="password" required
                autocomplete="current-password" placeholder="Password" />

            <x-input-error :messages="$errors->get('password')" class="input-error" />
        </div>

        <!-- Remember Me -->
        <div class="remember-group mt-4">
            <label for="remember_me">
                <input id="remember_me" type="checkbox" class="rounded remember-checkbox" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="forgot-password">
            @if (Route::has('password.request'))
                <a class="link" href="{{ route('password.request') }}">
                    {{ __('Forgot password') }}
                </a>
            @endif
        </div>
        <div>
            <div class="account">
                <a class="link" href="{{ route('register') }}">
                    {{ __('Dont have an account?') }}
                </a>
            </div>
            <x-primary-button class="button">
                {{ __('Log in') }}
            </x-primary-button>
    </form>
</x-guest-layout>
