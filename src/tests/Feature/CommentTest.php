<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;

class CommentTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function ログイン済みユーザーはコメントを送信できる()
  {
    $user = User::factory()->create();
    $item = Item::factory()->create();

    $this->actingAs($user);

    $response = $this->post("/items/{$item->id}/comments", [
      'content' => 'テストコメント',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('comments', [
      'user_id' => $user->id,
      'item_id' => $item->id,
      'content' => 'テストコメント',
    ]);
  }

  /** @test */
  public function ログインしていないユーザーはコメントを送信できない()
  {
    $item = Item::factory()->create();

    $response = $this->post("/items/{$item->id}/comments", [
      'content' => 'ログイン前コメント',
    ]);

    $response->assertRedirect('/login'); // ログインページに飛ばされる
    $this->assertDatabaseMissing('comments', [
      'content' => 'ログイン前コメント',
    ]);
  }

  /** @test */
  public function コメントが空ならバリデーションエラーになる()
  {
    $user = User::factory()->create();
    $item = Item::factory()->create();

    $this->actingAs($user);

    $response = $this->post("/items/{$item->id}/comments", [
      'content' => '',
    ]);

    $response->assertSessionHasErrors(['content' => 'コメントを入力してください。']);
  }

  /** @test */
  public function コメントが255字以上ならバリデーションエラーになる()
  {
    $user = User::factory()->create();
    $item = Item::factory()->create();

    $this->actingAs($user);

    $longComment = str_repeat('あ', 256);

    $response = $this->post("/items/{$item->id}/comments", [
      'content' => $longComment,
    ]);

    $response->assertSessionHasErrors(['content' => 'コメントは255文字以内で入力してください。']);
  }
}
