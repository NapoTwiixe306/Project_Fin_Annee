<?php

spl_autoload_register(function ($class) {
    // Mapping des namespaces vers les dossiers
    $namespaceMap = [
        'Services' => 'Services',
        'Controllers' => 'Controllers', 
        'Models' => 'Models',
        'Database' => 'Database'
    ];
    
    // Extraire le namespace et le nom de classe
    $parts = explode('\\', $class);
    if (count($parts) >= 2) {
        $namespace = $parts[0];
        $className = $parts[1];
        
        // Vérifier si le namespace est mappé
        if (isset($namespaceMap[$namespace])) {
            $file = __DIR__ . '/' . $namespaceMap[$namespace] . '/' . $className . '.php';
            
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
    
    // Fallback : essayer le chemin direct
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Chargement de la configuration et des services principaux
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/Database/DatabaseConnection.php';
require_once __DIR__ . '/Services/SessionService.php';
require_once __DIR__ . '/Services/CookieService.php';
require_once __DIR__ . '/Services/CSRFService.php';
require_once __DIR__ . '/Services/ValidationService.php';
?>
