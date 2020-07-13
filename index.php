<?php
/**
 * File Name: index.php
 * PHP Version 7
 *
 * @category None
 * @package  None
 * @author   Jack Chen <redchenjs@live.com>
 * @license  https://server.zyiot.top/lpm public
 * @version  GIT: <v0.1-draft>
 * @link     https://server.zyiot.top/lpm
 */

$data = file_get_contents("php://input");   // 获取POST数据
$data = json_decode($data, true);           // 解析JSON
$request = $data['request'];                // 收到$request请求代码;Number;3位数字;

switch ($request) {
case 100:   // 设备端同步请求
    $status = $data['status'];              // 收到$status设备状态;String;"on":开启,"off":关闭;
    $mac_addr = $data['mac'];               // 收到$mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $arr = array(
        'request' => 100,                   // 发送$request请求号;Number;将发来的$request原样送回;
        'status'  => true,                  // 发送$status请求结果;Bool;未上机时,true:设备已注册,false:设备未注册;已上机时,true:保持上机,false:强制下机;
        'token'   => md5(time()),           // 发送$token设备令牌;String;32位MD5,用于二维码编码,每分钟更新一次,未注册设备不发送;
        'user_info'  => "Xb14610214",       // 发送$user_info用户信息;String;10位字符串,未上机时不发送;
        'timer_time' => "09:00:00"          // 发送$timer_time预约时间;String;"HH:MM:SS",24小时制,未上机时不发送;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case 101:   // 设备端下机通知
    $status = $data['status'];              // 收到$status设备状态;String;"on":开启,"off":关闭;
    $mac_addr = $data['mac'];               // 收到$mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $arr = array(
        'request' => 101                    // 发送$request请求号;Number;将发来的$request原样送回;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case 102:   // 设备端上机请求
    $status = $data['status'];              // 收到$status设备状态;String;"on":开启,"off":关闭;
    $mac_addr = $data['mac'];               // 收到$mac设备MAC地址;String;"30:ae:a4:56:75:50";
    $arr = array(
        'request' => 102,                   // 发送$request请求号;Number;将发来的$request原样送回;
        'status'  => true,                  // 发送$status请求结果;Bool;true:允许上机,false:不允许上机;
        'user_info'  => "Xb14610214",       // 发送$user_info用户信息;String;10位字符串,不允许上机时不发送;
        'timer_time' => "09:00:00"          // 发送$timer_time预约时间;String;"HH:MM:SS",24小时制,不允许上机时不发送;
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
default:    // 其他请求
    break;
}
?>
