<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Pour des raisons de sécurité, vous devez choisir un nouveau mot de passe avant de continuer.
    </div>

    <form method="POST" action="{{ route('mot-de-passe.update') }}">
        @csrf

        <div>
            <x-input-label for="password" value="Nouveau mot de passe" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                          name="password_confirmation" required autocomplete="new-password" />
        </div>

        <div class="mt-6">
            <x-primary-button>Enregistrer</x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-sm text-gray-500 underline">Se déconnecter</button>
    </form>
</x-guest-layout>