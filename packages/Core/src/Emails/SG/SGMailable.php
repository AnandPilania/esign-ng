<?php


namespace Core\Emails\SG;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use SendGrid;
use SendGrid\Mail\Mail;

class SGMailable extends Mail
{
    use Queueable, SerializesModels;

    protected $data;

    protected $sender;

    protected $from;

    protected $mailer;

    const SENDER_EMAIL = 'info@shop.dev-azure.selmesta.jp';
    const SENDER_NAME = '健やかショップ';

    /**
     * SGMailable constructor.
     * @param $data
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($data)
    {
        parent::__construct();
        $this->from = env('SENDGRID_SENDER');
        $this->sender = env('SENDGRID_SENDER_NAME');
        $this->data = $data;

        $this->mailer = new SendGrid(env('SENDGRID_API_KEY', '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'));
        $this->from(env('SENDGRID_SENDER', self::SENDER_EMAIL), env('SENDGRID_SENDER_NAME', self::SENDER_NAME));
    }

    public function to($to, $receiver = '')
    {
        Log::info("To: $to");
        $this->addTo($to, $receiver);
        return $this;
    }

    public function from($from, $sender)
    {
        Log::info("From: $from");
        $this->setFrom($from, $sender);
        return $this;
    }

    public function cc($cc)
    {
        $this->addCc($cc);
        return $this;
    }

    public function ccs($cc = [])
    {
        $this->addCcs($cc);
        return $this;
    }

    public function bCCs($bcc = [])
    {
        $this->addBccs($bcc);
        return $this;
    }

    public function attach($resource)
    {
        $this->addAttachments($resource);
        return $this;
    }

    public function subject($subject)
    {
        Log::info("subject: $subject");
        $this->setSubject($subject);

        return $this;
    }

    public function view($view, array $data = [])
    {
        $content = view($view, $data)->render();
        $this->addContent("text/html", $content);

        return $this;
    }

    public function send()
    {
        Log::info("[SendGrid] sending");
        $response = $this->mailer->send($this);
        Log::info('[SendGrid] receiving: ' . json_encode($response));
    }
}
