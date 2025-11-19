<?php

use App\Services\TemplateRenderer;

it('renders stub variables', function () {
    $renderer = new TemplateRenderer();
    $stub = sys_get_temp_dir().'/stub.test';
    file_put_contents($stub, 'Hello {{ name }} and domains: {{ domains }}');
    $out = $renderer->render($stub, ['name' => 'World', 'domains' => ['a.com','b.com']]);
    expect($out)->toContain('Hello World')
        ->and($out)->toContain('a.com,b.com');
    @unlink($stub);
});
