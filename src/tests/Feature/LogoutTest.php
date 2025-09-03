<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LogoutTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function ログイン中のユーザーはログアウトできる()
  {
    // ユーザーを作成してログイン状態にする
    $user = User::factory()->create();
    $this->actingAs($user);

    // ログアウト処理を実行
    $response = $this->post('/logout');

    // ログアウト後にログイン画面へリダイレクトされることを確認
    $response->assertRedirect('/login');

    // ログアウト状態になっていることを確認
    $this->assertGuest();
  }
}
