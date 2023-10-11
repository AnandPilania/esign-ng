<?php


namespace Admin\Controllers;


use Core\Controllers\eCBaseController;
use Illuminate\Http\Request;

class AdminPasswordController extends eCBaseController
{
    public function resetPasword(Request $request)
    {
        $rules = [
          'email' => 'required|email'
        ];
    }
}
