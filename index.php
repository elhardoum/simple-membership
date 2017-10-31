<?php

// first things first
define ( 'ROOT_DIR', __DIR__ );

// debugging
define ( 'DEBUG_MODE', true );

require __DIR__ . '/env-setup.php';
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/dep-setup.php';

// this could be removed if you want to save tiny bits of memory
try {
    App\DB::i();
} catch ( PDOException $e ) {
    return App\View::Err('<h1>Database Error</h1><p>' . (
        DEBUG_MODE ? $e->getMessage() : 'Could not establish a database connection.'
    ) . '</p>', 500);
}

return App\Routes::dispatch();