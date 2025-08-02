<?php
/**
 * Configuration pour l'envoi d'emails
 * À personnaliser selon votre environnement
 */

// Configuration de base pour l'envoi d'email (même système que contact.php)
define('EMAIL_FROM', 'noreply@suprabrocante.be');
define('EMAIL_FROM_NAME', 'Supra Brocante');
define('EMAIL_REPLY_TO', 'noreply@suprabrocante.be');

// Configuration SMTP (optionnel - pour une configuration plus robuste)
define('SMTP_ENABLED', false);
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@example.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_ENCRYPTION', 'tls'); // 'tls' ou 'ssl'

// Configuration de l'application
define('APP_NAME', 'Supra Brocante');
define('APP_URL', 'http://localhost'); // À modifier en production

// Durée d'expiration des tokens (en heures)
define('RESET_TOKEN_EXPIRY_HOURS', 1);

// Limitation du taux de demandes (en minutes)
define('RESET_REQUEST_LIMIT_MINUTES', 15); 