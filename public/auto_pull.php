<?php
$output = [];
$return_var = 0;
exec('git config --global --add safe.directory /var/www/fiinway-backend 2>&1', $output, $return_var);
exec('git config --global --add safe.directory "*" 2>&1', $output, $return_var);
exec('cd /var/www/fiinway-backend && git pull origin main 2>&1', $output, $return_var);
exec('cd /var/www/fiinway-backend && php artisan cache:clear 2>&1', $output, $return_var);
exec('cd /var/www/fiinway-backend && php artisan config:clear 2>&1', $output, $return_var);
exec('cd /var/www/fiinway-backend && php artisan view:clear 2>&1', $output, $return_var);
header('Content-Type: application/json');
echo json_encode([
    'status' => $return_var === 0,
    'output' => $output
]);

