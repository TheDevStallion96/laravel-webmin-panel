<?php

namespace App\Http\Requests;

use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Site::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'slug' => ['required','string','max:255', Rule::unique('sites','slug')],
            // Root path must be unique so two sites cannot manage the same directory tree
            'root_path' => ['required','string','max:1024', Rule::unique('sites','root_path')],
            'public_dir' => ['nullable','string','max:255'],
            'php_version' => ['required', Rule::in(['8.1','8.2','8.3'])],
            'repo_url' => ['nullable','url'],
            'default_branch' => ['required','string','max:255'],
            'environment' => ['nullable','array'],
            'deploy_strategy' => ['required', Rule::in(['basic','zero_downtime'])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        if (!isset($data['public_dir']) || $data['public_dir'] === null) {
            $data['public_dir'] = 'public';
        }
        $data['status'] = 'active';
        $data['environment'] = $data['environment'] ?? [];
        return $data;
    }
}
