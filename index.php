<?php
/**
 * File Name: index.php
 * PHP Version 7
 *
 * @category None
 * @package  None
 * @author   Jack Chen <redchenjs@live.com>
 * @license  https://server.zyiot.top/lem public
 * @version  GIT: <v0.1-draft>
 * @link     https://server.zyiot.top/lem
 */

$data = file_get_contents("php://input");   // 获取POST数据
$data = json_decode($data, true);           // 解析JSON
$code = $data['code'];                      // 接收$code请求号;Number;3位数字;

switch ($code) {
case 100:   // 设备端同步请求
    $status   = $data['status'];            // 接收$status设备状态;String;"on":已上机,"off":未上机;
    $wifi_mac = $data['wifi_mac'];          // 接收$wifi_mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $arr = array(
        'code'        => $code,             // 发送$code请求号;Number;将接收到的请求号原样送回;
        'status'      => true,              // 发送$status请求结果;Bool;未上机时,true:更新二维码数据,false:保持二维码数据;已上机时,true:保持上机,false:强制下机;
        'token'       => md5(time()),       // 发送$token设备令牌;String;32位MD5,二维码数据,未上机且有更新时发送;
        'user_info'   => 'Xb14610214',      // 发送$user_info用户信息;String;10位字符串(学工号),已上机且有更新时发送;
        'expire_time' => '20:00:00'         // 发送$expire_time预约到期时间;String;"HH:MM:SS",24小时制,已上机且有更新时发送;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case 101:   // 设备端下机通知
    $status   = $data['status'];            // 接收$status设备状态;String;"on":已上机,"off":未上机;
    $wifi_mac = $data['wifi_mac'];          // 接收$wifi_mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $arr = array(
        'code' => $code                     // 发送$code请求号;Number;将接收到的请求号原样送回;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case 102:   // 设备端上机请求
    $status   = $data['status'];            // 接收$status设备状态;String;"on":已上机,"off":未上机;
    $wifi_mac = $data['wifi_mac'];          // 接收$wifi_mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $arr = array(
        'code'        => $code,             // 发送$code请求号;Number;将接收到的请求号原样送回;
        'status'      => true,              // 发送$status请求结果;Bool;true:允许上机,false:不允许上机;
        'user_info'   => 'Xb14610214',      // 发送$user_info用户信息;String;10位字符串(学工号),允许上机时发送;
        'expire_time' => '20:00:00'         // 发送$expire_time预约到期时间;String;"HH:MM:SS",24小时制,允许上机时发送;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
default:    // 其他请求
    break;
}
?>
