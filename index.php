<?php
/**
 * File Name: index.php
 * PHP Version 7
 *
 * @category None
 * @package  None
 * @author   Jack Chen <redchenjs@live.com>
 * @license  https://server.zyiot.top/lem public
 * @version  GIT: <v0.4>
 * @link     https://server.zyiot.top/lem
 */

require 'utils/app.php';
require 'utils/dev.php';
require 'utils/web.php';

const HTTP_REQ_CODE_APP_GET_INFO    = 110; // 微信端获取用户信息
const HTTP_REQ_CODE_APP_SET_TIME    = 111; // 微信端设定预约时间
const HTTP_REQ_CODE_APP_SET_CANCEL  = 112; // 微信端取消预约时间
const HTTP_REQ_CODE_APP_SET_ONLINE  = 113; // 微信端请求允许上机
const HTTP_REQ_CODE_APP_SET_OFFLINE = 114; // 微信端请求强制下机
const HTTP_REQ_CODE_APP_BIND_USER   = 115; // 微信端请求绑定用户
const HTTP_REQ_CODE_APP_UNBIND_USER = 116; // 微信端请求解绑用户
const HTTP_REQ_CODE_APP_UPDATE_PSWD = 117; // 微信端请求修改密码

const HTTP_REQ_CODE_DEV_GET_INFO    = 210; // 设备端获取用户信息
const HTTP_REQ_CODE_DEV_SET_ONLINE  = 211; // 设备端请求允许上机
const HTTP_REQ_CODE_DEV_SET_OFFLINE = 212; // 设备端发送下机通知

$data = file_get_contents('php://input');   // 获取POST数据
$data = json_decode($data, true);           // 解析JSON
$code = $data['request'];                   // 客户端请求码

switch ($code) {
case HTTP_REQ_CODE_APP_GET_INFO:
    $wx_code = $data['wx_code'];
    if (($wx_openid = getOpenID($wx_code)) !== null) {
        if (($user_id = getUserID($wx_openid)) !== null) {
            $last_info = getLastInfo($user_id);
            $arr = array(
                'result' => true,
                'user_id' => $user_id,
                'last_time' => $last_info['last_time'],
                'last_location' => $last_info['last_location']
            );
        } else {
            $arr = array(
                'result' => false
            );
        }
    } else {
        $arr = array(
            'result' => null
        );
    }
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_APP_BIND_USER:
    $wx_code = $data['wx_code'];
    $user_id = $data['user_id'];
    $user_passwd = $data['user_passwd'];
    if (($wx_openid = getOpenID($wx_code)) !== null) {
        $errmsg = bindUser($wx_openid, $user_id, $user_passwd);
        $arr = array(
            'result' => $errmsg === null ? true : false,
            'errmsg' => $errmsg
        );
    } else {
        $arr = array(
            'result' => null
        );
    }
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_APP_UNBIND_USER:
    $wx_code = $data['wx_code'];
    $user_id = $data['user_id'];
    if (($wx_openid = getOpenID($wx_code)) !== null) {
        $errmsg = unbindUser($wx_openid, $user_id);
        $arr = array(
            'result' => $errmsg === null ? true : false,
            'errmsg' => $errmsg
        );
    } else {
        $arr = array(
            'result' => null
        );
    }
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_APP_UPDATE_PSWD:
    $wx_code = $data['wx_code'];
    $user_id = $data['user_id'];
    $old_passwd = $data['old_passwd'];
    $new_passwd = $data['new_passwd'];
    if (($wx_openid = getOpenID($wx_code)) !== null) {
        $errmsg = updatePassword($wx_openid, $user_id, $old_passwd, $new_passwd);
        $arr = array(
            'result' => $errmsg === null ? true : false,
            'errmsg' => $errmsg
        );
    } else {
        $arr = array(
            'result' => null
        );
    }
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_DEV_GET_INFO:
    $device_mac   = $data['device_mac'];    // 获取$device_mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $relay_status = $data['relay_status'];  // 获取$relay_status设备继电器状态;String;"on":已开启,"off":未开启;
    $arr = array(
        'code'        => $code,             // 返回$code请求号;Number;将接收到的请求号原样送回;
        'status'      => true,              // 返回$status状态数据;Bool;未上机时,true:更新二维码数据,false:保持二维码数据;已上机时,true:保持上机,false:强制下机;
        'qrcode'      => md5(time()),       // 返回$qrcode二维码数据;String;32位MD5字符串,未上机且有更新时发送;
        'user_info'   => 'Xb14610214',      // 返回$user_info用户信息;String;10位字符串(学工号),已上机且有更新时发送;
        'expire_time' => '20:00:00'         // 返回$expire_time预约到期时间;String;"HH:MM:SS",24小时制,已上机且有更新时发送;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_DEV_SET_ONLINE:
    $device_mac   = $data['device_mac'];    // 获取$device_mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $relay_status = $data['relay_status'];  // 获取$relay_status设备继电器状态;String;"on":已开启,"off":未开启;
    $arr = array(
        'code'        => $code,             // 返回$code请求号;Number;将接收到的请求号原样送回;
        'result'      => true,              // 返回$result请求结果;Bool;true:允许上机,false:不允许上机;
        'user_info'   => 'Xb14610214',      // 返回$user_info用户信息;String;10位字符串(学工号),允许上机时发送;
        'expire_time' => '20:00:00'         // 返回$expire_time预约到期时间;String;"HH:MM:SS",24小时制,允许上机时发送;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_DEV_SET_OFFLINE:
    $device_mac   = $data['device_mac'];    // 获取$device_mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $relay_status = $data['relay_status'];  // 获取$relay_status设备继电器状态;String;"on":已开启,"off":未开启;
    $arr = array(
        'code' => $code                     // 返回$code请求号;Number;将接收到的请求号原样送回;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
default:
    listLog();
    break;
}
?>
