<?php
// Run this ONCE after uploading to create the database tables.
// Then DELETE this file from your server immediately!
require_once __DIR__ . '/db.php';
setup_db();
echo 'Database tables created successfully. DELETE this file now!';
