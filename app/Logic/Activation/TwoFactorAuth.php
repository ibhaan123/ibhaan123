<?php
namespace App\Logic\Activation;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use Illuminate\Http\JsonResponse as IlluminateJsonResponse;
use Illuminate\Http\Response as IlluminateHtmlResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Support\Facades\Cache;
use App\Notifications\TwoFa;
use Illuminate\Support\Facades\Hash;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;


class TwofactorAuth extends Authenticator{
	
	public function getQRCodeInline($company, $holder, $secret, $size = 150, $encoding = 'utf-8')
    {
        $url = $this->getQRCodeUrl($company, $holder, $secret);
		$renderer = new ImageRenderer(
			new RendererStyle($size),
			new ImagickImageBackEnd()
		);
		$writer = new Writer($renderer);
		$data = $writer->writeString($url, $encoding);;
        return 'data:image/png;base64,'.base64_encode($data);
    }
	
	protected function makeJsonResponse($statusCode)
    {
		if ($statusCode != SymfonyResponse::HTTP_OK) {
            return new IlluminateJsonResponse(
				['status'=>'ERROR','message'=>__('auth.invalid_auth_code')],
				SymfonyResponse::HTTP_OK
			);
        }
		return new IlluminateJsonResponse(
			['status'=>'2FA','twofactorAuth' => $this->config('otp_input') , 'message'=>__('auth.2faRequired')],
			SymfonyResponse::HTTP_OK
		);
    }
	
	protected function has_verify_phone()
    {
        return $this->getRequest()->has('verify_phone');
    }

    protected function verify_phone()
    {
		$user = auth()->user();
        $code = Cache::remember('sms_code',8,function(){
			return str_replace('0',7,strval(mt_rand(111111,999999)));
		});
		$phone = preg_replace("/[^\d]/","",$this->getRequest()->input('verify_phone'));
		Cache::put('sms_'.$code,$phone,8);
		Notification::route($this->config('sms_provider'), $phone)
            ->notify(new TwoFa($code));
		return new IlluminateJsonResponse(
			['status'=>'SUCCESS', 'message'=>__('auth.sms_code_sent')],
			 SymfonyResponse::HTTP_OK
		);
    }
	
	protected function has_verify_code()
    {
        return $this->getRequest()->has('verify_code');
    }

    protected function verify_code()
    {
		$user = auth()->user();
        $code = $this->getRequest()->input('verify_code');
		$phone = Cache::get('sms_'.$code);
		if(empty($phone)){
			return new IlluminateJsonResponse(
				['status'=>'ERROR', 'message'=>__('auth.sms_code_error')],
				 SymfonyResponse::HTTP_OK
			);
		}
		$user->phone_number = $phone;
		$user->save();
		return new IlluminateJsonResponse(
			['status'=>'SUCCESS', 'message'=>__('auth.sms_code_sent')],
			 SymfonyResponse::HTTP_OK
		);
    }
	
	protected function has_secret()
    {
        return $this->getRequest()->has('secret');
    }

    protected function save_secret()
    {
		$user = auth()->user();
        $secret = $this->getRequest()->input('secret');
		$code = $this->getRequest()->input('code');
		$valid = $this->verifyKey($secret, $code, 8);
		if(!$valid){
			return new IlluminateJsonResponse(
				['status'=>'ERROR', 'message'=>__('auth.invalid2facode')],
				 SymfonyResponse::HTTP_OK
			);
		}
		$col = $this->config('otp_secret_column');
		$user->$col = $secret;
		$user->save();
		$this->login();
		return new IlluminateJsonResponse(
			['status'=>'SUCCESS', 'message'=>__('auth.setup_complete')],
			 SymfonyResponse::HTTP_OK
		);
    }
	
	public function isAuthorised(){
		$password = $this->getRequest()->input('password');
		return Hash::check($password, auth()->user()->password);
		
	}
	
	public function authFailed(){
		return response()->json(['status' => 'ERROR','message' => __('auth.authfailed')]);
		
	}
	
	public function phoneIsVerified(){
		$user = auth()->user();
		if(setting('verify_phone','false')=='true')
		return isset($user->phone_number) && !empty($user->phone_number);
		return true;
		
	}
	
	public function  makeRequestOneTimePasswordResponse(){
		if($this->has_verify_code()){
			return $this->verify_code();
		}
		
		if($this->has_verify_phone()){
			return $this->verify_phone();
		}
		if($this->has_verify_phone()){
			return $this->verify_phone();
		}
		if($this->has_secret()){
			return $this->save_secret();
		}
		if(!$this->isActivated()||!$this->phoneIsVerified()){
			$response = ['status'=>'2FASETUP','message'=>__('auth.2faSetupRequired')];
			$response['verify_phone'] = !$this->phoneIsVerified();
			$response['verify_twofa'] = !$this->isActivated();
			if(!$this->isActivated()){
				$secretKey= $this->generateSecretKey();
				$inlineUrl = $this->getQRCodeInline(
					env('APP_NAME'),
					env('MAIL_FROM_ADDRESS'),
					$secretKey
				);
				$response['secret']= $secretKey;
				$response['inlineUrl'] = $inlineUrl;
			}
			return new IlluminateJsonResponse(
				$response,
				 SymfonyResponse::HTTP_OK
			);
		}
		return parent:: makeRequestOneTimePasswordResponse();
	}
	
	protected function canPassWithoutCheckingOTP()
    {
        return
            !$this->isEnabled() ||
            $this->noUserIsAuthenticated() ||
            $this->twoFactorAuthStillValid();
    }
	
	
	protected function makeStatusCode()
    {
		if($this->inputHasOneTimePassword() && !$this->checkOTP())
        return SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY;
		$user = auth()->user();
		$code = Cache::remember('code',3,function(){
			return $this->getCurrentOtp($this->getGoogle2FASecretKey());
		});
		$user->notify(new TwoFa($code));
		return SymfonyResponse::HTTP_OK;
    }
}