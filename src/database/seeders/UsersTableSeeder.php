<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
  public function run()
  {
    // 外部キー制約を維持しながら削除
    DB::table('users')->delete();
    DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');

    DB::table('users')->insert([
      [
        'id' => 1,
        'name' => '田中 奏多',
        'email' => 'tanaka@example.com',
        'password' => Hash::make('password123'),
        'postcode' => '1000001',
        'address' => '東京都千代田区千代田1-1',
        'building' => '皇居前マンション101',
        'profile_image' => 'profiles/user1.jpg',
        'email_verified_at' => now(),
      ],
      [
        'id' => 2,
        'name' => '佐藤 美咲',
        'email' => 'sato@example.com',
        'password' => Hash::make('password123'),
        'postcode' => '1500001',
        'address' => '東京都渋谷区神宮前1-2-3',
        'building' => '渋谷ハイツ201',
        'profile_image' => 'profiles/user2.jpg',
        'email_verified_at' => now(),
      ],
      [
        'id' => 3,
        'name' => '鈴木 大和',
        'email' => 'suzuki@example.com',
        'password' => Hash::make('password123'),
        'postcode' => '5300001',
        'address' => '大阪府大阪市北区梅田1-1-1',
        'building' => '梅田タワー1503',
        'profile_image' => 'profiles/user3.jpg',
        'email_verified_at' => now(),
      ],
      [
        'id' => 4,
        'name' => '高橋 由衣',
        'email' => 'takahashi@example.com',
        'password' => Hash::make('password123'),
        'postcode' => '4600002',
        'address' => '愛知県名古屋市中区栄2-2-2',
        'building' => '栄レジデンス305',
        'profile_image' => 'profiles/user4.jpg',
        'email_verified_at' => now(),
      ],
      [
        'id' => 5,
        'name' => '伊藤 蓮',
        'email' => 'ito@example.com',
        'password' => Hash::make('password123'),
        'postcode' => '0600001',
        'address' => '北海道札幌市中央区北1条1-1',
        'building' => '札幌シティハイツ502',
        'profile_image' => 'profiles/user5.jpg',
        'email_verified_at' => now(),
      ],
    ]);
  }
}
