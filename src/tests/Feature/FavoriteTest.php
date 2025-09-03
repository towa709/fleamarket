<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class FavoriteTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function いいねアイコンを押すといいねが登録される()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $item = Item::factory()->create();

    // 初回いいね
    $response = $this->post(route('favorites.toggle', ['item' => $item->id]));
    $response->assertStatus(200);

    $this->assertDatabaseHas('favorites', [
      'user_id' => $user->id,
      'item_id' => $item->id,
    ]);
  }

  /** @test */
  public function 追加済みのアイコンは色が変化する()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $item = Item::factory()->create();
    $user->favorites()->attach($item->id); // 既にいいね済みの状態にする

    // 商品詳細ページを表示
    $response = $this->get(route('items.show', ['id' => $item->id]));

    // Bladeで「favorited」クラスが付く想定
    $response->assertSee('favorited');
  }

  /** @test */
  public function 再度いいねアイコンを押すといいねが解除される()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $item = Item::factory()->create();
    $user->favorites()->attach($item->id); // いいね済みにしておく

    // 解除処理
    $response = $this->post(route('favorites.toggle', ['item' => $item->id]));
    $response->assertStatus(200);

    $this->assertDatabaseMissing('favorites', [
      'user_id' => $user->id,
      'item_id' => $item->id,
    ]);
  }
}
