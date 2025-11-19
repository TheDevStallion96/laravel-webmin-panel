<?php

namespace App\Services;

use Illuminate\Filesystem\Filesystem;

class TemplateRenderer
{
    public function __construct(private Filesystem $fs = new Filesystem()) {}

    /**
     * Render a stub file by replacing {{ variable }} placeholders.
     */
    public function render(string $stubPath, array $vars): string
    {
        $contents = $this->fs->get($stubPath);
        foreach ($vars as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $contents = str_replace('{{ '.$key.' }}', (string) $value, $contents);
        }
        return $contents;
    }
}
