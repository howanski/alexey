<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class MailerService
{
    public function __construct(
        private Environment $twig,
        private MailerInterface $mailer,
        private string $emailFromDefault,
        private string $kernelEnv,
    ) {
    }

    public function sendMail(
        string $to,
        string $subject,
        string $htmlBody,
        string $from = '',
    ): void {
        if (strlen($from) === 0) {
            $from = $this->emailFromDefault;
        }
        $email = (new Email())
            ->from(new Address(address: $from, name: 'Alexey'))
            ->to($to)
            ->subject($subject)
            ->html($htmlBody);
        $this->mailer->send($email);
    }

    public function sendMailTunnelChange(string $to, string $newAddress): void
    {
        $subject = '[' . $this->kernelEnv . '] Tunnel addres have changed!';
        $message = 'New Ngrok tunnel address is: ' . $newAddress;
        $this->sendMail(
            to: $to,
            subject: $subject,
            htmlBody: $this->twig->render('email/generic.html.twig', [
                'subject' => $subject,
                'message' => $message
            ])
        );
    }
}
