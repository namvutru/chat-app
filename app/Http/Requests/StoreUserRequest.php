<?php

namespace App\Http\Requests;


class StoreUserRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'name' => 'required|unique:users',
            'email' => 'required|unique:users|email',
            'password' => 'required|min:8',
        ];
    }
}
