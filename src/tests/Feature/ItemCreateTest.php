<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ItemCreateTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function 出品商品情報が正しく保存される()
  {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('items.store'), [
      'name'        => 'テスト商品',
      'brand'       => 'テストブランド',
      'description' => 'これはテスト用の商品説明です。',
      'condition'   => '新品',
      'price'       => 5000,
      'category_id' => [$category->id],
      // ここを image() → create() に変更
      'image'       => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
    ]);

    $response->assertRedirect('/');

    $this->assertDatabaseHas('items', [
      'user_id'     => $user->id,
      'name'        => 'テスト商品',
      'brand'       => 'テストブランド',
      'description' => 'これはテスト用の商品説明です。',
      'condition'   => '新品',
      'price'       => 5000,
    ]);

    $this->assertDatabaseHas('category_items', [
      'category_id' => $category->id,
    ]);
  }
}
