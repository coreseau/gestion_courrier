<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouvel utilisateur</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.utilisateurs.store') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
                @csrf
                @include('admin.users._form')

                <div class="flex items-center gap-4">
                    <x-primary-button>Créer le compte</x-primary-button>
                    <a href="{{ route('admin.utilisateurs.index') }}" class="text-sm text-gray-500 underline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>