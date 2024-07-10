<?php

namespace App\Http\Controllers\Api;

use App\Events\AccessGroupEvent;
use App\Events\AddGroupEvent;
use App\Http\Controllers\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Models\AddGroup;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;

use function App\Helpers\formatResult;

class AddGroupController extends ApiBaseController
{

    public function create(Request $request)
    {
        $data =  $request->only(
            'user_receive_id',
            'room_id'
        );
        $user_id = auth()->user()->id;

        if ($data['user_receive_id'] == $user_id)  return $this->dataResponse(formatResult('lỗi bất định'));

        $checkexistaddgroup = $this->checkExistAddGroupUser($data['room_id'], $data['user_receive_id']);

        if (!$checkexistaddgroup['success']) return $this->dataResponse($checkexistaddgroup);

        if (!$this->checkRoomMember($data['room_id'], $user_id)) return $this->dataResponse(formatResult('bạn không phải là thành viên của phòng này'));

        if ($this->checkRoomMember($data['room_id'], $data['user_receive_id'])) return $this->dataResponse(formatResult('người dùng này đã là thành viên của phòng này rồi'));
        $data['status'] = AddGroup::STATUS['not_access'];
        $addgroup = AddGroup::create($data);
        $addgroup['room_name'] = $addgroup->room->name;
        event(new AddGroupEvent($addgroup));
        return $this->dataResponse(formatResult('gửi lời mời thang gia phòng thành công', true));
    }

    public function access($id)
    {
        $user_id  = auth()->user()->id;
        $addgroup = Addgroup::find($id);
        if (!$addgroup)  return $this->dataResponse(formatResult('lời mời không tồn tại'));
        if ($addgroup->user_receive_id != $user_id) return $this->dataResponse(formatResult('lời mời không dành cho bạn'));
        if ($addgroup->user_receive->id != auth()->user()->id) return $this->dataResponse(formatResult('lỗi bất định'));
        $addgroup->status = Addgroup::STATUS['access'];
        $addgroup->save();
        $addgroup['user_receive_name'] = $addgroup->user_receive->name;
        $group =  Room::find($addgroup->room_id);
        $group->members()->attach([
            $addgroup->user_receive->id
        ]);

        $message = Message::create([
            'message' => $addgroup->user_receive->name . ' đã tham gia vào nhóm',
            'room_id' => $group->id,
            'status' => Message::STATUS['show']
        ]);

        event(new AccessGroupEvent($addgroup));
        return $this->dataResponse(formatResult('bạn đã tham gia vào nhóm ' . $group->name, true));
    }

    public function refuse($id)
    {
        $addgroup = AddGroup::find($id);
        if (!$addgroup)  return $this->dataResponse(formatResult('lời mời không tồn tại'));
        if ($addgroup->user_receive_id != auth()->user()->id)  return $this->dataResponse(formatResult('lời mời không dành cho bạn'));
        $addgroup->delete();
        return $this->dataResponse(formatResult('bạn đã từ chối lời mời tham gia phòng ' . $addgroup->room->name, true));
    }

    private function checkExistAddGroupUser($room_id, $user_id)
    {
        $addgroup = AddGroup::where('room_id', $room_id)
            ->where('user_receive_id', $user_id)
            ->first();
        if ($addgroup) return formatResult('đã tồn tại lời mời tham gia phòng cho người này');
        return formatResult(null, true);
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

    private function checkExistRoom($room_id)
    {
        $room = Room::find($room_id);
        if (!$room)  return formatResult('phòng không tồn tại');
        return formatResult('phòng tồn tại', true, $room);
    }
}
