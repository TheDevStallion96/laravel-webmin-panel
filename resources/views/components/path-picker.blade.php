@props(['name','placeholder' => '/var/www/example'])
<input {{ $attributes->merge(['class' => 'border rounded p-2']) }} name="{{ $name }}" placeholder="{{ $placeholder }}" x-data x-on:focus="$el.select()" />
