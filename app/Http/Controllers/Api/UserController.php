<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Jobs\SendMessage;
use App\Models\AddFriend;
use App\Models\AddGroup;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

use function App\Helpers\formatResult;

class UserController extends ApiBaseController
{
    public function create(StoreUserRequest $request)
    {
        $data =  $request->only(
            'name',
            'email',
            'password'
        );
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        if (!$user) return $this->dataResponse(formatResult('đăng kí thất bại'));

        $user->rooms()->attach($this->getIdGlobal());
        return $this->dataResponse(formatResult('đăng kí thành công', true));
    }

    public function login(Request $request)
    {
        $data = $request->only(
            'email',
            'password'
        );

        $user = User::where('email', $data['email'])->first();

        if (!$user)  return $this->dataResponse(formatResult('email chưa được đăng kí'));


        if ($user && $token = auth('api')->attempt($data)) {
            $loginInfo =  [
                'data' => [
                    'user' => $user,
                    'token' => 'Bearer ' . $token,
                    'expires_in' => time() + config('jwt.ttl') * 60,
                ]
            ];
            return $this->dataResponse(formatResult('đăng nhập thành công', true, $loginInfo));
        } else {
            return $this->dataResponse(formatResult('đăng nhập thất bại'));
        }
    }

    public function logout()
    {
        auth('api')->logout();
        return $this->dataResponse(formatResult('bạn đã đăng xuất', true));
    }

    public function me()
    {
        $user = auth()->user();
        return $this->dataResponse(formatResult(null, true, $user));
    }

    public function addfriendNotAccess()
    {
        $user_id = auth()->user()->id;
        $addfriend = AddFriend::join('users', 'add_friend.user_send_id', 'users.id')
            ->select('add_friend.id', 'users.name as user_send_name')
            ->where('add_friend.user_receive_id', $user_id)
            ->where('add_friend.status', AddFriend::STATUS['not_access'])
            ->orderBy('add_friend.created_at', 'desc')
            ->get();
        return $this->dataResponse(formatResult(null, true, $addfriend));
    }

    public function addgroupNotAccess()
    {
        $user_id = auth()->user()->id;
        $addgroup = AddGroup::join('rooms', 'add_group.room_id', 'rooms.id')
            ->select('add_group.id', 'rooms.name as room_name')
            ->where('add_group.user_receive_id', $user_id)
            ->where('add_group.status', AddFriend::STATUS['not_access'])
            ->orderBy('add_group.created_at', 'desc')
            ->get();
        return $this->dataResponse(formatResult(null, true, $addgroup));
    }

    public function search(Request $request)
    {
        $data = $request->all();
        $query =  User::select('name', 'id', 'email')->orderBy('created_at', 'desc');

        if (isset($data['name'])) {
            $query->where('name', 'like', '%' . $data['name'] . '%');
        }

        if (isset($data['email'])) {
            $query->where('email', $data['email']);
        }

        $query->where('id', '!=', auth()->user()->id);
        $users = $this->handelPaginate($query, $data);
        return $this->dataResponse(formatResult(null, true, $users));
    }

    public function message($room_id, Request $request)
    {
        $data = $request->all();

        $data['user_id'] = auth()->id();
        $data['room_id'] = $room_id;
        $data['status'] = Message::STATUS['show'];
        $message = Message::create($data);
        if (isset($data['files'])) {
            foreach ($data['files'] as $file) {
                $this->save($message->id, 'storage/' . $file);
            }
            $this->deleteTemp();
        }

        SendMessage::dispatch($message);
        return $this->dataResponse(formatResult('tạo tin nhắn thành công', true));
    }

    public function userGroups(Request $request)
    {
        $data =  $request->all();
        $user_id =  auth()->user()->id;
        $query =  Room::join('usertoroom', 'rooms.id', 'usertoroom.room_id')
            ->join('users', 'usertoroom.user_id', 'users.id')
            ->where('users.id', $user_id)
            ->where('rooms.type', Room::TYPE['group']);

        if (isset($data['name'])) {
            $query->where('rooms.name', 'like', '%' . $data['name'] . '%');
        }

        $query->select('rooms.id', 'rooms.name');

        $groups = $this->handelPaginate($query);
        return $this->dataResponse(formatResult(null, true, $groups));
    }

    public function userFriends(Request $request)
    {
        $data =  $request->all();
        $user_id =  auth()->user()->id;
        $query_send =  User::join('add_friend', 'add_friend.user_send_id', 'users.id')
            ->select('users.id as id', 'users.name as name', 'users.email as email')
            ->where('add_friend.user_receive_id', $user_id);
        $query_receive =  User::join('add_friend', 'add_friend.user_receive_id', 'users.id')
            ->select('users.id as id', 'users.name as name', 'users.email as email')
            ->where('add_friend.user_send_id', $user_id);

        $query = $query_send->union($query_receive)->where('add_friend.status', AddFriend::STATUS['access']);

        if (isset($data['name'])) {
            $query->where('users.name', 'like', '%' . $data['name'] . '%');
        }

        $groups = $this->handelPaginate($query);
        return $this->dataResponse(formatResult(null, true, $groups));
    }

    public function userFriendOptions()
    {
        $user_id =  auth()->user()->id;
        $query_send =  User::join('add_friend', 'add_friend.user_send_id', 'users.id')
            ->select('users.id as id', 'users.name as name')
            ->where('add_friend.user_receive_id', $user_id);
        $query_receive =  User::join('add_friend', 'add_friend.user_receive_id', 'users.id')
            ->select('users.id as id', 'users.name as name')
            ->where('add_friend.user_send_id', $user_id);

        $query = $query_send->union($query_receive)->where('add_friend.status', AddFriend::STATUS['access']);

        $groups = $query->get()->toArray();
        return $this->dataResponse(formatResult(null, true, $groups));
    }
}
