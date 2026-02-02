<?php

$files = [
    'README.md',
    'composer.json',
    'src/Contracts',
    'src/Models',
    'src/Builders',
    'src/MenuService.php',
    'src/MenuServiceProvider.php',
    'config/menu.php',
    'tests',
];

echo "🔍 mohib/menu Package Structure Check\n";
echo str_repeat('=', 40)."\n\n";

foreach ($files as $file) {
    $exists = file_exists($file) ? '✅' : '❌';
    echo "  {$exists} {$file}\n";
}

echo "\n";
echo '📋 Total Files: '.count($files)."\n";

$present = array_filter($files, 'file_exists');
if (count($present) === count($files)) {
    echo "🎉 Package structure is COMPLETE!\n";
    echo "📦 Ready for Composer distribution\n";
    echo "🌟 mohib/menu Laravel package is ready!\n";
} else {
    echo "⚠️  Some files are missing\n";
    echo "🔧 Please complete the package structure\n";
}
