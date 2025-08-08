<?php
namespace LaminSanneh\FlexiContact\Components;

use Cms\Classes\ComponentBase;
use Mail;
use Validator;
use ValidationException;
use Request;

class ContactPageForm extends ComponentBase
{
    public $formValidationRules = [
        'name'            => ['required'],
        'email'           => ['required', 'email'],
        'phone'           => ['required'],
        'content_message' => ['required'], // Yeni ad
    ];

    public function componentDetails()
    {
        return [
            'name'        => 'Contact Page Form',
            'description' => 'Contact page form for sending messages'
        ];
    }

    public function onFormSubmit()
    {
        $data = post();

        // content_message-i message kimi map edirik ki, email şablonunda işləsin
        $data['message'] = (string) $data['content_message'];

        $validator = Validator::make($data, $this->formValidationRules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Email göndər
        Mail::send('laminsanneh.flexicontact::contactEmails.message', $data, function($message) use ($data) {
            $message->to('elekberhsnli@gmail.com', 'Admin')
                    ->subject('Yeni əlaqə mesajı')
                    ->replyTo($data['email'], $data['name']);
        });
	return [
 		   '#contact-form-result' => '<p style="color:green;">Mesajınız uğurla göndərildi. Təşəkkürlər!</p>',
 		   'resetForm' => true // JS-də istifadə üçün flag
		];

    }
}
