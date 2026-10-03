@php
    $champ = 'block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    $edition = isset($utilisateur);
    $roleActuel = old('role', $edition ? $utilisateur->roles->pluck('name')->first() : 'collaborateur');
@endphp

<div>
    <x-input-label for="name" value="Nom complet" />
    <input id="name" name="name" type="text" value="{{ old('name', $utilisateur->name ?? '') }}" class="{{ $champ }}" required>
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="email" value="Adresse courriel (identifiant de connexion)" />
    <input id="email" name="email" type="email" value="{{ old('email', $utilisateur->email ?? '') }}" class="{{ $champ }}" required>
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div>
    <x-input-label for="fonction" value="Fonction (facultatif)" />
    <input id="fonction" name="fonction" type="text" value="{{ old('fonction', $utilisateur->fonction ?? '') }}" class="{{ $champ }}">
    <x-input-error :messages="$errors->get('fonction')" class="mt-2" />
</div>

<div>
    <x-input-label for="role" value="Rôle" />
    <select id="role" name="role" class="{{ $champ }}" required>
        <option value="collaborateur" @selected($roleActuel === 'collaborateur')>Collaborateur</option>
        <option value="directeur" @selected($roleActuel === 'directeur')>Directeur</option>
        <option value="admin" @selected($roleActuel === 'admin')>Administrateur</option>
    </select>
    <x-input-error :messages="$errors->get('role')" class="mt-2" />
</div>

@unless ($edition)
    <div>
        <x-input-label for="password" value="Mot de passe initial (facultatif)" />
        <input id="password" name="password" type="text" autocomplete="off" class="{{ $champ }}">
        <p class="mt-1 text-xs text-gray-500">
            Laissez vide pour générer automatiquement un mot de passe temporaire. L'utilisateur devra le changer à sa première connexion.
        </p>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>
@endunless