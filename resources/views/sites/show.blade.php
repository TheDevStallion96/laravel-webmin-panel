<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Site Details</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto p-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ $site->name }}</h1>
            <div class="text-sm text-gray-500">Slug: {{ $site->slug }} • PHP {{ $site->php_version }} • Status: {{ $site->status->value }}</div>
        </div>
        <div class="space-x-2">
            @can('manage-site')
                <form method="POST" action="{{ route('sites.pause', $site) }}" class="inline">
                    @csrf
                    <button class="px-3 py-1 border rounded" @disabled($site->status->value==='paused')>Pause</button>
                </form>
                <form method="POST" action="{{ route('sites.resume', $site) }}" class="inline">
                    @csrf
                    <button class="px-3 py-1 border rounded" @disabled($site->status->value==='active')>Resume</button>
                </form>
                <form method="POST" action="{{ route('sites.destroy', ['site' => $site, 'redirect' => 1]) }}" class="inline" onsubmit="return confirm('Delete this site?');">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1 bg-red-600 text-white rounded">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="mt-6">
        <h2 class="font-semibold mb-2">Settings</h2>
        <div class="bg-white rounded shadow p-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Root Path:</span> {{ $site->root_path }}</div>
                <div><span class="text-gray-500">Public Dir:</span> {{ $site->public_dir }}</div>
                <div><span class="text-gray-500">Default Branch:</span> {{ $site->default_branch }}</div>
                <div><span class="text-gray-500">Deploy Strategy:</span> {{ $site->deploy_strategy->value }}</div>
            </div>
        </div>
    </div>

    @can('manage-site')
    <div class="mt-10" x-data="webserverRebuild()">
        <h2 class="font-semibold mb-2 flex items-center justify-between">
            <span>Web Server Config</span>
            <div class="space-x-2">
                <button @click="dryRun(true)" type="button" class="px-3 py-1 border rounded text-sm" :disabled="loading">Dry-run Diff</button>
                <button @click="dryRun(false)" type="button" class="px-3 py-1 border rounded text-sm" :disabled="loading">Dry-run (Full)</button>
                <button @click="rebuild()" type="button" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm" :disabled="loading">Rebuild</button>
            </div>
        </h2>
        <template x-if="error">
            <div class="mb-4 text-sm text-red-600" x-text="error"></div>
        </template>
        <template x-if="result">
            <x-webserver-diff :vhost="null" :pool="null" x-bind:vhost="result.vhost" x-bind:pool="result.pool" />
        </template>
        <div x-show="loading" class="text-sm text-gray-500 mt-4">Processing...</div>
    </div>
    @endcan
    <script>
        function webserverRebuild() {
            return {
                loading: false,
                result: null,
                error: null,
                async dryRun(withDiff) {
                    this.loading = true; this.error = null; this.result = null;
                    try {
                        const url = `{{ route('sites.webserver.rebuild', $site) }}?dry=1${withDiff?'&diff=1':''}`;
                        const res = await fetch(url, {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}});
                        if(!res.ok) throw new Error('Request failed');
                        this.result = await res.json();
                    } catch(e) { this.error = e.message; } finally { this.loading = false; }
                },
                async rebuild() {
                    this.loading = true; this.error = null;
                    try {
                        const res = await fetch(`{{ route('sites.webserver.rebuild', $site) }}`, {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}});
                        if(res.redirected) { window.location = res.url; return; }
                        if(!res.ok) throw new Error('Rebuild failed');
                    } catch(e) { this.error = e.message; } finally { this.loading = false; }
                }
            }
        }
    </script>
    </div>
</x-app-layout>
