<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name'        => 'required|string|max:255',                // 商品名
      'description' => 'required|string|max:255',                // 商品説明
      'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',   // 商品画像
      'category_id'   => 'required|array',
      'category_id.*' => 'exists:categories,id',                 // 商品カテゴリ
      'condition' => 'required|string|not_in:""',                //商品状態
      'price'       => 'required|numeric|min:1',                 // 商品価格
    ];
  }

  public function messages(): array
  {
    return [
      'name.required'        => '商品名は必須です。',
      'description.required' => '商品の説明は必須です。',
      'description.max'      => '商品の説明は255文字以内で入力してください。',
      'image.required'       => '商品画像は必須です。',
      'image.image'          => '画像ファイルを選択してください。',
      'image.mimes'          => '画像はjpegまたはpng形式でアップロードしてください。',
      'category_id.required' => '商品カテゴリを選択してください。',
      'condition.required'   => '商品状態は選択必須です。',
      'price.required'       => '商品価格は必須です。',
      'price.numeric'        => '価格は数値で入力してください。',
      'price.min'            => '価格は0円以上にしてください。',
    ];
  }
}
