<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;

class UserInfoTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function プロフィール画面でユーザー情報が正しく表示される()
  {
    $user = User::factory()->create([
      'name' => 'テストユーザー',
      'profile_image' => 'profiles/test.png',
    ]);

    // 出品商品
    $item1 = Item::factory()->create([
      'user_id' => $user->id,
      'name' => '出品商品A',
    ]);

    // 購入商品
    $item2 = Item::factory()->create(['name' => '購入商品B']);
    Transaction::create([
      'buyer_id' => $user->id,
      'seller_id' => $item2->user_id,
      'item_id'  => $item2->id,
      'payment_method' => 'card',
      'shipping_postcode' => '100-0001',
      'shipping_address'  => '東京都千代田区千代田1-1',
      'shipping_building' => 'テストビル101',
      'purchased_at'      => now(),
    ]);

    $this->actingAs($user);

    // 出品一覧タブで確認
    $response = $this->get('/mypage?page=sell');
    $response->assertSee('テストユーザー');
    $response->assertSee('storage/profiles/test.png');
    $response->assertSee('出品商品A');

    // 購入一覧タブで確認
    $response = $this->get('/mypage?page=buy');
    $response->assertSee('購入商品B');
  }
}
