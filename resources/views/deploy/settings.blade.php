<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Deploy Settings – {{ $site->name }}</h2>
    </x-slot>
    <div class="max-w-5xl mx-auto p-6 space-y-6">
        @if(session('status'))<div class="p-3 bg-green-100 text-green-800 text-sm rounded">{{ session('status') }}</div>@endif
        <div class="bg-white rounded shadow p-4 space-y-2">
            <h3 class="font-semibold">Repository</h3>
            <p class="text-sm">URL: <code>{{ $site->repo_url ?? 'not set' }}</code></p>
            <p class="text-sm">Branch: <code>{{ $site->default_branch }}</code></p>
            <p class="text-sm">Strategy: <code>{{ $site->deploy_strategy }}</code></p>
        </div>
        <div class="bg-white rounded shadow p-4">
            <form method="POST" action="{{ route('sites.deploy.run', $site) }}" onsubmit="return confirm('Start deployment?');">
                @csrf
                <button class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">Deploy Now</button>
            </form>
        </div>
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold mb-2">Recent Deployments</h3>
            <ul class="text-sm space-y-1">
                @forelse($deployments as $d)
                    <li>
                        <a class="underline" href="{{ route('sites.deploy.logs', [$site,$d]) }}">#{{ $d->id }}</a>
                        – {{ $d->status }} – {{ $d->commit_hash }}
                        @if($d->finished_at) <span class="text-xs text-gray-500">({{ $d->finished_at->diffForHumans() }})</span>@endif
                    </li>
                @empty
                    <li class="text-gray-500">No deployments yet.</li>
                @endforelse
            </ul>
            <a class="text-xs text-indigo-600 underline" href="{{ route('sites.deploy.history', $site) }}">Full history</a>
        </div>
    </div>
</x-app-layout>
