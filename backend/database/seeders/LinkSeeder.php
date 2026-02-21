<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create([
            'name' => 'Soha',
            'email' => 'soha@test.com',
        ]);

        // Create some tags for this user
        $tags = Tag::factory()
            ->count(8)
            ->for($user)
            ->create();

        // Create links for this user
        Link::factory()
            ->count(25)
            ->for($user)
            ->create()
            ->each(function ($link) use ($tags) {
                $link->tags()->attach(
                    $tags->random(rand(0, 3))->pluck('id')->all()
                );
            });
    }
}
