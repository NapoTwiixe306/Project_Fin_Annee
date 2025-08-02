<?php

namespace Controllers;

use Models\Brocanteur;
use Services\SessionService;
use Services\ValidationService;

class ContactController
{
    private $brocanteurModel;
    private $sessionService;
    private $validationService;

    public function __construct()
    {
        $this->brocanteurModel = new Brocanteur();
        $this->sessionService = SessionService::getInstance();
        $this->validationService = new ValidationService();
    }

    /**
     * Affiche la page de contact
     */
    public function index(): array
    {
        $user = null;
        $adminEmail = null;

        if ($this->sessionService->isLoggedIn()) {
            $user = $this->brocanteurModel->getById($this->sessionService->getUserId());
        }

        // Récupérer l'email admin pour le formulaire
        $adminEmail = $this->brocanteurModel->getAdminEmail();

        return [
            'user' => $user,
            'adminEmail' => $adminEmail,
            'isLoggedIn' => $this->sessionService->isLoggedIn()
        ];
    }

    /**
     * Traite l'envoi du formulaire de contact
     */
    public function sendMessage(): array
    {
        $result = ['success' => false, 'message' => '', 'error' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            // Validation des données
            $errors = [];
            
            if (empty($name)) {
                $errors[] = "Le nom est requis.";
            }
            
            if (empty($email) || !$this->validationService->isValidEmail($email)) {
                $errors[] = "L'email est requis et doit être valide.";
            }
            
            if (empty($subject)) {
                $errors[] = "Le sujet est requis.";
            }
            
            if (empty($message)) {
                $errors[] = "Le message est requis.";
            }

            if (empty($errors)) {
                // Envoi du message (simulation)
                if ($this->sendContactEmail($name, $email, $subject, $message)) {
                    $result['success'] = true;
                    $result['message'] = "Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.";
                } else {
                    $result['error'] = "Erreur lors de l'envoi du message. Veuillez réessayer.";
                }
            } else {
                $result['error'] = implode(' ', $errors);
            }
        }

        return $result;
    }

    /**
     * Envoie l'email de contact
     */
    private function sendContactEmail(string $name, string $email, string $subject, string $message): bool
    {
        $adminEmail = $this->brocanteurModel->getAdminEmail();
        
        if (!$adminEmail) {
            return false;
        }

        $headers = [
            'From: ' . $email,
            'Reply-To: ' . $email,
            'Content-Type: text/html; charset=UTF-8'
        ];

        $emailBody = "
            <h2>Nouveau message de contact</h2>
            <p><strong>Nom :</strong> " . htmlspecialchars($name) . "</p>
            <p><strong>Email :</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Sujet :</strong> " . htmlspecialchars($subject) . "</p>
            <p><strong>Message :</strong></p>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
        ";

        return mail($adminEmail, "Contact - " . $subject, $emailBody, implode("\r\n", $headers));
    }
} 