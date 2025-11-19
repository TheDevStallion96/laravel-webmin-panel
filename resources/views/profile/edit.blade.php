<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-2xl">
                    <h2 class="text-lg font-medium mb-4">API Tokens</h2>
                    @if (session('status') === 'token-created' && session('plainTextToken'))
                        <div class="p-4 mb-4 bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200 rounded">
                            <p class="font-medium">Token created. Copy it now; you won't see it again:</p>
                            <code class="block mt-2 break-all">{{ session('plainTextToken') }}</code>
                        </div>
                    @elseif (session('status') === 'token-revoked')
                        <div class="p-4 mb-4 bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200 rounded">Token revoked.</div>
                    @endif

                    <form method="POST" action="{{ route('tokens.store') }}" class="space-y-4 mb-8">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium">Name</label>
                            <input name="name" class="mt-1 w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Abilities</label>
                            <div class="mt-2 space-y-1">
                                @foreach ($allowedAbilities as $ability)
                                    <label class="inline-flex items-center space-x-2 mr-4">
                                        <input type="checkbox" name="abilities[]" value="{{ $ability }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                        <span>{{ $ability }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Allowed abilities for role.</p>
                        </div>
                        <x-primary-button>{{ __('Create Token') }}</x-primary-button>
                    </form>

                    <h3 class="text-md font-semibold mb-2">Existing Tokens</h3>
                    <div class="bg-white dark:bg-gray-900 rounded shadow divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($tokens as $token)
                            <div class="p-4 flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $token->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Abilities: {{ $token->abilities ? implode(', ', (array) $token->abilities) : 'all' }}</div>
                                </div>
                                <form method="POST" action="{{ route('tokens.destroy', $token->id) }}" onsubmit="return confirm('Revoke token?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button>{{ __('Revoke') }}</x-danger-button>
                                </form>
                            </div>
                        @empty
                            <div class="p-4 text-gray-500 dark:text-gray-400">No tokens yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
