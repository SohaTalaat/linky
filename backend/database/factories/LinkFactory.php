<?php

namespace Database\Factories;

use App\Enums\LinkStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Link>
 */
class LinkFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'url' => $this->faker->url(),
            'title' => $this->faker->boolean(70) ? $this->faker->sentence(4) : null,
            'description' => $this->faker->boolean(50) ? $this->faker->paragraph() : null,
            'image' => $this->faker->boolean(30) ? $this->faker->imageUrl() : null,
            'site_name' => $this->faker->boolean(50) ? $this->faker->domainWord() : null,
            'favicon' => $this->faker->boolean(40) ? $this->faker->imageUrl(64, 64) : null,
            'notes' => $this->faker->boolean(60) ? $this->faker->text(180) : null,
            'status' => $this->faker->randomElement([
                LinkStatus::SAVED->value,
                LinkStatus::READING->value,
                LinkStatus::DONE->value,
            ]),
            'is_favorite' => $this->faker->boolean(20),
            'last_opened_at' => $this->faker->boolean(40) ? $this->faker->dateTimeBetween('-30 days', 'now') : null,
        ];
    }
}
