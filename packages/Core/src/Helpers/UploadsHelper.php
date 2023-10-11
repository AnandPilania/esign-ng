<?php

namespace Core\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadsHelper
{
    /**
     * Get File Extension
     * @param $name
     * @param $request
     * @return string
     */
    public static function getFileExtension($name, $request)
    {
        if ($request->hasFile($name)) {
            $image = $request->file($name);
            return $image->getClientOriginalExtension();
        }
        return '';
    }

    /**
     * Upload Origin File
     * @param $image
     * @param $uploadPath
     * @param $name
     * @param $request
     * @return string
     */
    public static function uploadFileOrigin($image, $uploadPath, $name, $request)
    {
        if ($request->hasFile($name)) {
            $imageName = $image->getClientOriginalName();
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            $image->move($uploadPath, $imageName);
            return '/' . $uploadPath . $imageName;
        }
    }

    /**
     * Get Origin Image
     * @param $name
     * @param $uploadPath
     * @param $request
     * @return string
     */
    public static function getOriginImage($name, $uploadPath, $request)
    {
        if ($request->hasFile($name)) {
            $image = $request->file($name);
            return self::uploadFileOrigin($image, $uploadPath, $name, $request);
        }
        return '';
    }


    /**
     * Upload Origin File Receive
     * @param $image
     * @param $uploadPath
     * @param $name
     * @param $request
     * @param $using_storage
     * @return string
     */
    public static function uploadFileOriginReceive($uploadPath, $name, $request, $using_storage)
    {
        $full_path = '';
        if ($request->hasFile($name)) {
            $image = $request->file($name);
            $save_name = $image->hashName();
            $full_path = $uploadPath . $save_name;
            Log::info("using_storage = $using_storage");
            if ($using_storage) {
                if (!Storage::disk(StorageHelper::azureAdminDisk())->exists($uploadPath)) {
                    Storage::disk(StorageHelper::azureAdminDisk())->makeDirectory($uploadPath);
                }
                Storage::disk(StorageHelper::azureAdminDisk())->put($full_path, file_get_contents($image));
            } else {
                if (!Storage::disk('public')->exists($uploadPath)) {
                    Storage::disk('public')->makeDirectory($uploadPath);
                }
                Storage::disk('public')->put($full_path, file_get_contents($image));
            }
        }
        return $full_path;
    }

    /**
     * Get Origin Image
     * @param $name
     * @param $uploadPath
     * @param $request
     * @param bool $using_storage
     * @return string
     */
    public static function getOriginFile($name, $uploadPath, $request, $using_storage = true)
    {
        if ($request->hasFile($name)) {
            return self::uploadFileOriginReceive($uploadPath, $name, $request, $using_storage);
        }
        return '';
    }

    /**
     * @param $name
     * @param $uploadPath
     * @param $request
     * @param bool $storageLocal
     * @return array
     */
    public static function getMultiOriginFile($name, $uploadPath, $request, $storageLocal = false)
    {
        $azurePath = [];
        $tmpPath = [];
        $fileLocalName = [];
        if ($request->hasFile($name)) {
            $files = $request->file($name);
            foreach ($files as $key => $file) {
                $fileLocalName[$key] = $file->getClientOriginalName();
                $save_name = $file->hashName();
                $full_path = $uploadPath . $save_name;
                // upload file to Storage Azure
                if (!Storage::disk(StorageHelper::azureAdminDisk())->exists($uploadPath)) {
                    Storage::disk(StorageHelper::azureAdminDisk())->makeDirectory($uploadPath);
                }
                Storage::disk(StorageHelper::azureAdminDisk())->put($full_path, file_get_contents($file));
                if ($storageLocal) {
                    // move file Storage Local
                    $fileName = $save_name;
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }
                    $file->move($uploadPath, $fileName);
                    $tmpPath[$key] = '/' . $uploadPath . $fileName;
                }
                $azurePath[$key] = $full_path;
            }
        }
        if ($storageLocal) return [$azurePath, $tmpPath, $fileLocalName];
        return $azurePath;
    }

    /**
     * @param string $uploadPath
     * @param $name
     * @param $request
     * @return string
     */
    public static function handelUploadImage($uploadPath = TEMP_IMAGE, $name, $request)
    {
        $full_path = '';
        if ($request->hasFile($name)) {
            $image = $request->file($name);
            $save_name = $image->hashName();
            $full_path = $uploadPath . $save_name;
            if (!Storage::disk()->exists($uploadPath)) {
                Storage::disk()->makeDirectory($uploadPath);
            }
            Storage::disk()->put($full_path, file_get_contents($image));
        }
        return $full_path;
    }

    /**
     * @param string $uploadPath
     * @param $name
     * @param $request
     * @return string
     */
    public static function handleRemoveImage($uploadPath = TEMP_IMAGE, $name, $request)
    {
        $image_path = '';
        if ($request->has($name)) {
            $uploadPath = TEMP_IMAGE;
            $el_delete = $request->get($name);
            $image_path = $uploadPath . $el_delete;
            if (Storage::disk()->exists($image_path)) {
                Storage::disk()->delete($image_path);
            }
        }
        return $image_path;
    }

    public static function downTemp($path, $file)
    {
        $file_name = basename($file);

        Log::info("FileName: $file_name");
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $temp = $path . $file_name;

        stream_copy_to_stream(fopen($file, 'r'), fopen($temp, 'w'));
        Log::info('FILE  ' . $temp);
        return $temp;
    }

    public static function ftpDownloadTmp($ftpConn, $path, $file)
    {
        $file_name = basename($file);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $temp = $path . $file_name;
        try {
            ftp_get($ftpConn, $temp, $file, FTP_BINARY);
            return $temp;
        } catch (Exception $e) {
            Log::error("[ftpDownloadTmp] can not download ftp file: " . $e->getMessage());
            throw new Exception("[ftpDownloadTmp] can not download ftp file: " . $e->getMessage());
        }
    }
}
