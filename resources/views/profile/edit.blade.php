<x-app-layout>
    <style>
        .profile-wrapper {
            padding: 3rem 0;
            background-color: #f9f1e9;
            border: 1px solid #d5bdaf;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            font-family: 'Cookie', cursive;
            min-height: 100vh;
        }

        .profile-container {
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .profile-card {
            background-color: #f9f1e9;
            border: 1px solid #d5bdaf;
            font-family: 'Cookie', cursive;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .profile-form {
            max-width: 600px;
        }
    </style>
    <div class="profile-wrapper">
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-form">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-form">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-form">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
