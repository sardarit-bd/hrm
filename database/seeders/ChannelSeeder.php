<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Channel;

class ChannelSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            'Fiverr',
            'Upwork',
            'Toptal',
            'Local',
            'Direct',
            'Referral',
        ];

        foreach ($channels as $name) {
            Channel::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}