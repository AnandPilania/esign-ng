<?php


namespace Customer\Controllers;

use Core\Controllers\eCBaseController;
use Core\Helpers\StorageHelper;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class eCTestController extends eCBaseController
{
    private $storageHelper;

    /**
     * eCTestController constructor.
     * @param $storageHelper
     */
    public function __construct(StorageHelper $storageHelper)
    {
        $this->storageHelper = $storageHelper;
    }

    public function uploadFile(Request $request) {
        Log::info("[eCTestController] uploadFile -- BEGIN ");
        $path = $this->storageHelper->uploadFile($request->file('test'), '/internal/' . time() . '/');
        Log::info("[eCTestController] uploadFile -- END " . $path);
        return $this->sendResponse($path, 'OK');
    }

    public function uploadFileV2(Request $request) {
        $en = new Encrypter(base64_decode('0iIB7rnoD54fsENE4B7D0xhCu35DG82MX11ThxiE+dM='), 'AES-256-CBC');
        Log::info($en->encryptString("nammai252087"));
        Log::info("::>" . $en->decryptString("eyJpdiI6Ijc3TC85djgrY1JQSTVILzNmU09SSFFcdTAwM2RcdTAwM2QiLCJ2YWx1ZSI6ImxWRS9McXdrMmpEeVAvanV6TUwyYndcdTAwM2RcdTAwM2QiLCJtYWMiOiJmYjUzN2E3ODNhNTU4MzhlMmM1ODI4YTA3NDQwZmFjMzI0OTM1ZGM1NTA3YjE3MjlhOWE0ZDQ4ZjM0MzgwMjE3In0="));
        Log::info(Crypt::encryptString("nammai252087"));
        Log::info("[eCTestController] uploadFile -- BEGIN ");
        $path = $this->storageHelper->uploadFile($request->file('test'), '/internal/' . time() . '/');
        Log::info("[eCTestController] uploadFile -- END " . $path);
        return $this->sendResponse($path, 'OK');
    }
}
