<?php
/**
 * File Name: index.php
 * PHP Version 7
 *
 * @category None
 * @package  None
 * @author   Jack Chen <redchenjs@live.com>
 * @license  https://server.zyiot.top/lem public
 * @version  GIT: <v0.2-draft>
 * @link     https://server.zyiot.top/lem
 */

const HTTP_REQ_CODE_DEV_UPD = 100;  // 设备端请求获取更新
const HTTP_REQ_CODE_DEV_OFF = 101;  // 设备端发送下机通知
const HTTP_REQ_CODE_DEV_ON  = 102;  // 设备端请求允许上机

$data = file_get_contents("php://input");   // 获取POST数据
$data = json_decode($data, true);           // 解析JSON
$code = $data['request'];                   // 客户端请求码

switch ($code) {
case HTTP_REQ_CODE_DEV_UPD:
    $device_mac   = $data['device_mac'];    // 获取$device_mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $relay_status = $data['relay_status'];  // 获取$relay_status设备继电器状态;String;"on":已开启,"off":未开启;
    $arr = array(
        'code'        => $code,             // 返回$code请求号;Number;将接收到的请求号原样送回;
        'status'      => true,              // 返回$status请求结果;Bool;未上机时,true:更新二维码数据,false:保持二维码数据;已上机时,true:保持上机,false:强制下机;
        'qrcode'      => md5(time()),       // 返回$qrcode二维码数据;String;32位MD5字符串,未上机且有更新时发送;
        'user_info'   => 'Xb14610214',      // 返回$user_info用户信息;String;10位字符串(学工号),已上机且有更新时发送;
        'expire_time' => '20:00:00'         // 返回$expire_time预约到期时间;String;"HH:MM:SS",24小时制,已上机且有更新时发送;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_DEV_OFF:
    $device_mac   = $data['device_mac'];    // 获取$device_mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $relay_status = $data['relay_status'];  // 获取$relay_status设备继电器状态;String;"on":已开启,"off":未开启;
    $arr = array(
        'code' => $code                     // 返回$code请求号;Number;将接收到的请求号原样送回;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_DEV_ON:
    $device_mac   = $data['device_mac'];    // 获取$device_mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $relay_status = $data['relay_status'];  // 获取$relay_status设备继电器状态;String;"on":已开启,"off":未开启;
    $arr = array(
        'code'        => $code,             // 返回$code请求号;Number;将接收到的请求号原样送回;
        'status'      => true,              // 返回$status请求结果;Bool;true:允许上机,false:不允许上机;
        'user_info'   => 'Xb14610214',      // 返回$user_info用户信息;String;10位字符串(学工号),允许上机时发送;
        'expire_time' => '20:00:00'         // 返回$expire_time预约到期时间;String;"HH:MM:SS",24小时制,允许上机时发送;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
default:
    break;
}
?>
