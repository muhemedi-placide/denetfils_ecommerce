<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeCustomerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $preferredLocale = 'fr',
        private readonly ?string $temporaryPassword = null,
    )
    {
        $this->onQueue('mail');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isEnglish = $this->preferredLocale === 'en';

        $message = (new MailMessage)
            ->subject($isEnglish ? 'Welcome to '.config('shop.name') : 'Bienvenue chez '.config('shop.name'))
            ->greeting($isEnglish ? 'Welcome '.$notifiable->first_name.'!' : 'Bienvenue '.$notifiable->first_name.' !')
            ->line($isEnglish
                ? 'Your customer account was created securely during checkout.'
                : 'Votre compte client a été créé de manière sécurisée pendant votre commande.')
            ->line($this->temporaryPassword
                ? ($isEnglish
                    ? 'Your temporary password is: '.$this->temporaryPassword
                    : 'Votre mot de passe temporaire est : '.$this->temporaryPassword)
                : ($isEnglish
                    ? 'Use the email address and password you chose to sign in and track your orders.'
                    : 'Utilisez votre adresse email et le mot de passe que vous avez choisi pour vous connecter et suivre vos commandes.'))
            ->action($isEnglish ? 'Open my account' : 'Ouvrir mon compte', rtrim(config('seo.site_url'), '/').'/'.$this->preferredLocale.'/connexion')
            ->line($isEnglish ? 'Thank you for your trust.' : 'Merci pour votre confiance.');

        if ($this->temporaryPassword) {
            $message->line($isEnglish
                ? 'For security, change this password after your first sign-in.'
                : 'Pour votre sécurité, modifiez ce mot de passe après votre première connexion.');
        }

        return $message;
    }
}
