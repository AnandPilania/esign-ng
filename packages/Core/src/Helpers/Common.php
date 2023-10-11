<?php

namespace Core\Helpers;

use App\Http\Controllers\Controller;
use Core\Models\Admin;
use Core\Models\Agency;
use Core\Models\User;
use Core\Repositories\RepositoryInterface;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;


class Common
{

    const JP = 1;
    const DEVICE_PC = 1;
    const DEVICE_SP = 2;
    const DEVICE_OTHER = 3;

    public static function getConverterServer() {
        $url = Config::get('app.signServerUrl');
        Log::info("getConverterServer: " . $url);
        return $url;
    }
    public static function getConverterPassword() {
        return Config::get('app.signServerPass');
    }
    public static function getSignUrl() {
        return Config::get('app.signUrl');
    }

    /**
     * Encryption Data Input.
     *
     * @param mixed $data
     * @return string
     */
    public static function cryptData($data)
    {
        return base64_encode($data);
    }

    /**
     * Encryption Data Input.
     *
     * @param mixed $data
     * @return string
     */
    public static function decryptData($data)
    {
        return base64_decode($data);
    }

    /**
     * @param string $email
     * @return mixed
     */
    public static function hashUnique($email)
    {
        $date = Carbon::now(config('app.timezone'));
        $expiredDate = $date->addDays(config('settings.token_register_expired_date')); // ExpireDate 2 day
        $token = $expiredDate . 'andemail' . $email;
        return Crypt::encrypt($token);
    }

