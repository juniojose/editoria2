<?php

namespace EditorIA2\UserManager\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true); // Enable exceptions

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host       = $_ENV['SMTP_HOST'];
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $_ENV['SMTP_USERNAME'];
        $this->mailer->Password   = $_ENV['SMTP_PASSWORD'];
        $this->mailer->SMTPSecure = $_ENV['SMTP_SECURE'];
        $this->mailer->Port       = $_ENV['SMTP_PORT'];

        // Set default sender
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        
        // Set content type
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    /**
     * @throws Exception
     */
    public function sendEmail(string $toAddress, string $toName, string $subject, string $htmlBody, string $altBody = ''): bool
    {
        try {
            // Recipients
            $this->mailer->addAddress($toAddress, $toName);

            // Content
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = $altBody ?: strip_tags($htmlBody);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            // Re-throw the exception to be handled by the caller
            throw new Exception("O e-mail não pôde ser enviado. Erro do Mailer: {$this->mailer->ErrorInfo}", 500);
        }
    }
}
