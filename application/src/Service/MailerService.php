<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function sendMail(string $from, string $to, string $subject, string $htmlBody): void
    {
        $email = (new Email())
            ->from(new Address(address: $from, name: 'Alexey'))
            ->to($to)
            ->subject($subject)
            ->html($htmlBody);
        $this->mailer->send($email);
    }
}
