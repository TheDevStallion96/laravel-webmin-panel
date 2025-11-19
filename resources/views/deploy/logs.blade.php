<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Deployment Logs – {{ $site->name }} #{{ $deployment->id }}</h2>
    </x-slot>
    <div class="max-w-5xl mx-auto p-6 space-y-4">
        <div class="bg-white rounded shadow p-4">
            <div class="text-sm mb-2">Commit: <code>{{ $deployment->commit_hash }}</code> | Branch: {{ $deployment->branch }} | Status: {{ $deployment->status }}</div>
            <pre class="text-xs bg-gray-900 text-green-200 p-3 rounded overflow-x-auto" style="max-height: 500px;">{{ $contents }}</pre>
        </div>
        <a href="{{ route('sites.deploy.history', $site) }}" class="text-xs underline">Back to history</a>
    </div>
</x-app-layout>
