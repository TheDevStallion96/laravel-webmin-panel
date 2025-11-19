<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Deployment History – {{ $site->name }}</h2>
    </x-slot>
    <div class="max-w-5xl mx-auto p-6">
        @if(session('status'))<div class="p-3 bg-green-100 text-green-800 text-sm rounded mb-4">{{ session('status') }}</div>@endif
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-2">ID</th>
                    <th class="py-2">Commit</th>
                    <th class="py-2">Branch</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Started</th>
                    <th class="py-2">Finished</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($deployments as $d)
                <tr class="border-b">
                    <td class="py-1"><a class="underline" href="{{ route('sites.deploy.logs', [$site,$d]) }}">#{{ $d->id }}</a></td>
                    <td class="py-1 font-mono">{{ $d->commit_hash }}</td>
                    <td class="py-1">{{ $d->branch }}</td>
                    <td class="py-1">{{ $d->status }}</td>
                    <td class="py-1">{{ $d->started_at?->toDateTimeString() }}</td>
                    <td class="py-1">{{ $d->finished_at?->toDateTimeString() }}</td>
                    <td class="py-1">
                        @if($d->status === 'completed')
                        <form method="POST" action="{{ route('sites.deploy.rollback', [$site,$d]) }}" class="inline" onsubmit="return confirm('Rollback to deployment #{{ $d->id }}?');">
                            @csrf
                            <button class="text-xs px-2 py-1 bg-yellow-200 rounded">Rollback</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $deployments->links() }}</div>
        <div class="mt-6">
            <a href="{{ route('sites.deploy.settings', $site) }}" class="text-xs underline">Back to settings</a>
        </div>
    </div>
</x-app-layout>