    /**
     * @param string $email
     * @return mixed
     */
    public static function hashValid($email)
    {
        $date = Carbon::now(config('app.timezone'));
        $expiredDate = $date->addDays(config('settings.token_forgot_expired_date')); // ExpireDate 2 day
        $token = $expiredDate . 'andemail' . $email;
        return Crypt::encrypt($token);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function hashValue($id)
    {
        $date = Carbon::now(config('app.timezone'));
        $current_date = $date->toDateTimeString();
        $current_date = strtotime($current_date);
        $hash = $current_date . $id;
        return Hash::make($hash);
    }

    /**
     * @param string $string
     * @return array|bool
     */
    public static function convertStringToArray($string)
    {
        if (empty($string)) {
            return false;
        }
        return explode('andemail', $string);
    }

    /**
     * @param $gender
     * @return mixed
     */
    public static function strGender($gender)
    {
        $genders = ['指定なし', '男性', '女性'];
        if (app()->getLocale() == LANGUAGE_EN) {
            $genders = ['Unspecified', 'Male', 'Female'];
        }
        return $genders[$gender];
    }

    /**
     * @param $word
     * @param int $limit
     * @param string $dotted
     * @return int|string
     */
    public static function limitWord($word, $limit = 10, $dotted = '...')
    {
        $limit = Str::words($word, $limit, $dotted);
        return $limit;
    }

    /**
     * @param $datetime
     * @return bool|string
     */
    public static function formatDate($datetime)
    {
        if ($datetime) {
            return Carbon::parse($datetime)->format('Y-m-d');
        }
        return false;
    }

    /**
     * @param number $price
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep
     * @return bool|string
     */
    public static function formatPrice($price, $decimals = 0, $dec_point = ".", $thousands_sep = ",")
    {
        if (!is_numeric($price)) {
            return false;
        }
        $price = intval($price);
        $price_format = number_format($price, $decimals, $dec_point, $thousands_sep);
        return CURRENCY . $price_format;
    }

    /**
     * @param number $number
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep
     * @return bool|string
     */
    public static function formatInt($number, $decimals = 0, $dec_point = ".", $thousands_sep = ",")
    {
        if (!$number) {
            return false;
        }
        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }

    /**
     * @param $datetime
     * @return string
     */
    public static function checkNotifyNew($datetime)
    {
        $current_date = Carbon::now(config('app.timezone'))->format('Y-m-d');
        $datetime = Carbon::parse($datetime)->format('Y-m-d');
        $cacl = strtotime($current_date) - strtotime($datetime);
        $day = $cacl / 3600 / 24;
        // NEW（7日以内ならNEWを表示する）
        if ($day <= 7) {
            return 'NEW';
        }
        return '';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param int $status
     * @param $user_id
     * @param string $expire_time
     * @return string
     */
    public static function checkStatusUser($status = 1, $user_id, $expire_time = '')
    {
        $str = '';
        switch ($status) {
            case 1:
                // No login
                $dt = Carbon::now(config('app.timezone'));
                $user = User::find($user_id);
                $url_regis = route('user.register.token', ['token' => $user->hash_value]);
                if ($expire_time && $expire_time > $dt) {
                    $str = '未ログイン <a href="javascript:void(0);" class="coppy-clipboard" data-url="' . $url_regis . '" title="URLコピー"><i class="fa fa-clipboard" aria-hidden="true"></i></a>';
                    $str .= '<a data-id="' . $user_id . '" data-status="' . $status . '" class="re_send_email" href="javascript:void(0);"><i class="fa fa-envelope" aria-hidden="true" title="初回登録メール再発行"></i></a>';
                } else {
                    $str = '未ログイン 有効期限切れ <a href="javascript:void(0);" class="coppy-clipboard" data-url="' . $url_regis . '" title="URLコピー"><i class="fa fa-clipboard" aria-hidden="true"></i></a>';
                    $str .= '<a data-id="' . $user_id . '" data-status="' . $status . '" class="re_send_email" href="javascript:void(0);"><i class="fa fa-envelope" aria-hidden="true" title="初回登録メール再発行"></i></a>';
                }
                # code...
                break;
            case 2:
                // Active
                $str = '有効 <a data-id="' . $user_id . '" class="lock_user" href="javascript:void(0);"><i class="fa fa-lock" aria-hidden="true" title="ック"></i></a>';
                break;
            case 3:
                // Lock
                $str = 'ロック <a data-id="' . $user_id . '" class="unlock_user" href="javascript:void(0);"><i class="fa fa-unlock" aria-hidden="true" title="ロック"></i></a>';
                break;
            case 4:
                // Stop
                $str = '<span class="red">利用停止</span>';
                break;
            default:
                break;
        }
        return $str;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param $status
     * @return string
     */
    public static function statusContact($status)
    {
        switch ($status) {
            case 2:
                return '確認';
            default:
                return '未確認';
        }
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @param $status
     * @return string
     */
    public static function statusCatalog($status)
    {
        switch ($status) {
            case 1:
                return trans('messages.catalog.table.status_active');
            default:
                return trans('messages.catalog.table.status_inactive');
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param $status
     * @return string
     */
    public static function statusAttribute($status)
    {
        switch ($status) {
            case 1:
                return '確認';
            default:
                return '未確認';
        }
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @param $route_back
     * @param array $request_url
     * @return string
     */
    public static function paramUrlBack($route_back, $request_url = [])
    {
        if ($request_url) {
            $url_back = $route_back . '?';
            foreach ($request_url as $key => $value) {
                $url_back .= '' . $key . '=' . $value . '&';
            }
        } else {
            $url_back = $route_back;
        }
        return $url_back;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param $categories
     * @param int $id_parent
     * @param array $arg_check
     * @return void
     */
    public static function showCategories($categories, $id_parent = 0, $arg_check = array())
    {
        # Get all catalog with parent_id = 0
        $menu_tmp = array();
        foreach ($categories as $key => $item) {
            # if parent_id = current id
            if ((int)$item['parent_id'] == (int)$id_parent) {
                $menu_tmp[] = $item;
                // After adding the menu archive boundary in the iteration, unset it from the menu list in the next step
                unset($categories[$key]);
            }
        }
        # STEP 2: MAKE MENU UNDER THE MENU LIST IN STEP 1 The recursive stop condition is until the menu is no longer available
        if ($menu_tmp) {
            echo '<ul>';
            foreach ($menu_tmp as $item) {
                echo '<li value="' . $item['id'] . '">';
                if (in_array($item['id'], $arg_check)) {
                    echo '<label><input checked class="category-checkbox" type="checkbox" value="' . $item['id'] . '" name="categories[]" >&nbsp;&nbsp;' . $item['name'] . '</label>';
                } else {
                    echo '<label><input class="category-checkbox" type="checkbox" value="' . $item['id'] . '" name="categories[]" >&nbsp;&nbsp;' . $item['name'] . '</label>';
                }
                // Enter the list of unmatched menus and the parent id of the current menu
                self::showCategories($categories, $item['id'], $arg_check);
                echo '</li>';
            }
            echo '</ul>';
        }
    }

    /**
     * @param int $status
     * @return string
     */
    public static function checkStatus($status)
    {
        $html = '';
        switch ($status) {
            case '1':
                $html .= '<span class="label-danger status-label">Deactivate</span>';
                break;
            case '0':
                $html .= '<span class="label-success status-label">Active</span>';
                break;
            default:
                # code...
                break;
        }
        return $html;
    }

    /**
     * common set per page row cookie
     * @param $request
     * @param string $cookieName
     * @param Controller $controller
     * @return JsonResponse
     */
    public static function setPerPageCookie($request, string $cookieName, Controller $controller)
    {
        $perPage = $request->per_page;
        setCookieSite(COOKIE_INFO_SITE, [$cookieName => $perPage]);
        return $controller->respondAjax(true, 'success');
    }

    /**
     * common function ajax delete record by id
     * @param $request
     * @param RepositoryInterface $repository
     * @param $model
     * @param string $guardName
     * @param string $routeIndex
     * @param int $take
     * @param array $param
     * @param string $cookieName
     * @param string $configName
     * @return JsonResponse
     */
    public static function destroyAccount($request, $repository, $model, $guardName, $routeIndex, $take, $param, $cookieName, $configName)
    {
        try {
            $id = $request->id;
            $object = $repository->find($id);
            $param['page'] = $request->current_page;
            $urlCallback = route($routeIndex, $param);
            $tableName = $model::TABLE_NAME ?? '';
            if ($object) {
                DB::beginTransaction();
                if ($model == Admin::class && $object->master_flag == $model::CAN_NOT_DELETE) {
                    return response()->json([
                        'status' => false,
                        'title' => trans('admin/messages.common.error_title'),
                        'msg' => trans('admin/messages.common.destroy.no_permission'),
                        'url_callback' => $urlCallback,
                        'html' => 'error',
                    ]);
                }
                $object->delete_flag = true;
                $object->save();
                if ($model == Agency::class || $model == User::class) {
                    if (!empty($object->account)) {
                        $object->account->delete_flag = true;
                        $object->account->save();
                    }
                }
                $perPage = self::getPerPage($take, $cookieName, $configName);
                $lastPage = $model::paginate($perPage)->lastPage();
                $pageCurrent = $request->current_page;
                if ($pageCurrent > $lastPage) {
                    $pageCurrent = $lastPage;
                }
                $param['page'] = $pageCurrent;
                $urlCallback = route($routeIndex, $param);

                if ($id == Auth::guard($guardName)->id()) {
                    Auth::guard($guardName)->logout();
                }
                DB::commit();
                Log::info('[Common][destroyAccount][' . $model . '] destroy account success by id = ' . $id);
                return response()->json([
                    'title' => trans('admin/messages.common.destroy.title_success'),
                    'msg' => trans('admin/messages.common.destroy.msg_success'),
                    'url_callback' => $urlCallback,
                    'html' => 'success',
                    'status' => true,
                ]);
            }
            Log::info('[Common][destroyAccount][' . $model . '] not found by id = ' . $id);
            return response()->json([
                'status' => false,
                'title' => trans('admin/messages.common.error_title'),
                'msg' => $tableName . $id . trans('admin/messages.common.destroy.destroy_not_found_object'),
                'html' => 'error',
                'url_callback' => $urlCallback,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[Common][destroyAccount][' . $model . '] fail cause : ' . $e->getMessage() . renderRequestLog($request));
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * common function ajax change status account
     * @param RepositoryInterface $repository
     * @param $request
     * @param string $status
     * @return JsonResponse
     */
    public static function changeStatus($repository, $request, $status)
    {
        $model = $repository->getModel();
        try {
            $id = $request->id;
            $object = $repository->find($id);
            $tableName = $model::TABLE_NAME ?? '';
            if ($object) {
                DB::beginTransaction();
                $object->status = $status;
                $object->save();
                DB::commit();
                Log::info('[Common][changeStatus][' . $model . ']change status success by id = ' . $id);
                return response()->json([
                    'status' => true,
                    'title' => trans('admin/messages.common.status.title_success'),
                    'msg' => trans('admin/messages.common.status.msg_success'),
                    'html' => 'success'
                ]);
            }
            Log::info('[Common][changeStatus][' . $model . '] not found by id = ' . $id . renderRequestLog($request));
            return response()->json([
                'status' => false,
                'title' => trans('admin/messages.common.error_title'),
                'msg' => $tableName . $id . trans('admin/messages.common.destroy.change_status_not_found_object'),
                'html' => 'error'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[Common][changeStatus][' . $model . '] fail cause : ' . $e->getMessage() . renderRequestLog($request));
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Automatically generate a 12-character password consisting of at least 1 character after each half-width lower case English character,
     * half-width English character, numeric character, symbol.
     * @param int $length
     * @return string
     */
    final public function generatorPassword($length)
    {
        $symbols = array();
        $password = '';
        $symbols["lower_case"] = 'abcdefghijklmnopqrstuvwxyz';
        $symbols["upper_case"] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $symbols["numbers"] = '1234567890';
        $symbols["special_symbols"] = '!?~@#-_+<>[]{}';

        $rand_upper_case = rand(1, 3);
        $rand_numbers = rand(1, 3);
        $rand_special_symbols = rand(1, 3);
        $rand_lower_case = $length - $rand_upper_case - $rand_numbers - $rand_special_symbols;

        $password .= $this->randomChars($symbols["upper_case"], $rand_upper_case);
        $password .= $this->randomChars($symbols["numbers"], $rand_numbers);
        $password .= $this->randomChars($symbols["special_symbols"], $rand_special_symbols);
        $password .= $this->randomChars($symbols["lower_case"], $rand_lower_case);
        $password = str_shuffle($password);
        return $password;
    }

    /**
     * Function that retrieves a set number of random characters
     * from a string.
     *
     * @param $str  string that you want to get random characters from.
     * @param $numChars  number of random characters that you want.
     * @return false|string
     */
    private function randomChars(string $str, $numChars)
    {
        //Return the characters.
        return substr(str_shuffle($str), 0, $numChars);
    }

    /**
     * get records per page
     * @param int $take
     * @param string $cookieName
     * @param string $configName
     * @return int
     */
    public static function getPerPage($take, $cookieName, $configName = 'settings.paginate.event')
    {
        $perPage = $take;
        $getCookieSite = getCookieSite(COOKIE_INFO_SITE, $cookieName);
        if ($getCookieSite && in_array($getCookieSite, config($configName))) {
            $perPage = $getCookieSite;
        }
        return $perPage;
    }

    /**
     * @param $category
     * @return int
     */
    public static function getAllCountCategory($category)
    {
        $count = count($category);
        foreach ($category as $cate)
            if (!empty($cate->children)) {
                $count += self::getAllCountCategory($cate->children);
            }
        return $count;
    }

    /**
     * check if it is going on or not
     * @param datetime $start
     * @param datetime $end
     * @return bool
     */
    public static function checkEventInProcess($start, $end)
    {
        $now = Carbon::now(config('app.timezone'));
        $currentDate = strtotime($now);
        $start = strtotime($start);
        $end = strtotime($end);
        if ($start <= $currentDate && $currentDate < $end) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $datetime
     * @return string
     */
    public static function displayDatetime($datetime)
    {
        if (empty($datetime)) return '';
        if (config('app.timezone') == 'Asia/Tokyo') {
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            $day = $weekdays[+date('w', strtotime($datetime))];
            $datetime = date('Y年m月d日（' . $day . '）H:i', strtotime($datetime));
        }
        return $datetime;
    }

    /**
     * @param $date
     * @param bool $displayDay
     * @return string
     */
    public static function displayDate($date, $displayDay = true)
    {
        if (empty($date)) return '';
        if (config('app.timezone') == 'Asia/Tokyo') {
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            $day = $weekdays[+date('w', strtotime($date))];
            if ($displayDay) {
                $date = date('Y年m月d日（' . $day . '）', strtotime($date));
            } else {
                $date = date('Y年m月d日', strtotime($date));
            }
        }
        return $date;
    }

    /**
     * set cookies for the site
     * @param $key
     * @param $value
     * @param $cookieKey : COOKIE_INFO_SITE || COOKIE_INFO_LOGIN
     * @return bool
     */
    public static function setCookie($key = array(), $value = array(), $cookieKey = COOKIE_INFO_SITE)
    {
        try {
            $cookieKey = PREFIX_SECURE . $cookieKey;
            $oldInfoBase64 = isset($_COOKIE[$cookieKey]) ? $_COOKIE[$cookieKey] : null;
            $info = [];
            if ($oldInfoBase64) {
                $info = \GuzzleHttp\json_decode(Common::decryptData($oldInfoBase64), true);
            }
            foreach ($key as $index => $item) {
                $info[$item] = $value[$index];
            }

            $newInfoBase64 = Common::cryptData(\GuzzleHttp\json_encode($info));
            setcookie($cookieKey, $newInfoBase64, time() + (config('settings.cookie.expire_time')), "/");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * set cookies for the site
     * @param $key
     * @param $default
     * @param $cookieKey : COOKIE_INFO_SITE || COOKIE_INFO_LOGIN
     * @return string
     */
    public static function getCookie($key = null, $default = null, $cookieKey = COOKIE_INFO_SITE)
    {
        $cookieKey = PREFIX_SECURE . $cookieKey;
        $oldInfoBase64 = isset($_COOKIE[$cookieKey]) ? $_COOKIE[$cookieKey] : null;
        if ($oldInfoBase64) {
            $info = \GuzzleHttp\json_decode(Common::decryptData($oldInfoBase64), true);
            if (!$key) {
                return $info;
            } else if (isset($info[$key])) {
                return $info[$key];
            }
        }
        return $default;
    }

    /**
     * set cookies for the site
     * @param $key
     * @param $cookieKey : COOKIE_INFO_SITE || COOKIE_INFO_LOGIN
     * @return bool
     */
    public static function deleteCookie($key, $cookieKey = COOKIE_INFO_SITE)
    {
        try {
            $cookieKey = PREFIX_SECURE . $cookieKey;
            $oldInfoBase64 = isset($_COOKIE[$cookieKey]) ? $_COOKIE[$cookieKey] : null;
            $info = [];
            if ($oldInfoBase64) {
                $info = \GuzzleHttp\json_decode(Common::decryptData($oldInfoBase64), true);
                if (is_array($key)) {
                    foreach ($key as $keyItem) {
                        if (isset($info[$keyItem])) {
                            unset($info[$keyItem]);
                        }
                    }
                } else {
                    if (isset($info[$key])) {
                        unset($info[$key]);
                    }
                }
            }

            $newInfoBase64 = Common::cryptData(\GuzzleHttp\json_encode($info));
            setcookie($cookieKey, $newInfoBase64, time() + (config('settings.cookie.expire_time')), "/");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * common get condition by start_at and end_at for event, feature and matter
     * @param array $condition
     * @param $request
     * @return array
     */
    public static function getConditionDatetime($condition, $request)
    {
        if (!empty($request->input('start_at'))) {
            $condition[] = ['start_at', '>=', $request->input('start_at')];
        }
        if (!empty($request->input('end_at'))) {
            $condition[] = ['end_at', '<=', $request->input('end_at')];
        }
        return $condition;
    }

    /**
     * common get order by name and start_at, end_at for event, feature and matter
     * @param string $orderBy
     * @param $request
     * @return string
     */
    public static function getOrderByNameAndDatetime($orderBy, $request)
    {
        if (!empty($request->input('sort')) && !empty($request->input('direction')) && in_array($request->input('direction'), ['asc', 'desc'])) {
            if ($request->input('sort') == SORT_BY_DATETIME) {
                $orderBy = 'start_at ' . $request->input('direction') . ', end_at ' . $request->input('direction');
            } elseif ($request->input('sort') == SORT_BY_NAME) {
                $orderBy = 'name ' . $request->input('direction');
            }
        }
        return $orderBy;
    }

    public static function getOrderBy($orderBy, $request)
    {
        if (!empty($request->input('sort')) && !empty($request->input('direction')) && in_array($request->input('direction'), ['asc', 'desc'])) {
            $direction = $request->input('direction');
            switch ($request->input('sort')) {
                case SORT_BY_EMAIL :
                    $orderBy = 'email ' . $direction;
                    break;
                case SORT_BY_NAME :
                    $orderBy = 'name ' . $direction;
                    break;
                case SORT_BY_DATETIME :
                    $orderBy = 'start_at ' . $direction . ', end_at ' . $direction;
                    break;
                case SORT_BY_GROUP_NAME :
                    $orderBy = 'group_name ' . $direction;
                    break;
                case SORT_BY_MANAGE_NAME :
                    $orderBy = 'manager_name ' . $direction;
                    break;
                case SORT_BY_URL_KEY :
                    $orderBy = 'url_key ' . $direction;
                    break;
                case SORT_BY_TITLE :
                    $orderBy = 'title ' . $direction;
                    break;
                default :
                    $orderBy = 'id ' . $direction;
                    break;
            }
        }
        return $orderBy;
    }

    /**
     * @param $model
     * @param $attribute
     * @param $lang
     * @param string $translation
     * @return mixed
     */
    public static function displayDataLanguage($model, $attribute, $lang, $translation = 'translation')
    {
        if (array_key_exists($lang, config('settings.lang')) && $lang != DEFAULT_LANG) {
            if (!empty($model->$translation) && !$model->$translation->isEmpty()) {
                foreach ($model->$translation as $data) {
                    if ($data->language_id == $lang) {
                        return $data->$attribute;
                    }
                }
            }
        }
        return $model->$attribute;
    }

    /**
     * @param $key
     * @param $value
     * @param null $default
     * @return string
     */
    public static function checkSelected($key, $value, $default = null)
    {
        $request = request();
        if ((!empty(old($key)) || old($key) != null) && old($key) == $value) {
            return 'selected';
        } elseif ((!empty($request->get($key)) || $request->get($key) != null) && $request->get($key) == $value) {
            return 'selected';
        } elseif (empty(old($key)) && empty($request->get($key)) && $default && $default == $value) {
            return 'selected';
        } else {
            return '';
        }
    }

    /**
     * @param $key
     * @return mixed|string
     */
    public static function getOldValue($key)
    {
        $request = request();
        if (!empty(old($key))) {
            return old($key);
        } elseif (!empty($request->get($key))) {
            return $request->get($key);
        }
        return '';
    }

    /**
     * Check Regex
     * @Param $regex
     * @Param $value
     * return bool
     */
    public static function checkRegex($regex, $value)
    {
        $len = mb_strlen($value, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $characters = mb_substr($value, $i, 1, 'UTF-8');
            if (!preg_match($regex, $characters)) {
                return false;
            }
        }
        return true;
    }

    /**
     * check customer login or not
     */
    public static function authCheckCustomer()
    {
        $customerID = Helpers::getCustomerId();
        return !empty($customerID);
    }

    /**
     * @param array $breadcrumb
     * @return View
     */
    public static function getBreadcrumbs($breadcrumb)
    {
        return view('partials.breadcrumbs', ['breadcrumb' => $breadcrumb]);
    }

    /**
     * get image product
     * @param $product
     * @return string
     */
    public static function getImageProduct($product)
    {
        if (!empty($product->image1)) {
            return $product->image1;
        }
        if (!empty($product->image2)) {
            return $product->image2;
        }
        if (!empty($product->image3)) {
            return $product->image3;
        }
        if (!empty($product->image4)) {
            return $product->image4;
        }
        if (!empty($product->image5)) {
            return $product->image5;
        }
        if (!empty($product->image6)) {
            return $product->image6;
        }
        return '';
    }

    /**
     * getEfficacyProduct
     * @param  $proposalProduct
     * @param  $product
     * @return String
     */
    public static function getEfficacyProduct($proposalProduct, $product)
    {
        if (is_object($proposalProduct)) {
            $efficacyProposalProduct = getValueTran($proposalProduct, 'efficacy');
            $efficacyProduct = getValueTran($product, 'efficacy');

            if (!empty($efficacyProposalProduct) && trim($efficacyProposalProduct) != '') {
                return $efficacyProposalProduct;
            }
            return $efficacyProduct;
        }

        if (!empty($proposalProduct) && trim($proposalProduct) != '') {
            return $proposalProduct;
        }
        return $product;
    }

    /**
     * getCapacityProduct
     * @param  $proposalProduct
     * @param  $product
     * @return String
     */
    public static function getCapacityProduct($proposalProduct, $product)
    {
        if (is_object($proposalProduct)) {
            $capacityProposalProduct = getValueTran($proposalProduct, 'capacity');
            $capacityProduct = getValueTran($product, 'capacity');

            if (!empty($capacityProposalProduct) && trim($capacityProposalProduct) != '') {
                return $capacityProposalProduct;
            }
            return $capacityProduct;
        }

        if (!empty($proposalProduct) && trim($proposalProduct) != '') {
            return $proposalProduct;
        }
        return $product;
    }

    /**
     * getNameProduct
     * @param  $nameProposalProduct
     * @param  $nameProduct
     * @return String
     */
    public static function getNameProduct($proposalProduct, $product)
    {
        if (is_object($proposalProduct)) {
            $nameProposalProduct = getValueTran($proposalProduct, 'name');
            $nameProduct = getValueTran($product, 'name');

            $capacity = self::getCapacityProduct($proposalProduct, $product);

            if (!empty($nameProposalProduct) && trim($nameProposalProduct) != '') {
                if (trim($capacity) != '') {
                    return $nameProposalProduct . ' / ' . $capacity;
                } else {
                    return $nameProposalProduct;
                }
            }

            if (trim($capacity) != '') {
                return $nameProduct . ' / ' . $capacity;
            } else {
                return $nameProduct;
            }
        }

        if (!empty($proposalProduct) && trim($proposalProduct) != '') {
            return $proposalProduct;
        }
        return $product;
    }


    public static function getUnitMoney($matter)
    {
        if ($matter->union_aux_set == Matter::UNION_AUX_SET_SCORE) {
            if ($matter->union_unit_type == Matter::UNION_UNIT_TYPE_TRUE && !empty($matter->union_aux_amount_specified2_1)) {
                return $matter->union_aux_amount_specified2_1;
            }
            return trans('frontend/messages.currency_unit_score');
        }
        return trans('frontend/messages.currency_unit_money');
    }

    /**
     * @param number $price
     * @param int $decimals
     * @param $matter
     * @param string $dec_point
     * @param string $thousands_sep
     * @return bool|string
     */
    public static function formatPriceFrontend($price, $matter, $decimals = 0, $dec_point = ".", $thousands_sep = ",")
    {
        if (!is_numeric($price)) {
            return '0' . self::getUnitMoney($matter);
        }
        $price = intval($price);
        $price_format = number_format($price, $decimals, $dec_point, $thousands_sep);
        return $price_format . ' ' . self::getUnitMoney($matter);
    }

    /**
     * get customer info by rank
     * @param object $customer
     * @param string $type
     * @return string | numeric
     */
    public static function getCustomerInfoByRank($customer, $type)
    {
        if (empty($customer)) {
            return 0;
        }
        if (!in_array($type, ['point', 'text'])) {
            throw new Exception('No support this type ' . $type);
        }
        if (!is_object($customer)) {
            return 0;
        }
        switch ($customer->rank) {
            case Matter::RANK_NORMAL :
                return $type == 'point' ? Matter::POINT_RANK_NORMAL : trans('frontend/messages/cart.customer_info.rank.normal');
            case Matter::RANK_SILVER :
                return $type == 'point' ? Matter::POINT_RANK_SILVER : trans('frontend/messages/cart.customer_info.rank.silver');
            case Matter::RANK_GOLD :
                return $type == 'point' ? Matter::POINT_RANK_GOLD : trans('frontend/messages/cart.customer_info.rank.gold');
            case Matter::RANK_PLATINUM :
                return $type == 'point' ? Matter::POINT_RANK_PLATINUM : trans('frontend/messages/cart.customer_info.rank.platinum');
            default:
                return $type == 'point' ? Matter::NO_RANK : '';
        }
    }

    /**
     * randomString
     * @param integer $length
     * @return String
     */
    public static function randomString($length = 10, $isNumber = false)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($isNumber) {
            $characters = '0123456789';
        }
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * substrwords
     * @param  $text
     * @param  $maxchar
     * @param  $end
     * @return String
     */
    public static function substrwords($text, $maxchar, $end = '...')
    {
        $words = mb_str_split($text);
        $output = $text;
        if (count($words) > $maxchar) {

            $output = '';
            $i = 0;
            while (1) {

                $length = count(mb_str_split($output)) + 1;
                if ($length > $maxchar) {
                    break;
                } else {
                    $output .= $words[$i];
                    ++$i;
                }
            }
            $output .= $end;
        }

        return $output;
    }


    /**
     * @param $weightNumber
     * @return String
     */
    public static function strWeight($weightNumber)
    {
        if ($weightNumber == '0') {
            return 'g';
        } else if ($weightNumber == '1') {
            return 'kg';
        }
        return '';
    }

    /**
     * get config name depend on locale
     * @param string $config
     * @return string
     */
    public static function getConfigNameByLocale($config)
    {
        $locale = LocaleHelper::getLocale();
        if ($locale == EN) {
            $config = $config . '_en';
        }
        return $config;
    }


    /**
     * @param $routeIndex
     */
    public static function saveUrlCallbackIndex($routeIndex)
    {
        $urlCallback = self::getUrlCallbackIndexReferer($routeIndex);
        $keySave = self::replaceKeySaveCookie($routeIndex);

        setCookieSite(COOKIE_ADMIN_URL_CALLBACK, [
            $keySave => $urlCallback
        ]);
    }

    /**
     * @param $routeIndex
     * @param array $paramRoute
     * @return string
     */
    public static function getUrlCallbackIndex($routeIndex, $paramRoute = []): string
    {
        $keySave = self::replaceKeySaveCookie($routeIndex);
        $urlCallback = getCookieSite(COOKIE_ADMIN_URL_CALLBACK, $keySave);
        if (empty($urlCallback) || (!empty($paramRoute) && $urlCallback == route($routeIndex))) {
            $urlCallback = route($routeIndex, $paramRoute);
        }
        return $urlCallback;
    }

    /**
     * @param $routeIndex
     * @param array $paramRoute
     * @return string
     */
    public static function getUrlCallbackIndexReferer($routeIndex, $paramRoute = []): string
    {
        $referer = request()->headers->get('referer');
        $urlCore = route($routeIndex);
        if (strpos($referer, $urlCore) !== false && strpos($referer, $urlCore . '/') === false) {
            $urlCallback = $referer;
        } else {
            $urlCallback = route($routeIndex, $paramRoute);
        }
        return $urlCallback;
    }

    /**
     * @param $routeIndex
     * @return string
     */
    public static function replaceKeySaveCookie($routeIndex): string
    {
        $keySave = str_replace('.', '_', $routeIndex);
        $keySave = str_replace('-', '_', $keySave);
        return $keySave;
    }

    /**
     * Get device client (PC, SP, Other)
     * @return int
     */
    public static function getDeviceClient()
    {
        $agent = new Agent();
        $device = self::DEVICE_OTHER;
        if ($agent->isDesktop()) {
            $device = self::DEVICE_PC;
        } elseif ($agent->isMobile()) {
            $device = self::DEVICE_SP;
        }
        return $device;
    }

    /**
     * Escape string in query like
     *
     * @param String $string
     * @return string
     */
    public static function escapeLike($string): string
    {
        $arySearch = array('\\', '%', '_');
        $aryReplace = array('\\\\', '\%', '\_');
        return str_replace($arySearch, $aryReplace, $string);
    }

    public static function subStringWordByByte($str, $maxByte =40)
    {
        $len = mb_strlen($str, 'UTF-8');
        $countByte = 0;
        $strResult = $str;
        for ($i = 0; $i < $len; $i++) {
            $characters = mb_substr($str, $i, 1, 'UTF-8');
            if (preg_match('/[\\\u3000-\\\u303F]+|[一-龠]+|[ぁ-ゔ]+|[ァ-ヴー]+|[ａ-ｚＡ-Ｚ０-９]+|[々〆〤]+/u', $characters) && !preg_match('/[0-9]+/u', $characters)) {
                $countByte += 2;
            } else{
                $countByte += 1;
            }
            if ($countByte >= $maxByte) {
                return substr($str, $i-1);
            }
        }
        return $strResult;
    }

    public static function httpPost($url, $data, $headers) {
        try {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
            $response = curl_exec($curl);
            if ($response === false) {
                curl_close($curl);
                return array("status" => false);
            }
            curl_close($curl);
            $res = json_decode($response, true);
            if ($res["code"] != 200) {
                return array("status" => false, 'data' => $response);
            }
            return array('data' => $response, "status" => true);
        } catch (Exception $e) {
            return array("status" => false, 'e' => $e);
        }
    }
    
    public static function httpPostMySign($url, $data, $headers) {
        try {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            if ($response === false) {
                curl_close($curl);
                return array("status" => false);
            }
            curl_close($curl);
            $res = json_decode($response, true);
            if ($res["code"] != 200) {
                return array("status" => false, 'data' => $response);
            }
            return array('data' => $response, "status" => true);
        } catch (Exception $e) {
            return array("status" => false, 'e' => $e);
        }
    }
}
