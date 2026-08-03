<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.blade\.php$/', RegexIterator::GET_MATCH);

$count = 0;
foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    
    // Pattern to match:
    $pattern = '/:actions="\'(.*?)\'"/s';
    
    if (preg_match($pattern, $content)) {
        
        $newContent = preg_replace_callback('/<x-page-header\b(.*?):actions="\'(.*?)\'"(.*?)(\/?)>/s', function($matches) {
            $before = $matches[1];
            $actionContent = $matches[2];
            $after = $matches[3];
            $isSelfClosing = $matches[4] === '/';
            
            // Clean up actionContent
            $actionContent = str_replace('\"', '"', $actionContent);
            $actionContent = str_replace("\'", "'", $actionContent);
            $actionContent = preg_replace('/\'\.route\((.*?)\)\.\'/', '{{ route($1) }}', $actionContent);
            $actionContent = trim($actionContent);
            
            $slot = "\n    <x-slot:actions>\n        $actionContent\n    </x-slot:actions>\n";
            
            if ($isSelfClosing) {
                return "<x-page-header" . rtrim($before . $after) . ">" . $slot . "</x-page-header>";
            } else {
                return "<x-page-header" . $before . $after . ">" . $slot;
            }
        }, $content);
        
        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo "Updated: $path\n";
            $count++;
        }
    }
}
echo "Total files updated: $count\n";
