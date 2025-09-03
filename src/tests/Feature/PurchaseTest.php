<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;

class PurchaseTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function 購入ボタンを押すと購入が完了する()
  {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();

    $item = Item::factory()->create([
      'user_id' => $seller->id,
      'is_sold' => false,
    ]);

    $this->actingAs($buyer);

    // 🔹 GETリクエストに変更
    $response = $this->get(route('purchase.success', [
      'item_id' => $item->id,
      'payment' => 'card'
    ]));

    $response->assertRedirect('/');

    $this->assertDatabaseHas('transactions', [
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'item_id'  => $item->id,
      'payment_method' => 'card',
    ]);

    $this->assertTrue($item->fresh()->is_sold);
  }


  /** @test */
  public function 購入済み商品は一覧画面でSOLDと表示される()
  {
    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $seller->id]);

    Transaction::create([
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'item_id' => $item->id,
      'payment_method' => 'card',
      'shipping_postcode' => '123-4567',
      'shipping_address' => '東京都新宿区1-1-1',
      'shipping_building' => 'テストビル101',
      'purchased_at' => now(),
    ]);

    $response = $this->get('/');

    $response->assertSee('SOLD');
  }

  /** @test */
  public function 購入した商品がプロフィールの購入一覧に追加される()
  {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();

    $item = Item::factory()->create([
      'user_id' => $seller->id,
      'is_sold' => false,
    ]);

    // 購入処理をシミュレート
    Transaction::create([
      'buyer_id' => $buyer->id,
      'seller_id' => $seller->id,
      'item_id'  => $item->id,
      'payment_method' => 'card',
      'shipping_postcode' => '123-4567',
      'shipping_address'  => '東京都渋谷区テスト町1-1',
      'shipping_building' => 'テストビル101',
      'purchased_at'      => now(),
    ]);

    // 🔹 マイページの「購入一覧」タブを開く
    $this->actingAs($buyer);
    $response = $this->get('/mypage?page=buy');

    // 購入した商品の名前が表示されることを確認
    $response->assertSee($item->name);
  }
}
