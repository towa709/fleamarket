<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function メールアドレスが未入力だとエラーメッセージが表示される()
  {
    $response = $this->post('/login', [
      'email' => '',
      'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['email']);
  }

  /** @test */
  public function パスワードが未入力だとエラーメッセージが表示される()
  {
    $response = $this->post('/login', [
      'email' => 'test@example.com',
      'password' => '',
    ]);

    $response->assertSessionHasErrors(['password']);
  }

  /** @test */
  public function 入力情報が間違っている場合エラーメッセージが表示される()
  {
    // DBにユーザーは作らないので必ず間違いになる
    $response = $this->post('/login', [
      'email' => 'wrong@example.com',
      'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors(); // ログインエラー
    $this->assertGuest();
  }

  /** @test */
  public function 正しい入力ならログインできて商品一覧にリダイレクトされる()
  {
    $user = User::factory()->create([
      'email' => 'test@example.com',
      'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
      'email' => 'test@example.com',
      'password' => 'password123',
    ]);

    $response->assertRedirect('/'); // 商品一覧に遷移
    $this->assertAuthenticatedAs($user);
  }
}
