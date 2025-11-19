<?php

use App\Services\ActivityLogService;

if (! function_exists('activity')) {
    /**
     * Create a new ActivityLogService instance.
     */
    function activity(): ActivityLogService
    {
        return new ActivityLogService();
    }
}

if (! function_exists('ansi_to_html')) {
    /**
     * Very small ANSI to HTML converter for basic SGR color/bold codes.
     * This escapes non-ANSI text for safety.
     */
    function ansi_to_html(string $text): string
    {
        $esc = "\e"; // escape char
        // Map of color codes to CSS colors
        $colors = [
            30 => '#6b7280', // gray-500
            31 => '#ef4444', // red-500
            32 => '#22c55e', // green-500
            33 => '#eab308', // yellow-500
            34 => '#3b82f6', // blue-500
            35 => '#a855f7', // purple-500
            36 => '#06b6d4', // cyan-500
            37 => '#e5e7eb', // gray-200
            90 => '#9ca3af', // gray-400
            91 => '#f87171', // red-400
            92 => '#4ade80', // green-400
            93 => '#fde047', // yellow-400
            94 => '#60a5fa', // blue-400
            95 => '#c084fc', // purple-400
            96 => '#67e8f9', // cyan-300
            97 => '#ffffff', // white
        ];

        $out = '';
        $i = 0;
        $len = strlen($text);
        $open = false;
        $style = '';

        $escapeHtml = function ($s) {
            return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        while ($i < $len) {
            $ch = $text[$i];
            if ($ch === "\e" && ($i + 1 < $len) && $text[$i+1] === '[') {
                // parse SGR
                $i += 2;
                $code = '';
                while ($i < $len && $text[$i] !== 'm') {
                    $code .= $text[$i++];
                }
                // skip 'm'
                $i++;
                $parts = array_filter(array_map('intval', explode(';', $code)));
                if (empty($parts)) $parts = [0];
                foreach ($parts as $p) {
                    if ($p === 0) { // reset
                        if ($open) { $out .= '</span>'; $open = false; $style=''; }
                    } elseif ($p === 1) { // bold
                        $style .= 'font-weight:700;';
                    } elseif (isset($colors[$p])) {
                        $style .= 'color:'.$colors[$p].';';
                    } elseif ($p >= 40 && $p <= 47) {
                        $fg = $p - 10; // crude map from bg to fg range
                        if (isset($colors[$fg])) $style .= 'background-color:'.$colors[$fg].';';
                    }
                }
                if ($style !== '') {
                    if ($open) { $out .= '</span>'; }
                    $out .= '<span style="'.$style.'">';
                    $open = true;
                }
                continue;
            }
            $out .= $escapeHtml($ch === "\n" ? "\n" : $ch);
            $i++;
        }
        if ($open) $out .= '</span>';
        // Preserve newlines
        return nl2br($out);
    }
}
