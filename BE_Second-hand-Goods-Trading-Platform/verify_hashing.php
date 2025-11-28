<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KhachHang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "==================================================\n";
echo "       KIỂM TRA MÃ HÓA MẬT KHẨU (HASHING)\n";
echo "==================================================\n";

// 1. Test Hash::make
echo "[1] Test Hash::make('123456'):\n";
$hash = Hash::make('123456');
echo "    Hash: " . substr($hash, 0, 20) . "...\n";
if (Hash::check('123456', $hash)) {
    echo "    ✅ Hash check OK\n";
} else {
    echo "    ❌ Hash check FAILED\n";
}

// 2. Test Create User
echo "\n[2] Test Create User (Simulation):\n";
$email = 'test_hash_' . time() . '@example.com';
$password = 'password123';

try {
    DB::beginTransaction();
    
    $user = KhachHang::create([
        'ho_va_ten' => 'Test Hash User',
        'email' => $email,
        'password' => Hash::make($password),
        'is_active' => 1
    ]);

    echo "    User created: " . $user->email . "\n";
    echo "    Stored Password: " . substr($user->password, 0, 20) . "...\n";

    if (strpos($user->password, '$2y$') === 0) {
        echo "    ✅ Password is hashed (starts with $2y$)\n";
    } else {
        echo "    ❌ Password is NOT hashed\n";
    }

    // 3. Test Login Logic (Simulation)
    echo "\n[3] Test Login Logic:\n";
    if (Hash::check($password, $user->password)) {
        echo "    ✅ Login with correct password: OK\n";
    } else {
        echo "    ❌ Login with correct password: FAILED\n";
    }

    if (!Hash::check('wrongpassword', $user->password)) {
        echo "    ✅ Login with wrong password: OK (Failed as expected)\n";
    } else {
        echo "    ❌ Login with wrong password: FAILED (Should not match)\n";
    }
    
    DB::rollBack(); // Don't save to DB
    echo "\n    (Rolled back database changes)\n";

} catch (Exception $e) {
    echo "    ❌ Error: " . $e->getMessage() . "\n";
    DB::rollBack();
}

echo "==================================================\n";
