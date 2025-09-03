<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => ['required', 'string', 'max:20'],
      'postcode' => ['required', 'regex:/^\d{3}-\d{4}$/'],
      'address'  => ['required', 'string', 'max:255'],
      'building' => ['nullable', 'string', 'max:255'],
      'image' => ['nullable', 'image', 'mimes:jpeg,png', 'max:2048'], // 2MBまで
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'ユーザー名は必須です。',
      'name.max' => 'ユーザー名は20文字以内で入力してください。',
      'postcode.required' => '郵便番号は必須です。',
      'postcode.regex' => '郵便番号はハイフンありの8文字で入力してください。',
      'address.required' => '住所は必須です。',
      'image.image' => 'プロフィール画像は画像ファイルを指定してください。',
      'image.mimes' => 'プロフィール画像はjpegまたはpng形式でアップロードしてください。',
      'image.max' => 'プロフィール画像は2MB以内でアップロードしてください。',
    ];
  }
}
