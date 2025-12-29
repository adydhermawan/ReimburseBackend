<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Reimbursement;
use App\Models\Client;
use App\Models\Category;

$output = "--- VERIFICATION START ---\n";
$output .= "Total Reimbursements: " . Reimbursement::count() . "\n";
$output .= "Total Clients: " . Client::count() . "\n";

$first = Reimbursement::with(['client', 'category'])->orderBy('id')->first();
$output .= "First Record: \n";
$output .= "  Date: " . $first->transaction_date . "\n";
$output .= "  Desc: " . $first->note . "\n";
$output .= "  Amount: " . $first->amount . "\n";
$output .= "  Client: " . $first->client->name . "\n";
$output .= "  Category: " . $first->category->name . "\n";

$last = Reimbursement::with(['client', 'category'])->orderBy('id', 'desc')->first();
$output .= "Last Record: \n";
$output .= "  Date: " . $last->transaction_date . "\n";
$output .= "  Desc: " . $last->note . "\n";
$output .= "  Amount: " . $last->amount . "\n";
$output .= "  Client: " . $last->client->name . "\n";
$output .= "  Category: " . $last->category->name . "\n";

// Check a specific mapping
$bensin = Reimbursement::where('note', 'Bensin')->first();
$output .= "Check Bensin Category: " . ($bensin ? $bensin->category->name : 'Not Found') . "\n";

// Check Clients list
$output .= "Clients List: " . implode(', ', Client::pluck('name')->toArray()) . "\n";

$output .= "--- VERIFICATION END ---\n";

file_put_contents('verify_log.txt', $output);

