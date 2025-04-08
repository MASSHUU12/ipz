<?php

namespace App\Mail;

use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SendMail extends Mailable
{
	use Queueable, SerializesModels;

	protected static $messages = [
		'default' => [
			'msg' => 'Jest to testowa wiadomość. Prosimy na nią nieodpowiadać',
		],
		'emailverify' => [
			'msg' => '',
		],
	];

	protected $chosenMsg;
	protected $toEmail;
	protected $messageType;
	protected $userId;
	public function __construct(string $messageType = 'default')
	{
		$this->messageType = $messageType;

		if (!isset(self::$messages[$messageType])) {
			$this->chosenMsg = ['msg' => 'Default message (type not recognized).'];
		} else {
			$this->chosenMsg = self::$messages[$messageType];
		}
	}

	public function build()
	{
		$data = $this->chosenMsg;
		$data['type'] = $this->messageType;

		if ($data['type'] === 'emailverify') {
			$data['verify_link'] = URL::temporarySignedRoute(
				'verification.verify',
				now()->addMinutes(60),
				[
					'id'   => $this->userId,
					'hash' => sha1($this->toEmail)
				]
			);
		}

		return $this
			->from('zutweatherproject@gmail.com', 'ipz')
			->subject('Testowa wiadomość od ipz')
			->view('emails.custom')
			->with([
				'data' => $data,
			]);
	}

	public static function sendMail(string $to, int $userId, string $messageType)
	{
		$instance = new self($messageType);
		$instance->toEmail = $to;
		$instance->userId  = $userId;
		\Illuminate\Support\Facades\Mail::to($to)->send($instance);
	}
}
