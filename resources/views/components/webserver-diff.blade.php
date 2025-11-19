@props([
    'vhost' => null,
    'pool' => null,
])

<div class="space-y-6" x-data>
    @if($vhost)
        <div class="border rounded-md bg-white shadow">
            <div class="px-4 py-2 flex items-center justify-between border-b">
                <h3 class="font-semibold">Nginx Vhost (Dry-run)</h3>
                <span class="text-xs px-2 py-1 rounded {{ $vhost['changed'] ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-700' }}">{{ $vhost['changed'] ? 'Changed' : 'Unchanged' }}</span>
            </div>
            <div class="p-4">
                @if(isset($vhost['diff']))
                    <pre class="text-xs font-mono overflow-x-auto leading-snug">
@php($lines = explode("\n", $vhost['diff']))
@foreach($lines as $line)
@php($class = str_starts_with($line,'+') ? 'text-green-600 bg-green-50' : (str_starts_with($line,'-') ? 'text-red-600 bg-red-50' : 'text-gray-700'))
<span class="block {{$class}}">{{ $line === '' ? ' ' : $line }}</span>
@endforeach
                    </pre>
                @else
                    <pre class="text-xs font-mono overflow-x-auto leading-snug">{{ $vhost['config'] }}</pre>
                @endif
            </div>
        </div>
    @endif

    @if($pool)
        <div class="border rounded-md bg-white shadow">
            <div class="px-4 py-2 flex items-center justify-between border-b">
                <h3 class="font-semibold">PHP-FPM Pool (Dry-run)</h3>
                <span class="text-xs px-2 py-1 rounded {{ $pool['changed'] ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-700' }}">{{ $pool['changed'] ? 'Changed' : 'Unchanged' }}</span>
            </div>
            <div class="p-4">
                @if(isset($pool['diff']))
                    <pre class="text-xs font-mono overflow-x-auto leading-snug">
@php($lines = explode("\n", $pool['diff']))
@foreach($lines as $line)
@php($class = str_starts_with($line,'+') ? 'text-green-600 bg-green-50' : (str_starts_with($line,'-') ? 'text-red-600 bg-red-50' : 'text-gray-700'))
<span class="block {{$class}}">{{ $line === '' ? ' ' : $line }}</span>
@endforeach
                    </pre>
                @else
                    <pre class="text-xs font-mono overflow-x-auto leading-snug">{{ $pool['config'] }}</pre>
                @endif
            </div>
        </div>
    @endif
</div>
