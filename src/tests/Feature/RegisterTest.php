<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;

class RegisterTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function 名前が未入力だとエラーメッセージが表示される()
  {
    $response = $this->post('/register', [
      'name' => '',
      'email' => 'test@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors(['name']);
  }

  /** @test */
  public function メールアドレスが未入力だとエラーメッセージが表示される()
  {
    $response = $this->post('/register', [
      'name' => 'テストユーザー',
      'email' => '',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors(['email']);
  }

  /** @test */
  public function パスワードが未入力だとエラーメッセージが表示される()
  {
    $response = $this->post('/register', [
      'name' => 'テストユーザー',
      'email' => 'test@example.com',
      'password' => '',
      'password_confirmation' => '',
    ]);

    $response->assertSessionHasErrors(['password']);
  }

  /** @test */
  public function パスワードが8文字未満だとエラーメッセージが表示される()
  {
    $response = $this->post('/register', [
      'name' => 'テストユーザー',
      'email' => 'test@example.com',
      'password' => 'short',
      'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors(['password']);
  }

  /** @test */
  public function パスワードと確認用パスワードが一致しないとエラーメッセージが表示される()
  {
    $response = $this->post('/register', [
      'name' => 'テストユーザー',
      'email' => 'test@example.com',
      'password' => 'password123',
      'password_confirmation' => 'different123',
    ]);

    // password ではなく password_confirmation のエラーになることを確認
    $response->assertSessionHasErrors(['password_confirmation']);
  }


  /** @test */
  public function 正しい入力ならユーザー登録され認証メールが送信される()
  {
    Notification::fake();

    $response = $this->post('/register', [
      'name' => 'テストユーザー',
      'email' => 'verify@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'verify@example.com')->first();

    // DBにユーザーが作成されていること
    $this->assertDatabaseHas('users', ['email' => 'verify@example.com']);

    // 認証メールが送信されていること
    Notification::assertSentTo([$user], VerifyEmail::class);
  }

  /** @test */
  public function 認証リンクをクリックするとメール認証が完了して商品一覧にリダイレクトされる()
  {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
      'verification.verify',
      now()->addMinutes(60),
      ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    // 認証後に商品一覧にリダイレクトされること
    $response->assertRedirect('/');

    // 認証済みになっていること
    $this->assertTrue($user->fresh()->hasVerifiedEmail());
  }
}
