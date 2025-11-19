<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Create Site</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto p-6" x-data="{ step: 1 }">
        <h1 class="text-2xl font-semibold mb-4">Create Site</h1>
    <form method="POST" action="{{ route('sites.store') }}" class="space-y-6">
        @csrf

        <div x-show="step === 1">
            <h2 class="text-lg font-medium mb-2">Basics</h2>
            <x-input-label>Name</x-input-label>
            <x-text-input name="name" class="w-full" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />

            <x-input-label class="mt-4">Slug</x-input-label>
            <x-text-input name="slug" class="w-full" required />
            <x-input-error :messages="$errors->get('slug')" class="mt-1" />

            <x-input-label class="mt-4">Root Path</x-input-label>
            <x-path-picker name="root_path" class="w-full" placeholder="/var/www/example" required />
            <x-input-error :messages="$errors->get('root_path')" class="mt-1" />
        </div>

        <div x-show="step === 2">
            <h2 class="text-lg font-medium mb-2">PHP</h2>
            <x-input-label>Public Directory</x-input-label>
            <x-text-input name="public_dir" class="w-full" value="public" />
            <x-input-error :messages="$errors->get('public_dir')" class="mt-1" />

            <x-input-label class="mt-4">PHP Version</x-input-label>
            <x-php-version-selector name="php_version" />
            <x-input-error :messages="$errors->get('php_version')" class="mt-1" />
        </div>

        <div x-show="step === 3">
            <h2 class="text-lg font-medium mb-2">Repository</h2>
            <x-input-label>Repository URL (optional)</x-input-label>
            <x-text-input name="repo_url" class="w-full" placeholder="https://github.com/org/repo" />
            <x-input-error :messages="$errors->get('repo_url')" class="mt-1" />

            <x-input-label class="mt-4">Default Branch</x-input-label>
            <x-text-input name="default_branch" class="w-full" value="main" />
            <x-input-error :messages="$errors->get('default_branch')" class="mt-1" />
        </div>

        <div x-show="step === 4">
            <h2 class="text-lg font-medium mb-2">Environment</h2>
            <p class="text-sm text-gray-500 mb-2">Add key/value pairs for .env (you can edit later).</p>
            <x-env-editor />
            <x-input-error :messages="$errors->get('environment')" class="mt-1" />
            <x-input-label class="mt-4">Deploy Strategy</x-input-label>
            <select name="deploy_strategy" class="w-full border rounded p-2">
                <option value="basic" selected>Basic</option>
                <option value="zero_downtime">Zero downtime</option>
            </select>
            <x-input-error :messages="$errors->get('deploy_strategy')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between">
            <button type="button" class="px-3 py-2 border rounded" x-bind:disabled="step===1" x-on:click="step--">Back</button>
            <div class="text-sm">Step <span x-text="step"></span> / 4</div>
            <button type="button" class="px-3 py-2 border rounded" x-show="step<4" x-on:click="step++">Next</button>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded" x-show="step===4">Create</button>
        </div>
        </form>
    </div>
</x-app-layout>
