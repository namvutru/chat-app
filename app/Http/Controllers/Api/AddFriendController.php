<?php

namespace App\Http\Controllers\Api;

use App\Events\AccessFriendEvent;
use App\Events\AddFriendEvent;
use App\Http\Controllers\ApiBaseController;
use App\Models\AddFriend;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

use function App\Helpers\formatResult;

class AddFriendController extends ApiBaseController
{
    public function create(Request $request)
    {
        $data =  $request->only(
            'user_receive_id'
        );
        if ($data['user_receive_id'] == auth()->user()->id)  return $this->dataResponse(formatResult('lỗi bất định'));
        $check_add_friend = $this->checkAddFriend($data['user_receive_id']);
        if (!$check_add_friend['success']) return $check_add_friend;
        $data['user_send_id'] = auth()->user()->id;
        $data['status'] = AddFriend::STATUS['not_access'];
        $addfriend = AddFriend::create($data);
        $addfriend['user_send_name'] = $addfriend->user_send->name;
        event(new AddFriendEvent($addfriend));
        return $this->dataResponse(formatResult('gửi lời mời kết bạn thành công', true));
    }

    public function access($id)
    {
        $addfriend = AddFriend::find($id);
        if (!$addfriend)  return $this->dataResponse(formatResult('lời mời không tồn tại'));
        if ($addfriend->user_receive->id != auth()->user()->id) return $this->dataResponse(formatResult('lỗi bất định'));
        $addfriend->status = AddFriend::STATUS['access'];
        $roomfriend = Room::create([
            'name' => 'Friend:' . $addfriend->user_send->name . ' - ' . $addfriend->user_receive->name,
            'type' => Room::TYPE['friend'],
        ]);
        $addfriend->friend_room_id = $roomfriend->id;
        $addfriend->save();
        $addfriend['user_receive_name'] = $addfriend->user_receive->name;
        $roomfriend->members()->attach([
            $addfriend->user_send->id,
            $addfriend->user_receive->id
        ]);
        event(new AccessFriendEvent($addfriend));
        return $this->dataResponse(formatResult('bạn đã chấp nhận lời mời kết bạn của ' . $addfriend->user_send->name, true));
    }

    public function refuse($id)
    {
        $addfriend = AddFriend::find($id);
        if (!$addfriend)  return $this->dataResponse(formatResult('lời mời không tồn tại'));
        if ($addfriend->user_receive_id != auth()->user()->id)  return $this->dataResponse(formatResult('lỗi bất định'));
        $addfriend->delete();
        return $this->dataResponse(formatResult('bạn đã từ chối lời mời kết bạn của ' . $addfriend->user_send->name, true));
    }

    private function checkAddFriend($user_receive_id)
    {
        $addfriend = AddFriend::where('user_receive_id', $user_receive_id)
            ->where('user_send_id', auth()->user()->id)
            ->first();
        $addfriend_receive = AddFriend::where('user_send_id', $user_receive_id)
            ->where('user_receive_id', auth()->user()->id)
            ->first();
        if ($addfriend_receive) return formatResult('hãy chấp nhận lời mời kết bạn của' . $addfriend_receive->user_send->name);
        if (!$addfriend)  return formatResult('chưa kết bạn chưa gửi lời mời', true);
        if ($addfriend->status == AddFriend::STATUS['not_access'])  return formatResult('bạn đã gửi lời mời kết bạn cho người này rồi');
        if ($addfriend->status == AddFriend::STATUS['access'])  return formatResult('người này đang là bạn bè của bạn');
        return formatResult('lỗi bất định');
    }

    public function unFriend($friend_id)
    {
        $user_id = auth()->user()->id;
        $friend = User::find($friend_id);
        $add_friend = AddFriend::where('user_send_id', $user_id)
            ->where('user_receive_id', $friend_id)
            ->where('status', AddFriend::STATUS['access'])
            ->union(
                AddFriend::where('user_receive_id', $user_id)
                    ->where('user_send_id', $friend_id)
                    ->where('status', AddFriend::STATUS['access'])
            )->first();
        if (!$add_friend) return   $this->dataResponse(formatResult('hai người không phải bạn bè'));
        $room =  Room::find($add_friend->friend_room_id);
        $room->members()->detach([$user_id, $friend_id]);
        $add_friend->delete();
        $room->delete();
        return  $this->dataResponse(formatResult('bạn đã hủy kết bạn với' . $friend->name, true));
    }
}
