<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium" style="color: #e5989b">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm " style="color:#5e503f;">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        {{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form
            style="background-color: #f9f1e9;
            border: 1px solid #d5bdaf;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            font-family: 'Cookie', cursive;"
            method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium" style="color: #5f4842">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm" style="color:#5e503f; text-align:center">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label style="color: #5f4842" for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    style="border: 1px solid #d5bdaf;border-radius: 5px;
                font-family: 'Cookie', cursive;    background-color: #f0eae2;
                color: #5e503f;"
                    id="password" name="password" type="password" class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}" />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')"
                    style="background-color: #e9cbba; color: white; border: 1px solid #c7b1a4;">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3" style="background-color: #b9a396; color: white;">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
