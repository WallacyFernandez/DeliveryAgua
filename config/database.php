<?php
return [
    'host' => getenv('DB_HOST') ?: 'db-agua.cjbewbs6d6lt.us-east-1.rds.amazonaws.com',
    'name' => getenv('DB_NAME') ?: 'NewRDS',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: 'wallacylendario'
]; 