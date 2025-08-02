<?php

namespace Services;

class ValidationService
{
    /**
     * Nettoie et valide une chaîne de caractères
     */
    public static function sanitizeString(string $input, int $maxLength = 255): string
    {
        $cleaned = trim($input);
        $cleaned = strip_tags($cleaned);
        $cleaned = htmlspecialchars($cleaned, ENT_QUOTES, 'UTF-8');
        
        if (strlen($cleaned) > $maxLength) {
            $cleaned = substr($cleaned, 0, $maxLength);
        }
        
        return $cleaned;
    }

    /**
     * Valide une adresse email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && strlen($email) <= 255;
    }

    /**
     * Valide un mot de passe
     */
    public static function validatePassword(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
        
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre.";
        }
        
        if (strlen($password) > 255) {
            $errors[] = "Le mot de passe ne peut pas dépasser 255 caractères.";
        }
        
        return $errors;
    }

    /**
     * Valide un nom ou prénom
     */
    public static function validateName(string $name): array
    {
        $errors = [];
        
        if (empty($name)) {
            $errors[] = "Le nom est obligatoire.";
        } elseif (strlen($name) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères.";
        } elseif (strlen($name) > 50) {
            $errors[] = "Le nom ne peut pas dépasser 50 caractères.";
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\'-]+$/', $name)) {
            $errors[] = "Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes.";
        }
        
        return $errors;
    }

    /**
     * Valide un ID numérique
     */
    public static function validateId($id): bool
    {
        return is_numeric($id) && $id > 0 && $id <= PHP_INT_MAX;
    }

    /**
     * Valide un fichier uploadé
     */
    public static function validateUploadedFile(array $file, array $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], int $maxSize = 5242880): array
    {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Erreur lors du téléchargement du fichier.";
            return $errors;
        }
        
        // Vérifier la taille
        if ($file['size'] > $maxSize) {
            $errors[] = "Le fichier est trop volumineux (max " . ($maxSize / 1024 / 1024) . "MB).";
        }
        
        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = "Type de fichier non autorisé.";
        }
        
        // Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            $errors[] = "Extension de fichier non autorisée.";
        }
        
        return $errors;
    }

    /**
     * Valide une URL
     */
    public static function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Valide un numéro de téléphone
     */
    public static function validatePhone(string $phone): bool
    {
        return preg_match('/^[\+]?[0-9\s\-\(\)]{8,15}$/', $phone);
    }

    /**
     * Valide une date
     */
    public static function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Échappe les caractères spéciaux pour SQL
     */
    public static function escapeSql(string $input): string
    {
        return addslashes($input);
    }

    /**
     * Valide un token CSRF
     */
    public static function validateCSRFToken(string $token): bool
    {
        $csrfService = CSRFService::getInstance();
        return $csrfService->verifyToken($token);
    }
} 