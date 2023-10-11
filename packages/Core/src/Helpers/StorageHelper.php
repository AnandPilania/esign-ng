<?php


namespace Core\Helpers;


use Illuminate\Support\Facades\Storage;

class StorageHelper
{

    protected $storage;

    /**
     * StorageHelper constructor.
     * @param string $configure
     */
    public function __construct($configure = 'public')
    {
        $this->storage = Storage::disk($configure);
    }

    public function uploadFile($file, $upload_path, $is_keep_name = true)
    {
        if (!$this->storage->exists($upload_path)) {
            $this->storage->makeDirectory($upload_path);
        }

        if ($is_keep_name)
            $save_name = $file->getClientOriginalName();
        else
            $save_name = $file->hashName();
        $full_path = $upload_path . $save_name;
        $this->storage->put($full_path, file_get_contents($file));

        return $full_path;
    }

    public function copyFile($source_path, $destination_path, $fileName) {
        if (!$this->storage->exists($destination_path)) {
            $this->storage->makeDirectory($destination_path);
        }
        if ($this->storage->exists($source_path)) {
            $this->storage->copy($source_path, $destination_path . $fileName);
            return true;
        }
        return false;
    }

    public function uploadBase64File($image, $upload_path) {
        if (!$this->storage->exists($upload_path)) {
            $this->storage->makeDirectory($upload_path);
        }
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace('data:image/jpg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = Common::randomString().'.'.'png';
        $full_path = $upload_path . $imageName;
        $this->storage->put($full_path, base64_decode($image));
        return $full_path;
    }

    public function downloadFile($file)
    {
        $path = $this->storage->path($file);
        $fp = fopen($path, 'r');
        $tmp = stream_get_contents($fp);
        fclose($fp);
        return $tmp;
    }

    public function removeFile($file)
    {
        if ($this->storage->exists($file)) {
            $this->storage->delete($file);
            return true;
        }
        return false;
    }

    public static function ftpMoveResource($ftpConn, $path, $new_path)
    {
        return ftp_rename($ftpConn, $path, $new_path);
    }
}
