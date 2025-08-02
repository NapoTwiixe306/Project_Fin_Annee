<?php



namespace Services;

class CSRFService
{
    private static $instance = null;
    private $sessionService;

    private function __construct()
    {
        $this->sessionService = SessionService::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Génère un token CSRF
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->sessionService->set('csrf_token', $token);
        return $token;
    }

    /**
     * Vérifie un token CSRF
     */
    public function verifyToken(string $token): bool
    {
        $storedToken = $this->sessionService->get('csrf_token');
        if (!$storedToken) {
            return false;
        }

        $isValid = hash_equals($storedToken, $token);
        
        // Régénérer le token après vérification
        if ($isValid) {
            $this->generateToken();
        }
        
        return $isValid;
    }

    /**
     * Récupère le token CSRF actuel
     */
    public function getToken(): string
    {
        $token = $this->sessionService->get('csrf_token');
        if (!$token) {
            $token = $this->generateToken();
        }
        return $token;
    }

    /**
     * Génère un champ caché HTML pour le token CSRF
     */
    public function getHiddenField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($this->getToken()) . '">';
    }
} 