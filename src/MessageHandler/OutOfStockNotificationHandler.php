<?php
namespace SF9\InventoryStockBundle\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use SF9\InventoryStockBundle\Message\OutOfStockNotification;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class OutOfStockNotificationHandler implements MessageHandlerInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $from;

    public function __construct(string $to, string $from, MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->to = $to;
        $this->from = $from;
    }

    public function __invoke(OutOfStockNotification $message): void
    {

        if($this->to == null || $this->from == null) {
            // whether or not to throw an error here depends if we want user 
            // to be able to use this bundle without enabling email notifications
            return;
        }

        $email = (new Email())
            ->from($this->from)
            ->to($this->to)
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Product out of stock!')
            ->text($message->getContent());

        $this->mailer->send($email);
    }
}