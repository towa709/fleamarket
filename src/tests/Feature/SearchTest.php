<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class SearchTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function 商品名で部分一致検索ができる()
  {
    // 検索対象の商品を作成
    Item::factory()->create(['name' => '赤いシャツ']);
    Item::factory()->create(['name' => '青いズボン']);

    // 「赤」で検索
    $response = $this->get('/?keyword=赤');

    $response->assertStatus(200);
    $response->assertSee('赤いシャツ');
    $response->assertDontSee('青いズボン');
  }

  /** @test */
  public function 検索状態がマイリストでも保持されている()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    // 検索対象の商品
    $item1 = Item::factory()->create(['name' => '赤いシャツ']);
    $item2 = Item::factory()->create(['name' => '青いズボン']);

    // ユーザーが item1 をお気に入り登録
    $user->favorites()->attach($item1->id);

    // 「赤」で検索しつつマイリストを開く
    $response = $this->get('/?tab=mylist&keyword=赤');

    $response->assertStatus(200);
    $response->assertSee('赤いシャツ');   // 検索結果が反映
    $response->assertDontSee('青いズボン'); // 部分一致しないものは表示されない
  }
}
