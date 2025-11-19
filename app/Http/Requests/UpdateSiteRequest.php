<?php

namespace App\Http\Requests;

use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Site $site */
        $site = $this->route('site');
        return $this->user()?->can('update', $site) ?? false;
    }

    public function rules(): array
    {
        /** @var Site $site */
        $site = $this->route('site');
        return [
            'name' => ['sometimes','string','max:255'],
            'slug' => ['sometimes','string','max:255', Rule::unique('sites','slug')->ignore($site?->id)],
            // Enforce uniqueness when updating root_path; ignore current record
            'root_path' => ['sometimes','string','max:1024', Rule::unique('sites','root_path')->ignore($site?->id)],
            'public_dir' => ['sometimes','string','max:255'],
            'php_version' => ['sometimes', Rule::in(['8.1','8.2','8.3'])],
            'repo_url' => ['nullable','url'],
            'default_branch' => ['sometimes','string','max:255'],
            'environment' => ['sometimes','array'],
            'deploy_strategy' => ['sometimes', Rule::in(['basic','zero_downtime'])],
        ];
    }
}
