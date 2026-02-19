<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = Schema::getColumnListing('attendances');
$output = "Columns in attendances table:\n";
foreach($columns as $column) {
    $output .= "- $column\n";
}

$db = DB::getDatabaseName();
$output .= "\nCurrent Database: $db\n";

file_put_contents('db_result.txt', $output);
echo "Result written to db_result.txt\n";


