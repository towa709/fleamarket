<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserInfoUpdateTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function プロフィール編集画面で初期値が正しく表示される()
  {
    $user = User::factory()->create([
      'name' => 'テストユーザー',
      'postcode' => '123-4567',
      'address' => '東京都新宿区西新宿1-1-1',
      'building' => '新宿ビル101',
      'profile_image' => 'profiles/test.png',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('profile.edit'));

    // 入力欄に過去の値が初期表示されているか確認
    $response->assertSee('テストユーザー');
    $response->assertSee('123-4567');
    $response->assertSee('東京都新宿区西新宿1-1-1');
    $response->assertSee('新宿ビル101');
    $response->assertSee('storage/profiles/test.png');
  }
}
