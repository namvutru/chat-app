<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\MessageFile;
use App\Models\Room;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ApiBaseController extends Controller
{
    public function dataResponse($result)
    {
        $data = [];
        $data['success'] = $result['success'];
        if ($result['message']) $data['message'] = $result['message'];
        if ($result['data']) $data['data'] = $result['data'];

        $status = Response::HTTP_OK;
        if (!$result['success']) $status = Response::HTTP_CONFLICT;

        return response()->json($data, $status);
    }

    public function save($message_id, $url)
    {
        if (file_exists($url)) {
            $parts = explode('/', $url);
            $name = end($parts);
            $namefiles = explode('.', $name);
            $tailname = end($namefiles);
            $image_tails = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'tiff', 'webp', 'svg', 'heif', 'heic', 'raw', 'psd', 'ico'];
            $video_tails = [
                'mp4', 'avi', 'mov', 'wmv', 'mkv', 'flv', 'webm', 'mpeg', 'mpg', '3gp', 'ogg', 'mts', 'm2ts'
            ];
            if (in_array($tailname, $image_tails)) {
                $newurl = 'storage/images/' . $name;

                if ($url != $newurl) {
                    if (!Storage::exists('public/images')) Storage::makeDirectory('public/images');
                    File::copy($url, 'storage/images/' . $name);
                    File::delete($url);
                }

                MessageFile::create([
                    'message_id' => $message_id,
                    'url' => 'images/' . $name,
                    'type_file' => MessageFile::TYPE['image']
                ]);
            } else if (in_array($tailname, $video_tails)) {
                $newurl = 'storage/videos/' . $name;

                if ($url != $newurl) {
                    if (!Storage::exists('public/videos')) Storage::makeDirectory('public/videos');
                    File::copy($url, 'storage/videos/' . $name);
                    File::delete($url);
                }

                MessageFile::create([
                    'message_id' => $message_id,
                    'url' => 'videos/' . $name,
                    'type_file' => MessageFile::TYPE['video']
                ]);
            } else {
                $newurl = 'storage/others/' . $name;

                if ($url != $newurl) {
                    if (!Storage::exists('public/others')) Storage::makeDirectory('public/others');
                    File::copy($url, 'storage/others/' . $name);
                    File::delete($url);
                }

                MessageFile::create([
                    'message_id' => $message_id,
                    'url' => 'others/' . $name,
                    'type_file' => MessageFile::TYPE['other']
                ]);
            }
        }
    }

    public function deleteTemp()
    {
        Storage::deleteDirectory('temp' . auth()->user()->id);
    }

    public function getIdGlobal()
    {
        $rooms = Room::select('id')->where('type', Room::TYPE['global'])->get();
        $room_ids = array_map(function ($item) {
            return $item['id'];
        }, array_values($rooms->toArray()));

        return $room_ids;
    }

    public function handelPaginate($query, $data = [], $perpage = 7)
    {
        return $query->paginate($perpage)->appends($data);
    }
}
