<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'vincent.timtam@gmail.com'; // Replace with the email used for testing if known, or just list all
// Actually, let's just list the last 5 tokens
$tokens = DB::table('password_reset_tokens')->orderBy('created_at', 'desc')->limit(5)->get();

echo "Last 5 password reset tokens:\n";
foreach ($tokens as $token) {
    echo "Email: " . $token->email . " | Created at: " . $token->created_at . "\n";
}
