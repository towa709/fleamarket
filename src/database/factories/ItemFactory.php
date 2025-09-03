<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item;
use App\Models\User;

class ItemFactory extends Factory
{
  protected $model = Item::class;

  public function definition()
  {
    return [
      'user_id' => User::factory(), // 出品者
      'name' => $this->faker->word(),
      'price' => $this->faker->numberBetween(100, 10000),
      'brand' => $this->faker->company(),
      'description' => $this->faker->sentence(),
      'img_url' => $this->faker->imageUrl(400, 400, 'fashion', true),
      'condition' => $this->faker->randomElement(['new', 'used']),
    ];
  }
}
