<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        Model::unguard();
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $holidays = Holiday::factory()
            ->count(3)
            ->create(['user_id' => $user->id]);

        foreach ($holidays as $holiday) {
            $participant = Participant::factory()
                ->count(2)
                ->create([
                    'user_id' => $user->id,
                ]);
            $holiday->participants()->sync($participant);
        }
    }
}
