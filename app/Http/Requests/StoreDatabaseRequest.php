<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDatabaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // gate enforced in controller
    }

    public function rules(): array
    {
        return [
            'engine' => ['required', Rule::in(['mysql','pgsql'])],
            'name' => ['required','alpha_dash','min:3','max:48'],
        ];
    }
}
