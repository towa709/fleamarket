<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Favorite;

class ItemDetailTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function 商品詳細ページで必要な情報が表示される()
  {
    $user = User::factory()->create();
    $item = Item::factory()->create([
      'user_id' => $user->id,
      'name' => 'テスト商品',
      'brand' => 'テストブランド',
      'price' => 5000,
      'description' => 'これはテスト用の商品説明です。',
      'condition' => '新品',
      'img_url' => 'https://via.placeholder.com/400.png',
    ]);

    // カテゴリを1つ追加
    $category = Category::factory()->create(['name' => 'テストカテゴリ']);
    $item->categories()->attach($category->id);

    // コメントを追加
    Comment::factory()->create([
      'user_id' => $user->id,
      'item_id' => $item->id,
      'content' => 'テストコメントです',
    ]);

    // お気に入り登録
    $user->favorites()->attach($item->id);

    $response = $this->get("/item/{$item->id}");

    // 商品情報
    $response->assertSee('テスト商品');
    $response->assertSee('テストブランド');
    $response->assertSee('¥5,000'); // ← 修正済み
    $response->assertSee('これはテスト用の商品説明です。');
    $response->assertSee('新品');

    // 商品画像
    $response->assertSee('https://via.placeholder.com/400.png');

    // カテゴリ
    $response->assertSee('テストカテゴリ');

    // いいね数
    $response->assertSee('1');

    // コメント数
    $response->assertSee('コメント(1)');

    // コメントのユーザー名と内容
    $response->assertSee($user->name);
    $response->assertSee('テストコメントです');
  }

  /** @test */
  public function 複数カテゴリが商品詳細ページに表示される()
  {
    $item = Item::factory()->create([
      'name' => 'カテゴリテスト商品',
    ]);

    $category1 = Category::factory()->create(['name' => 'カテゴリ1']);
    $category2 = Category::factory()->create(['name' => 'カテゴリ2']);

    // attach は ID を渡す
    $item->categories()->attach([$category1->id, $category2->id]);

    $response = $this->get("/item/{$item->id}");

    $response->assertSee('カテゴリ1');
    $response->assertSee('カテゴリ2');
  }
}
