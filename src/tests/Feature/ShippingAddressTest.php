<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;

class ShippingAddressTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function 送付先住所変更後に購入画面へ反映される()
  {
    $user = User::factory()->create([
      'postcode' => '1000001',
      'address'  => '東京都千代田区千代田1-1',
      'building' => '皇居前ビル101',
    ]);

    $item = Item::factory()->create();

    $this->actingAs($user);

    // 新しい住所を登録
    $this->post(route('purchase.updateAddress', ['item_id' => $item->id]), [
      'postcode' => '530-0001',  // ← ハイフン必須ならここ修正
      'address'  => '大阪府大阪市北区梅田1-1-1',
      'building' => '梅田タワー1503',
    ])->assertRedirect(route('purchase.show', $item->id));


    // 購入画面で変更後の住所が表示されているか確認
    $this->get(route('purchase.show', $item->id))
      ->assertSee('530-0001')
      ->assertSee('大阪府大阪市北区梅田1-1-1')
      ->assertSee('梅田タワー1503');
  }

  /** @test */
  public function 購入した商品に送付先住所が保存される()
  {
    $user = User::factory()->create();
    $item = Item::factory()->create();

    $this->actingAs($user);

    // 住所を変更
    $this->post(route('purchase.updateAddress', ['item_id' => $item->id]), [
      'postcode' => '150-0001',
      'address'  => '東京都渋谷区神宮前1-1-1',
      'building' => '原宿ビル202',
    ]);

    // 購入処理（成功を想定）
    $this->get(route('purchase.success', [
      'item_id' => $item->id,
      'payment' => 'card'
    ]))->assertRedirect('/');

    // DBに住所が保存されているか確認
    $this->assertDatabaseHas('transactions', [
      'item_id'  => $item->id,
      'buyer_id' => $user->id,
      'shipping_postcode' => '150-0001',
      'shipping_address'  => '東京都渋谷区神宮前1-1-1',
      'shipping_building' => '原宿ビル202',
    ]);
  }
}
