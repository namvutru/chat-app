<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Models\MessageFile;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use function App\Helpers\formatResult;

class MessageFileController extends ApiBaseController
{
    public function upload(Request $request)
    {
        $data = $request->all();
        $files = $data['files'];
        $dataUrls = [];
        foreach ($files as $file) {
            $url = $this->uploadFile($file);
            if ($url) array_push($dataUrls, $url);
        }

        if ($files) return $this->dataResponse(formatResult('Tải file thành công', true, $dataUrls));

        return $this->dataResponse(formatResult('Tải file thất bại'));
    }

    private function uploadFile($file)
    {
        return $file->store('temp' . auth()->user()->id, 'public');
    }
}
