<?php

namespace Services;

class CookieService
{
    private static $instance = null;
    private $defaultOptions = [
        'expires' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ];

    private function __construct()
    {
        // Configuration par défaut
        $this->defaultOptions['secure'] = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Définit un cookie
     */
    public function set(string $name, string $value, array $options = []): bool
    {
        $options = array_merge($this->defaultOptions, $options);
        
        if (PHP_VERSION_ID >= 70300) {
            return setcookie($name, $value, $options);
        } else {
            // Compatibilité avec les versions antérieures à PHP 7.3
            return setcookie(
                $name,
                $value,
                $options['expires'],
                $options['path'],
                $options['domain'],
                $options['secure'],
                $options['httponly']
            );
        }
    }

    /**
     * Récupère un cookie
     */
    public function get(string $name, ?string $default = null): ?string
    {
        return $_COOKIE[$name] ?? $default;
    }

    /**
     * Vérifie si un cookie existe
     */
    public function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Supprime un cookie
     */
    public function delete(string $name): bool
    {
        if (!$this->has($name)) {
            return false;
        }

        return $this->set($name, '', ['expires' => time() - 3600]);
    }

    /**
     * Sauvegarde les filtres de recherche
     */
    public function saveSearchFilters(array $filters, int $expireInDays = 30): bool
    {
        $json = json_encode($filters);
        return $this->set('search_filters', $json, [
            'expires' => time() + ($expireInDays * 24 * 60 * 60)
        ]);
    }

    /**
     * Récupère les filtres de recherche sauvegardés
     */
    public function getSearchFilters(): array
    {
        $json = $this->get('search_filters');
        if (!$json) {
            return [];
        }

        $filters = json_decode($json, true);
        return is_array($filters) ? $filters : [];
    }

    /**
     * Supprime les filtres de recherche
     */
    public function clearSearchFilters(): bool
    {
        return $this->delete('search_filters');
    }

    /**
     * Sauvegarde les préférences utilisateur
     */
    public function saveUserPreferences(array $preferences, int $expireInDays = 365): bool
    {
        $json = json_encode($preferences);
        return $this->set('user_preferences', $json, [
            'expires' => time() + ($expireInDays * 24 * 60 * 60)
        ]);
    }

    /**
     * Récupère les préférences utilisateur
     */
    public function getUserPreferences(): array
    {
        $json = $this->get('user_preferences');
        if (!$json) {
            return [];
        }

        $preferences = json_decode($json, true);
        return is_array($preferences) ? $preferences : [];
    }

    /**
     * Sauvegarde un filtre spécifique
     */
    public function saveFilter(string $filterName, string $value, int $expireInDays = 30): bool
    {
        return $this->set("filter_{$filterName}", $value, [
            'expires' => time() + ($expireInDays * 24 * 60 * 60)
        ]);
    }

    /**
     * Récupère un filtre spécifique
     */
    public function getFilter(string $filterName, string $default = ''): string
    {
        return $this->get("filter_{$filterName}", $default);
    }

    /**
     * Supprime un filtre spécifique
     */
    public function clearFilter(string $filterName): bool
    {
        return $this->delete("filter_{$filterName}");
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
