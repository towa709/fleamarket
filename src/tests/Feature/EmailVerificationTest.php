<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Illuminate\Auth\Notifications\VerifyEmail;

class EmailVerificationTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function 会員登録後に認証メールが送信される()
  {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    event(new \Illuminate\Auth\Events\Registered($user));

    Notification::assertSentTo(
      [$user],
      \Illuminate\Auth\Notifications\VerifyEmail::class
    );
  }


  /** @test */
  public function 認証リンクをクリックするとメール認証が完了する()
  {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
      'verification.verify',
      now()->addMinutes(60),
      [
        'id' => $user->id,
        'hash' => sha1($user->email),
      ]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertRedirect(route('items.index'));
    $this->assertNotNull($user->fresh()->email_verified_at);
  }

  /** @test */
  public function 認証していないユーザーは認証案内画面にリダイレクトされる()
  {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/sell');

    $response->assertRedirect(route('verification.notice'));
  }
}
