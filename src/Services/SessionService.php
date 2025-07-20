<?php

namespace Services;

class SessionService
{
    private static $instance = null;
    private $started = false;

    private function __construct()
    {
        $this->start();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Démarre la session si elle n'est pas déjà démarrée
     */
    public function start(): void
    {
        if (!$this->started && session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->started = true;
        }
    }

    /**
     * Définit une variable de session
     */
    public function set(string $key, $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    /**
     * Récupère une variable de session
     */
    public function get(string $key, $default = null)
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Vérifie si une variable de session existe
     */
    public function has(string $key): bool
    {
        $this->start();
        return isset($_SESSION[$key]);
    }

    /**
     * Supprime une variable de session
     */
    public function remove(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    /**
     * Vide toutes les variables de session
     */
    public function clear(): void
    {
        $this->start();
        $_SESSION = [];
    }

    /**
     * Détruit la session
     */
    public function destroy(): void
    {
        $this->start();
        
        // Vider les variables de session
        $_SESSION = [];
        
        // Supprimer le cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Détruire la session
        session_destroy();
        $this->started = false;
    }

    /**
     * Régénère l'ID de session
     */
    public function regenerateId(): void
    {
        $this->start();
        session_regenerate_id(true);
    }

    /**
     * Vérifie si l'utilisateur est connecté
     */
    public function isLoggedIn(): bool
    {
        return $this->has('brocanteur_id');
    }

    /**
     * Récupère l'ID de l'utilisateur connecté
     */
    public function getUserId(): ?int
    {
        $id = $this->get('brocanteur_id');
        return $id ? (int)$id : null;
    }

    /**
     * Connecte un utilisateur
     */
    public function login(int $userId): void
    {
        $this->regenerateId();
        $this->set('brocanteur_id', $userId);
        $this->set('login_time', time());
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(): void
    {
        $this->destroy();
    }

    /**
     * Vérifie si la session est expirée
     */
    public function isExpired(int $maxLifetime = 3600): bool
    {
        $loginTime = $this->get('login_time');
        if (!$loginTime) {
            return true;
        }
        
        return (time() - $loginTime) > $maxLifetime;
    }

    /**
     * Met à jour le temps de dernière activité
     */
    public function updateLastActivity(): void
    {
        $this->set('last_activity', time());
    }

    /**
     * Empêcher le clonage
     */
    private function __clone() {}

    /**
     * Empêcher la désérialisation
     */
    public function __wakeup() {}
}
