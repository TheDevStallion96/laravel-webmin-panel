@props(['name' => 'php_version', 'versions' => ['8.1','8.2','8.3'], 'selected' => '8.2'])
<select name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full border rounded p-2']) }}>
 @foreach($versions as $v)
  <option value="{{ $v }}" @selected($v===$selected)>{{ $v }}</option>
 @endforeach
</select>
