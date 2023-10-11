<?php


namespace Core\Helpers;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Core\Helpers\StorageHelper;

class ImageHelper
{
    const ORIGINAL_MODE = 0;
    const MODIFY_MODE = 1;
    private $storageHelper;

    protected $storage;
    protected $tmp_directory;
    protected $width = 600;
    protected $height = 600;

    const S3 = 's3';
    const GOOGLE = 'google';

    public function __construct(StorageHelper $storageHelper)
    {
        $this->storage = storage_path("app/public");
        $this->tmp_directory = storage_path("app/public") . "/images";
        $this->storageHelper = $storageHelper;
        $this->createDirectory();
    }

    private function createDirectory()
    {
        if (!is_dir($this->tmp_directory)) {
            mkdir($this->tmp_directory, 0777, true);
        }
    }


    public function convertImage($file, $mode = self::ORIGINAL_MODE)
    {
        $name_file = $this->removeSpecCharacter($file->getClientOriginalName());
        $name = $name_file;

        if ($mode == self::ORIGINAL_MODE) {
            $file->move($this->tmp_directory, $name);
            return $this->tmp_directory . '/' . $name;
        } else {
            $thumb = Image::make($file);
            $height = $thumb->height();
            $width = $thumb->width();
            $full_path = $this->tmp_directory . DIRECTORY_SEPARATOR . $name;
            if ($height == $width) {
                $thumb = $thumb->resize($this->width, $this->height);
                $thumb->save($full_path);
            } else {
                $max = max($width, $height);
                $min = min($width, $height);
                $ratio = $min / $max;
                $bg = Image::canvas($this->width, $this->height);
                $x = 0;
                $y = 0;
                if ($width > $height) {
                    $y = (int)($this->height * (1 - $ratio) / 2);
                    $thumb = $thumb->resize($this->width, $this->height * $ratio);
                } else {
                    $x = (int)($this->width * (1 - $ratio) / 2);
                    $thumb = $thumb->resize($this->width * $ratio, $this->height);
                }
                $full_path = $this->tmp_directory . DIRECTORY_SEPARATOR . $name;
                Log::info('full_path ' . $full_path . 'x = ' . $x . ' y = ' . $y);

                $thumb->save($full_path);
                $bg->insert($full_path, 'top-left', $x, $y);
                $bg->save($full_path);
            }
            return $full_path;
        }
    }

    private function removeSpecCharacter($name)
    {
        return $name;
    }

    public function uploadCloudStorage($storage, $tmp_path)
    {
        $name_file = $this->removeSpecCharacter($tmp_path->getClientOriginalName());
        switch ($storage) {
            case self::S3:
                $name = time() . $tmp_path->getClientOriginalName();
                $path = 'images/' . $name;
                $cloudpath = Storage::disk('s3')->put($path, $tmp_path, 'public');
                return [
                    'name' => $name_file,
                    'url' => Storage::disk('s3')->url($cloudpath)
                ];
            default:
                return '';
        }
    }

    /**
     * @param $tmp_path
     * @param string $size
     * @return false|string
     */
    public function resizeThumb($tmp_path)
    {
        $imgPath = $this->storage . $tmp_path;
        $thumb = Image::make($imgPath);
        $thumb->resize(320, 320, function ($const) {
            $const->aspectRatio();
            $const->upsize();
        });
        $thumb->save($imgPath);
        return $tmp_path;
    }
    public function resizeImageBase64($image, $width, $height)
    {
        $imgPath = $this->storageHelper->uploadBase64File($image, "/fakeImagePath/");

        $thumb = Image::make($image)->resize(300, 300, function ($const) {
            $const->aspectRatio();
        })->save($this->storage . $imgPath);

        $thumb = 'data:image/png;base64,' . base64_encode($thumb);
        $this->storageHelper->removeFile($imgPath);
        return $thumb;
    }
}
