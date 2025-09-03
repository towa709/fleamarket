<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class PaymentMethodTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function コンビニ払いを選択すると小計欄に反映される()
  {
    $user = User::factory()->create();
    $item = Item::factory()->create(['price' => 5000]);

    $this->actingAs($user);

    // 購入画面を表示
    $response = $this->get(route('purchase.show', ['item_id' => $item->id]));
    $response->assertStatus(200);

    // コンビニ払いを選択して送信
    $this->post(route('purchase.store', ['item_id' => $item->id]), [
      'payment_method' => 'konbini',
    ])->assertStatus(302);

    // 再度購入画面を表示して「コンビニ払い」が小計欄に反映されているか確認
    $this->get(route('purchase.show', ['item_id' => $item->id]))
      ->assertSee('コンビニ払い');
  }

  /** @test */
  public function カード払いを選択すると小計欄に反映される()
  {
    $user = User::factory()->create();
    $item = Item::factory()->create(['price' => 8000]);

    $this->actingAs($user);

    // 購入画面を表示
    $response = $this->get(route('purchase.show', ['item_id' => $item->id]));
    $response->assertStatus(200);

    // カード払いを選択して送信
    $this->post(route('purchase.store', ['item_id' => $item->id]), [
      'payment_method' => 'card',
    ])->assertStatus(302);

    // 再度購入画面を表示して「カード払い」が小計欄に反映されているか確認
    $this->get(route('purchase.show', ['item_id' => $item->id]))
      ->assertSee('カード払い');
  }
}
