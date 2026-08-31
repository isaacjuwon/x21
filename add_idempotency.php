<?php

$files = [
    'app/Http/Controllers/Api/V1/Loans/StoreController.php',
    'app/Http/Controllers/Api/V1/Loans/LoanApprovalController.php',
    'app/Http/Controllers/Api/V1/Loans/LoanDisbursementController.php',
    'app/Http/Controllers/Api/V1/Loans/LoanPayoffController.php',
    'app/Http/Controllers/Api/V1/Loans/LoanRejectionController.php',
    'app/Http/Controllers/Api/V1/Loans/LoanRepaymentController.php',
    
    'app/Http/Controllers/Api/V1/Wallet/WalletFundController.php',
    'app/Http/Controllers/Api/V1/Wallet/WalletTransferController.php',
    'app/Http/Controllers/Api/V1/Wallet/WalletWithdrawController.php',
    
    'app/Http/Controllers/Api/V1/Shares/UpdateListingController.php',
    'app/Http/Controllers/Api/V1/Shares/BuyOrderController.php',
    'app/Http/Controllers/Api/V1/Shares/SellOrderController.php',
    'app/Http/Controllers/Api/V1/Shares/ShareOrderApprovalController.php',
    'app/Http/Controllers/Api/V1/Shares/ShareOrderRejectionController.php',
    'app/Http/Controllers/Api/V1/Shares/DividendController.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Missing: $file\n";
        continue;
    }
    $content = file_get_contents($file);
    
    if (strpos($content, 'WendellAdriel\Idempotency\Attributes\Idempotent') !== false) {
        continue;
    }
    
    // Add use statement
    $content = preg_replace('/(namespace App\\\\Http\\\\Controllers.*?;)/', "$1\n\nuse WendellAdriel\Idempotency\Attributes\Idempotent;", $content);
    
    $content = preg_replace('/^(class\s+\w+)/m', "#[Idempotent]\n$1", $content);
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
