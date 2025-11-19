@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-4">Domains for {{ $site->name }}</h1>
    @if(session('status'))
        <div class="p-3 bg-green-100 text-green-800 rounded mb-4">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow rounded p-4 mb-6">
        <form method="POST" action="{{ route('sites.domains.store', $site) }}" class="flex gap-2 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium">Hostname</label>
                <input name="hostname" class="mt-1 w-full border rounded p-2" placeholder="example.com" />
                @error('hostname')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Primary?</label>
                <input type="checkbox" name="is_primary" value="1" class="mt-2" />
            </div>
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Add Domain</button>
        </form>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2">Hostname</th>
                <th class="py-2">Primary</th>
                <th class="py-2">Force HTTPS</th>
                <th class="py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($domains as $domain)
            <tr class="border-b">
                <td class="py-2 font-mono">{{ $domain->hostname }}</td>
                <td class="py-2">@if($domain->is_primary)<span class="text-green-600 font-semibold">Yes</span>@else No @endif</td>
                <td class="py-2">@if($domain->https_forced) <span class="text-blue-600">On</span> @else Off @endif</td>
                <td class="py-2 flex gap-2">
                    @if(!$domain->is_primary)
                    <form method="POST" action="{{ route('sites.domains.primary', [$site,$domain]) }}">
                        @csrf
                        <button class="text-xs bg-gray-200 px-2 py-1 rounded">Make Primary</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('sites.domains.https', [$site,$domain]) }}">
                        @csrf
                        <button class="text-xs bg-yellow-200 px-2 py-1 rounded">Toggle HTTPS</button>
                    </form>
                    <form method="POST" action="{{ route('sites.domains.destroy', [$site,$domain]) }}" onsubmit="return confirm('Delete domain?')">
                        @csrf @method('DELETE')
                        <button class="text-xs bg-red-600 text-white px-2 py-1 rounded">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-8">
        <h2 class="font-semibold mb-2">SSL Certificate</h2>
        @if($certificate)
            <p class="text-sm">Type: {{ $certificate->type }} | CN: {{ $certificate->common_name }} | Expires: {{ $certificate->expires_at->toDateString() }}</p>
        @else
            <p class="text-sm mb-2">No certificate provisioned.</p>
            <form method="POST" action="{{ route('sites.certificates.provision', $site) }}">
                @csrf
                <button class="bg-indigo-600 text-white px-4 py-2 rounded">Provision Let's Encrypt</button>
            </form>
        @endif
    </div>
</div>
@endsection
