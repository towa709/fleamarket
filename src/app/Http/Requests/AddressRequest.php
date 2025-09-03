<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true; // 認証済みなら誰でもOK
  }

  public function rules(): array
  {
    return [
      'postcode' => ['required', 'regex:/^\d{3}-\d{4}$/'],
      'address'  => ['required', 'string', 'max:255'],
      'building' => ['nullable', 'string', 'max:255'],
    ];
  }

  public function messages(): array
  {
    return [
      'postcode.required' => '郵便番号は必須です。',
      'postcode.regex'    => '郵便番号はハイフンありの8文字で入力してください。',
      'address.required'  => '住所は必須です。',
    ];
  }
}
