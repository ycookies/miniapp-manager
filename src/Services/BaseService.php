<?php
namespace Ycookies\MiniappManager\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
/**
 * 基础服务层 基类
 * @package App\Services
 * anthor Fox
 */
class BaseService {

    public $user_id;
    public $role_as;

    public function __construct($user_id = '') {

    }
}