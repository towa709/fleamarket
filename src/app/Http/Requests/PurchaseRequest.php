<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'payment_method' => ['required'],
    ];
  }

  public function withValidator($validator)
  {
    $validator->after(function ($validator) {
      $user = $this->user();

      $hasProfileAddress = !empty($user->postcode) && !empty($user->address);

      $hasSessionAddress = session()->has('shipping_postcode') && session()->has('shipping_address');

      if (!$hasProfileAddress && !$hasSessionAddress) {
        $validator->errors()->add('address_id', '配送先を選択してください。');
      }
    });
  }

  public function messages()
  {
    return [
      'payment_method.required' => '支払い方法を選択してください。',
    ];
  }
}
