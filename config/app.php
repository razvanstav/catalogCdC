<?php

return [
    'name' => 'Îndrumar',
    'tagline' => 'Spațiul tău didactic & conectare cu familia',
    'env' => getenv('APP_ENV') ?: 'development',
    'debug' => (bool)(getenv('APP_DEBUG') ?: true),
    'url' => getenv('APP_URL') ?: 'http://localhost:8000',
    'timezone' => 'Europe/Bucharest',
    'locale' => 'ro',
];
