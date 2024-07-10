<?php

namespace App\Http\Controllers\Api;

use App\Events\AccessGroupEvent;
use App\Events\OutGroupEvent;
use App\Http\Controllers\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Models\AddGroup;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;

use function App\Helpers\formatResult;

class RoomController extends ApiBaseController
{
    public function index()
    {
        $user = auth()->user();
        $rooms = $user->rooms;
        return $this->dataResponse(formatResult(null, true, $rooms));
    }

    public function create(Request $request)
    {
        $data =  $request->only('name');
        $data['type'] = Room::TYPE['group'];
        $room = Room::create($data);
        $room->members()->attach([auth()->user()->id]);
        return $this->dataResponse(formatResult('tạo phòng thành công', true));
    }

    public function outroom($room_id)
    {
        $room =  Room::find($room_id);
        if (!$room) return $this->dataResponse(formatResult('phòng không tồn tại'));
        $user = auth()->user();
        if (!$this->checkRoomMember($room_id, $user->id)) return $this->dataResponse(formatResult('bạn không phải thành viên của phòng này'));
        $room->members()->detach($user->id);
        $this->deleteAddGroup($room_id);

        $message = Message::create([
            'message' =>  $user->name . ' đã rời nhóm',
            'room_id' => $room_id,
            'status' => Message::STATUS['show']
        ]);
        event(new OutGroupEvent([
            'user_name' => $user->name,
            'room_id' => $room_id,
        ]));
        if (!$room->members) $room->delete();
        return  $this->dataResponse(formatResult('bạn đã rời khỏi phòng ' . $room->name, true));
    }

    public function messages($room_id)
    {
        $room  = Room::find($room_id);
        if (!$room) return $this->dataResponse(formatResult('Phòng này không tồn tại'));
        if (!$this->checkRoom($room_id)) return $this->dataResponse(formatResult('Bạn chưa vào phòng này'));
        // $messages = Message::with('user')
        //     ->where('room_id', $room_id)
        //     ->where('status', '1')
        //     ->orderBy('created_at', 'asc')
        //     ->get();

        $messages = Message::with('files')->leftJoin('users', 'messages.user_id', 'users.id')
            ->select('messages.*', 'users.name as user_name')
            ->where('room_id', $room_id)
            ->where('status', '1')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->sortBy('created_at');
        $values = array_values($messages->toArray());
        return $this->dataResponse(formatResult(null, true, $values));
    }

    private function checkRoom($room_id)
    {
        $room  = Room::find($room_id);
        if (!$room) return false;
        if ($room->type == Room::TYPE['global']) return true;
        $members = $room->members;
        $user_id = auth()->user()->id;
        foreach ($members as $member) {
            if ($member->id == $user_id) return true;
        }
        return false;
    }

    private function checkRoomMember($room_id, $user_id)
    {
        $group = Room::find($room_id);
        $members = $group->members;
        if ($members) {
            foreach ($members as $member) {
                if ($member->id == $user_id) return true;
            }
        }
        return false;
    }

    private function deleteAddGroup($room_id)
    {
        $user_id = auth()->user()->id;
        AddGroup::where('room_id', $room_id)->where('user_receive_id', $user_id)->delete();
    }
}
