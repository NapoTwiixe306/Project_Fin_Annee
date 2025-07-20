<?php

spl_autoload_register(function ($class) {
    // Conversion du nom de classe en chemin de fichier
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Chargement des services principaux
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/Database/DatabaseConnection.php';
require_once __DIR__ . '/Services/SessionService.php';
require_once __DIR__ . '/Services/CookieService.php';
