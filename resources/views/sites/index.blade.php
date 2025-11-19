<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Sites</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Sites</h1>
        @can('manage-site')
        <a href="{{ route('sites.create') }}" class="px-3 py-2 bg-indigo-600 text-white rounded">New Site</a>
        @endcan
    </div>
    <div class="bg-white rounded shadow divide-y">
        @forelse ($sites as $site)
            <a href="{{ route('sites.show', $site) }}" class="p-4 flex justify-between hover:bg-gray-50">
                <div>
                    <div class="font-medium">{{ $site->name }}</div>
                    <div class="text-xs text-gray-500">{{ $site->slug }} — {{ $site->status->value }}</div>
                </div>
                <div class="text-sm text-gray-500">PHP {{ $site->php_version }}</div>
            </a>
        @empty
            <div class="p-4 text-gray-500">No sites yet.</div>
        @endforelse
    </div>
    </div>
</x-app-layout>
