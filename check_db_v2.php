<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$describe = DB::select('DESCRIBE attendances');
$output = "Raw DESCRIBE attendances:\n";
foreach($describe as $col) {
    $output .= "- {$col->Field} ({$col->Type})\n";
}

$db = DB::getDatabaseName();
$output .= "\nCurrent Database: $db\n";

file_put_contents('db_check_v3.txt', $output);
echo "Result written to db_check_v3.txt\n";

