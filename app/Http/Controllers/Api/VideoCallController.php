<?php

namespace App\Http\Controllers\Api;

use App\Events\AccessCallVideoEvent;
use App\Events\CallVideoEvent;
use App\Events\CandidateEvent;
use App\Http\Controllers\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\Constraint\IsTrue;

use function App\Helpers\formatResult;
use function PHPUnit\Framework\isTrue;

class VideoCallController extends ApiBaseController
{
    public function callVideo(Request $request)
    {
        $data = $request->only(
            'user_receive_id',
            'user_send_peer_id',
        );
        $user_receive =  User::find($data['user_receive_id']);
        if (!$user_receive) return $this->dataResponse(formatResult('người này không tồn tại'));
        $user =  auth()->user();
        $data['user_send_id'] = $user->id;
        $data['user_send_name'] = $user->name;
        event(new CallVideoEvent($data));
        return $this->dataResponse(formatResult('bạn đã yêu cầu cuộc gọi tới ' . $user_receive->name, true));
    }

    public function accessVideoCall(Request $request)
    {
        $data = $request->only(
            'user_send_id',
            'answer'
        );
        $user_send =  User::find($data['user_send_id']);
        if (!$user_send) return $this->dataResponse(formatResult('người này không tồn tại'));
        $user =  auth()->user();
        $data['user_receive_id'] = $user->id;
        $data['user_receive_name'] = $user->name;
        event(new AccessCallVideoEvent($data));
        return $this->dataResponse(formatResult('bạn đã chấp nhận cuộc gọi từ ' . $user_send->name, true));
    }

    public function candidateIce(Request $request)
    {
        $data = $request->only(
            'candidate',
            'user_receive_id'
        );
        event(new CandidateEvent($data));
        return $this->dataResponse(formatResult('candidate ice', true));
    }
}
