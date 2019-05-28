<?php

namespace Datalytix\VueCRUD\Rules;

use Illuminate\Contracts\Validation\Rule;

class GoogleCaptchaConfirmed implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     * @throws \Exception
     */
    public function passes($attribute, $value)
    {
        $secret = config('services.google.captcha.secret');
        if ($secret === null) {
            throw new \Exception('reCaptcha secret missing from config (services.google.captcha.secret)');
        }
        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'POST',
            'https://www.google.com/recaptcha/api/siteverify',
            ['form_params' =>
                 [
                     'secret' => $secret,
                     'response' => $value,
                 ]
            ]
        );
        return json_decode($response->getBody()->getContents())->success === true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('reCaptcha bot check failed');
    }
}
