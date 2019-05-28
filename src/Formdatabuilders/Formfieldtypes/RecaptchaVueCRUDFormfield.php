<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


use Datalytix\VueCRUD\Rules\GoogleCaptchaConfirmed;

class RecaptchaVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * StaticVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'recaptcha';
        $this->checkConfiguration();
        $this->props = [
            'key' => config('services.google.captcha.key'),
            'locale' => \App::getLocale(),
        ];
        $this->setRules([
            new GoogleCaptchaConfirmed()
        ]);
        $this->setMessages([
            'GoogleCaptchaConfirmed' => 'Kérjük használja a robot-ellenőrzést'
        ]);
        $this->default = 'invalid';
    }

    protected function checkConfiguration()
    {
        if ((config('services.google.captcha.secret') == null)
            || (config('services.google.captcha.key') == null)) {
            throw new \Exception('reCaptcha data missing from config (services.google.captcha.secret, services.google.captcha.key)');
        }

    }
}