<?php
$output = [];
$return_var = 0;

putenv('HOME=/tmp');
exec('git -c safe.directory=* -C /var/www/fiinway-backend pull origin main 2>&1', $output, $return_var);
exec('cd /var/www/fiinway-backend && php artisan migrate --force 2>&1', $output, $return_var);
exec('cd /var/www/fiinway-backend && php artisan cache:clear 2>&1', $output, $return_var);
exec('cd /var/www/fiinway-backend && php artisan config:clear 2>&1', $output, $return_var);
exec('cd /var/www/fiinway-backend && php artisan view:clear 2>&1', $output, $return_var);
exec('cd /var/www/fiinway-backend && php artisan optimize:clear 2>&1', $output, $return_var);

header('Content-Type: application/json');
echo json_encode([
    'status' => $return_var === 0,
    'output' => $output
]);
