<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Site;

class DomainStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $site = $this->route('site');
        return $site && $this->user()->can('create', [\App\Models\Domain::class, $site]);
    }

    public function rules(): array
    {
        /** @var Site $site */
        $site = $this->route('site');
        $id = $site?->id;
        return [
            'hostname' => [
                'required',
                'string',
                'max:255',
                // FQDN or wildcard (single *.) with labels not starting/ending with hyphen
                'regex:/^(\*\.)?([A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,}$/',
                Rule::unique('domains', 'hostname'), // global uniqueness (migration level); conceptually per-site
            ],
            'is_primary' => ['sometimes','boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'hostname.regex' => 'Hostname must be a valid FQDN or wildcard like *.example.com.',
        ];
    }
}
