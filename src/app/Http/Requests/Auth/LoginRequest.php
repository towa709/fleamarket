<?php

namespace App\Http\Requests\Auth;

use Laravel\Fortify\Http\Requests\LoginRequest as BaseLoginRequest;

class LoginRequest extends BaseLoginRequest
{
  public function rules(): array
  {
    return [
      'email' => ['required', 'email'],
      'password' => ['required'],
    ];
  }

  public function messages()
  {
    return [
      'email.required' => 'メールアドレスを入力してください',
      'email.email'    => '正しいメールアドレス形式で入力してください',
      'password.required' => 'パスワードを入力してください',
    ];
  }
}
