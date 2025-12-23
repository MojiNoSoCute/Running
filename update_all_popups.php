<?php
// Script to update all files with popup alert system

$files_to_update = [
    'manage_registrations.php',
    'manage_payments.php', 
    'manage_age_groups.php',
    'manage_price_rates.php',
    'manage_shipping_options.php',
    'admin_index.php',
    'runner_form.php',
    'registration_form.php',
    'payment_form.php',
    'race_category_form.php',
    'age_group_form.php',
    'price_rate_form.php',
    'shipping_option_form.php'
];

$crud_files = [
    'crud_category.php',
    'crud_registration.php',
    'crud_payment.php',
    'crud_age_group.php',
    'crud_price_rate.php',
    'crud_shipping_option.php',
    'save_runner.php',
    'save_registration.php',
    'save_payment.php',
    'save_category.php',
    'save_age_group.php',
    'save_price_rate.php',
    'save_shipping.php'
];

// Add CSS and JS includes to HTML files
foreach ($files_to_update as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Add CSS if not already present
        if (strpos($content, 'alert-popup.css') === false) {
            $content = str_replace(
                '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">',
                '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/alert-popup.css">',
                $content
            );
        }
        
        // Add JS if not already present
        if (strpos($content, 'alert-popup.js') === false) {
            $content = str_replace(
                '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>',
                '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/alert-popup.js"></script>',
                $content
            );
        }
        
        // Replace alert() calls with showSuccess/showError
        $content = preg_replace('/alert\([\'"]([^\'"]*)[\'"]/', 'showInfo("$1"', $content);
        
        // Replace confirm() calls with showConfirm
        $content = preg_replace('/if\s*\(\s*confirm\([\'"]([^\'"]*)[\'"]/', 'showConfirm("$1", "ยืนยัน", () => {', $content);
        
        file_put_contents($file, $content);
        echo "Updated: $file\n";
    }
}

// Update CRUD files
foreach ($crud_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Replace alert messages in PHP
        $content = str_replace(
            "echo \"<script>alert('",
            "echo \"<script src='assets/js/alert-popup.js'></script><script>document.addEventListener('DOMContentLoaded', function() { showSuccess('",
            $content
        );
        
        $content = str_replace(
            "'); window.location='",
            "', 'สำเร็จ', () => window.location='",
            $content
        );
        
        $content = str_replace(
            "';</script>",
            "'); });</script>",
            $content
        );
        
        file_put_contents($file, $content);
        echo "Updated CRUD: $file\n";
    }
}

echo "All files updated with popup alert system!\n";
?>