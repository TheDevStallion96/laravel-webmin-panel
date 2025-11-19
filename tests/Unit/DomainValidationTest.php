<?php

use App\Http\Requests\DomainStoreRequest;

function hostnamePattern(): string
{
    $rules = (new DomainStoreRequest())->rules()['hostname'];
    foreach ($rules as $rule) {
        if (is_string($rule) && str_starts_with($rule, 'regex:')) {
            return substr($rule, 6);
        }
    }
    throw new RuntimeException('Regex rule not found');
}

test('domain validation accepts fqdn', function () {
    $pattern = hostnamePattern();
    expect(preg_match($pattern, 'example.com'))->toBe(1);
});

test('domain validation accepts wildcard', function () {
    $pattern = hostnamePattern();
    expect(preg_match($pattern, '*.example.org'))->toBe(1);
});

test('domain validation rejects invalid hostnames', function () {
    $pattern = hostnamePattern();
    $invalid = ['example', '-bad.example.com', 'bad..example.com', 'exa_mple.com', 'bad-.example.com'];
    foreach ($invalid as $host) {
        expect(preg_match($pattern, $host))->toBe(0);
    }
});
