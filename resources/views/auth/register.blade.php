<x-guest-layout>
    <style>
        h1 {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            font-family: Sans-Serif;
        }

        .form-group {
            margin-top: 10px;
        }

        .text-input {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            padding: 10px;
            border: 2px solid #A50028;
            border-radius: 30px;
        }

        .info-input {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            margin-left: 5%;
            padding: 10px;
            border: 2px solid #A50028;
            border-radius: 30px;
            background-color: white;
        }

        .input-error {
            color: #949494;
            margin-top: 4px;
        }

        .button {
            background-color: #A50028;
            display: flex;
            font-size: 14px;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 10px 30px;
            border-radius: 30px;
            margin-top: 20px;
            margin-bottom: 30px;
            width: 90%;
            margin-left: 5%;
            font-family: sans-serif;
        }

        .button:hover {
            background-color: #c91440;
        }

        .link {
            color: #A50028;
            text-decoration: none;
            margin-top: 16px;
        }

        .link:hover {
            text-decoration: underline;
            color: #c91440;
        }

        .name-group {
            display: flex;
            gap: 2px;
            margin-top: 16px;
            margin-bottom: 25px;
        }
    </style>

    <h1>Sign up</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class ="form-group">
            <x-input-label for="name" class="input-label" />
            <x-text-input id="name" class="info-input" type="text" name="name" :value="old('name')" required
                autofocus autocomplete="name" placeholder="Name" />
            <x-input-error :messages="$errors->get('name')" class="input-error" />
        </div>

        <!-- Email Address -->
        <div class="form-group">
            <x-input-label for="email" class="input-label"></x-input-label>
            <x-text-input id="email" class="info-input" type="email" name="email" value="{{ old('email') }}"
                required autocomplete="username" placeholder="Email" />
            <x-input-error :messages="$errors->get('email')" class="input-error" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <x-input-label for="password" class="input-label"></x-input-label>
            <x-text-input id="password" class="info-input" type="password" name="password" required
                autocomplete="new-password" placeholder="Password" />
            <x-input-error :messages="$errors->get('password')" class="input-error" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <x-input-label for="password_confirmation" class="input-label"></x-input-label>
            <x-text-input id="password_confirmation" class="info-input" type="password" name="password_confirmation"
                required autocomplete="new-password" placeholder="Confirm Password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="input-error" />
        </div>

        <div>
            <div class="flex items-center justify-center">
                <a class="link" href="{{ route('login') }}">
                    {{ __('Already have an account?') }}
                </a>
            </div>

            <div>
                <x-primary-button class="button">
                    {{ __('Sign Up') }}
                </x-primary-button>
            </div>
        </div>
    </form>
</x-guest-layout>
