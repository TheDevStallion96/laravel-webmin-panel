<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Databases for {{ $site->name }}</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-8">
        @if(session('status'))
            <div class="p-3 rounded bg-green-100 text-green-800 text-sm">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold mb-3">Create Database</h3>
            <form method="POST" action="{{ route('sites.databases.store',$site) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Engine</label>
                    <select name="engine" class="border rounded w-40 p-1 text-sm">
                        <option value="mysql">MySQL</option>
                        <option value="pgsql">Postgres</option>
                    </select>
                    @error('engine')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input name="name" class="border rounded w-72 p-1 text-sm" placeholder="app_db" />
                    @error('name')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                </div>
                <button class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">Create</button>
            </form>
        </div>

        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold mb-3 flex items-center justify-between">Existing Databases <span class="text-xs text-gray-500">({{ $databases->count() }})</span></h3>
            <div class="divide-y">
                @forelse($databases as $db)
                    <div class="py-3 flex items-center justify-between text-sm">
                        <div>
                            <div class="font-medium">{{ $db->name }} <span class="text-gray-500">({{ strtoupper($db->engine) }})</span></div>
                            <div class="text-xs text-gray-600">User: {{ $db->username }} • Host: {{ $db->host }}:{{ $db->port }} • Password: <code>{{ $db->password }}</code></div>
                        </div>
                        <form method="POST" action="{{ route('sites.databases.destroy', [$site,$db]) }}" onsubmit="return confirm('Delete database {{ $db->name }}?');">
                            @csrf @method('DELETE')
                            <button class="px-2 py-1 border rounded text-xs">Delete</button>
                        </form>
                    </div>
                @empty
                    <div class="py-6 text-center text-sm text-gray-500">No databases created yet.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold mb-2">Current .env DB Settings</h3>
            <pre class="text-xs bg-gray-50 p-3 rounded overflow-x-auto">@foreach($site->environment ?? [] as $k=>$v)@if(str_starts_with(strtolower($k),'db_')){{ strtoupper($k) }}={{ $v }}
@endif @endforeach</pre>
        </div>
    </div>
</x-app-layout>
