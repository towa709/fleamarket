<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;

class ItemListTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function 全商品を取得できる()
  {
    Item::factory()->count(3)->create();

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSeeText('商品一覧画面');
  }

  /** @test */
  public function 購入済み商品は_sold_と表示される()
  {
    $item = Item::factory()->create();
    Transaction::create([
      'item_id' => $item->id,
      'buyer_id' => User::factory()->create()->id,
      'seller_id' => $item->user_id,
      'status' => 'completed'
    ]);

    $response = $this->get('/');
    $response->assertSeeText('SOLD');
  }

  /** @test */
  public function 自分が出品した商品は一覧に表示されない()
  {
    $user = User::factory()->create();
    Item::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);
    $response = $this->get('/');

    $response->assertDontSeeText('商品名');
  }
}
