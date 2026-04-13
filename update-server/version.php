<?php
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
  'version' => '1.0.1',
  'updateURL' => 'https://app.lemonsqueezy.com/my-orders',
  'releaseNotes' => 'Hier ist die aktuellste Version gerade verfuegbar.'
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
