<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.blade\.php$/', RegexIterator::GET_MATCH);

$count = 0;
foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    
    // Pattern to match: {{ $var->prop->format('format') }}
    $pattern = '/\{\{\s*(\$[a-zA-Z0-9_]+(?:->[a-zA-Z0-9_]+)+)->format\(([\'"][^\'"]+[\'"])\)\s*\}\}/s';
    
    if (preg_match($pattern, $content)) {
        
        $newContent = preg_replace_callback($pattern, function($matches) {
            $prop = $matches[1];
            $format = $matches[2];
            
            return "{{ {$prop}?->format({$format}) ?? '—' }}";
        }, $content);
        
        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo "Updated: $path\n";
            $count++;
        }
    }
}
echo "Total files updated: $count\n";
