<?php
namespace LaminSanneh\FlexiContact\components;

use Cms\Classes\ComponentBase;
use LaminSanneh\FlexiContact\Models\Settings;
use Mail;
use Validator;
use ValidationException;
use Request;

class ContactForm extends ComponentBase
{
    public $formValidationRules = [
        'name' => ['required'],
        'surname' => ['required'],
        'phone' => ['required'],
        'fin_code' => ['required'],
        'email' => ['required', 'email'],
        'cv_file' => ['required', 'file', 'mimes:pdf,doc,docx'],  // Fayl validation-u
    ];

    public $customMessages = [];

    public function componentDetails()
    {
        return [
            'name' => 'laminsanneh.flexicontact::lang.strings.component_name',
            'description' => 'laminsanneh.flexicontact::lang.strings.component_desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'injectBootstrapAssets' => [
                'title'       => 'laminsanneh.flexicontact::lang.strings.inject_bootstrap',
                'description' => 'laminsanneh.flexicontact::lang.strings.inject_bootstrap_desc',
                'type'        => 'checkbox',
                'default'     => true,
            ],
            'injectMainScript' => [
                'title'       => 'laminsanneh.flexicontact::lang.strings.inject_main_script',
                'description' => 'laminsanneh.flexicontact::lang.strings.inject_main_script_desc',
                'type'        => 'checkbox',
                'default'     => true,
            ]
        ];
    }

    public function onMailSent()
    {
        if($this->enableCaptcha()){
            $this->formValidationRules['g-recaptcha-response'] = ['required'];
        }
    
        // Bütün inputları al
        $data = post();
    
        // Faylı ayrıca götür
        $cvFile = Request::file('cv_file');
        if ($cvFile) {
            $data['cv_file'] = $cvFile; // validasiya üçün əlavə et
        }
    
        // Validasiya et
        $validator = Validator::make($data, $this->formValidationRules, $this->customMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    
        // Captcha server-side yoxla
        if($this->enableCaptcha() && Settings::get('enable_server_captcha_validation')){
            if(!$this->googleCaptchaPasses(post('g-recaptcha-response'))){
                throw new ValidationException(['g-recaptcha-response' => 'Captcha credentials are incorrect']);
            }
        }
    
        // Mail göndər
        Mail::send('laminsanneh.flexicontact::emails.message', $data, function($message) use ($cvFile)
        {
            $recipientEmail = Settings::get('recipient_email') ?? 'test@example.com';
            $recipientName = Settings::get('recipient_name') ?? 'Admin';
    
            $subject = Settings::get('subject') ?? 'New Contact Form';
    
            $replyEmail = post('email') ?? 'noreply@example.com';
            $replyName  = post('name') ?? 'Anonymous';
    
            $message->replyTo($replyEmail, $replyName)
                    ->to($recipientEmail, $recipientName)
                    ->subject($subject);
    
            if ($cvFile) {
                $message->attach($cvFile->getRealPath(), [
                    'as' => $cvFile->getClientOriginalName(),
                    'mime' => $cvFile->getMimeType(),
                ]);
            }
        });
    
        $this->page["confirmation_text"] = Settings::get('confirmation_text') ?? 'Mesaj göndərildi!';
        return ['error' => false];
    }

    public function googleCaptchaPasses($googleCaptchaResponse)
    {
        $client = new \GuzzleHttp\Client();

        $params = [
            'secret' => Settings::get('secret_key'),
            'response' => $googleCaptchaResponse,
            'remoteip' => Request::ip()
        ];

        $res = $client->request(
            'POST',
            'https://www.google.com/recaptcha/api/siteverify',
            ['form_params' => $params]
        );

        $body = json_decode($res->getBody());

        return $body->success;
    }

    public function onRun()
    {
        if ($this->property('injectBootstrapAssets') == true) {
            $this->addCss('assets/css/bootstrap.css');
            $this->addJs('assets/js/bootstrap.js');
        }

        $this->addJs('https://www.google.com/recaptcha/api.js');

        if ($this->property('injectMainScript') == true) {
            $this->addJs('assets/js/main.js');
        }
    }

    public function siteKey()
    {
        return Settings::get('site_key');
    }

    public function enableCaptcha()
    {
        return Settings::get('enable_captcha');
    }
}
