<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Deployment Logs – {{ $site->name }} #{{ $deployment->id }}</h2>
    </x-slot>
    <div class="max-w-5xl mx-auto p-6 space-y-4">
        <div class="bg-white rounded shadow p-4 space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-sm">Commit: <code>{{ $deployment->commit_hash }}</code> | Branch: {{ $deployment->branch }} | Status: {{ $deployment->status }}</div>
                <form method="get" action="{{ route('sites.deploy.logs', [$site, $deployment]) }}" class="flex items-center gap-2 text-xs">
                    <label>Tail
                        <input type="number" min="0" name="tail" value="{{ $tail }}" class="w-20 rounded border-gray-300 text-xs" />
                    </label>
                    <label class="inline-flex items-center gap-1">
                        <input type="checkbox" name="ansi" value="1" @checked($ansi) /> ANSI
                    </label>
                    <button class="px-2 py-1 bg-gray-800 text-white rounded">Apply</button>
                </form>
            </div>
            <div class="text-xs bg-gray-900 text-green-200 p-3 rounded overflow-x-auto" style="max-height: 500px;">
                {!! $contents_html !!}
            </div>
        </div>
        <a href="{{ route('sites.deploy.history', $site) }}" class="text-xs underline">Back to history</a>
    </div>
</x-app-layout>
