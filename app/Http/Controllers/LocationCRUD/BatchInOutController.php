<?php

namespace App\Http\Controllers\LocationCRUD;

use App\Http\Controllers\Controller;
use App\Services\Location\LocationBindSkuDownloadService;
use App\Services\Location\LocationUploadService;
use App\Services\Location\LocationDownloadService;
use Illuminate\Http\Request;


class BatchInOutController extends Controller
{
    public function dowloadFile(Request $request)
    {
        $fileName = app(LocationDownloadService::class)
            ->exec();
        return ($fileName);
    }

    public function uploadFile(Request $request)
    {
        $request->validate(
            [
                'file' => 'required|mimes:csv,xls,xlsx|max:2048'
            ],
            [
                'file.required' => '無上傳檔案。',
                'file.mimes' => '檔案格式錯誤 (可接受的格式為 CSV/EXCEL)。',
                'file.max' => '檔案大小上限為 2 MB。'
            ]
        );
        $fileName = time() . '_' . $request->file->getClientOriginalName();
        $filePath = $request->file('file')->storeAs('locationUpload', $fileName);
        return app(LocationUploadService::class)
            ->setFilePath($filePath)
            ->exec();
    }

    public function uploadSkuReturnFile(Request $request)
    {
        $request->validate(
            [
                'file' => 'required|mimes:csv,xls,xlsx|max:2048'
            ],
            [
                'file.required' => '無上傳檔案。',
                'file.mimes' => '檔案格式錯誤 (可接受的格式為 CSV/EXCEL)。',
                'file.max' => '檔案大小上限為 2 MB。'
            ]
        );
        $fileName = time() . '_' . $request->file->getClientOriginalName();
        $filePath = $request->file('file')->storeAs('DownloadUpload', $fileName);
        return $fileName;
    }

    public function dowloadSkuFile(Request $request,string $file)
    {
        $fileName = $file;
        $filePath = "DownloadUpload/".$fileName;
        return app(LocationBindSkuDownloadService::class)
                ->setFilePath($filePath)
                ->exec();
    }
}
