<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class GlobalRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data_room = [
            'id' => 1,
            'name' => 'Global Room',
            'type' => Room::TYPE['global']
        ];
        Room::create($data_room);
    }
}
