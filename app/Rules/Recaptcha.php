<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use ReCaptcha\ReCaptcha as Gcaptcha;
class Recaptcha implements Rule
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
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $remoteip = $_SERVER['REMOTE_ADDR'];
        $secret = env('RE_CAP_SECRET');
        $recaptcha = new Gcaptcha($secret);
        $resp = $recaptcha->verify($value, $remoteip);
        if ($resp->isSuccess()) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('auth.CaptchaWrong');
    }
}
