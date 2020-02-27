<?php

namespace App\Traits;
use Laravel\Passport\HasApiTokens as LaravelHasApiTokens;


trait HasApiTokens
{
	use LaravelHasApiTokens;
	
	public function tokens()
    {
        return $this->hasMany(\App\Models\ApiToken::class, 'user_id')->orderBy('created_at', 'desc');
    }
	
	
	 /**
     * Set the current access token for the user.
     * Lets reload \App\Models\Token to include the relationships
     * @param  \Laravel\Passport\Token  $accessToken
     * @return $this
     */
    public function withAccessToken($accessToken)
    {
        $this->accessToken = \App\Models\ApiToken::find($accessToken->id);
        return $this;
    }
	
	public function getPersonalAccessTokensAttribute()
    {
         return  $this->tokens()->with('client')->get()->filter(function ($token) {
            return $token->client->personal_access_client && ! $token->revoked;
        })->values();
	}
}
