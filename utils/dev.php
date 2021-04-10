<?php
/**
 * File Name: dev.php
 * PHP Version 7
 *
 * @category None
 * @package  None
 * @author   Jack Chen <redchenjs@live.com>
 * @license  https://zyiot.top/lem public
 * @version  GIT: <v0.4>
 * @link     https://zyiot.top/lem
 */

/**
 * 使用$device_mac和$fw_version检索数据库决定设备是否需要更新，是则返回更新数据，否则返回空数据
 *
 * @param string $device_mac Device MAC Address
 * @param string $fw_version Firmware Version
 *
 * @return file $file
 */
function getFirmwareUpdate($device_mac, $fw_version)
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if (!$conn) {
        die('Access denied.');
    }
    mysqli_select_db($conn, DB_NAME);
    mysqli_set_charset($conn, 'utf8');

    // 查找$device_mac是否存在
    $sql = "SELECT `device_mac` FROM `dev_tbl`
            WHERE BINARY `device_mac`='$device_mac'";
    $retval = mysqli_query($conn, $sql);
    if (!$retval) {
        die('Query failed.');
    }
    // 整理查询结果
    if (mysqli_fetch_array($retval, MYSQLI_ASSOC) !== null) {
        // $device_mac记录存在，更新数据库记录
        $sql = "UPDATE `dev_tbl` SET `firmware_running`='$fw_version'
                WHERE BINARY `device_mac`='$device_mac'";
        $retval = mysqli_query($conn, $sql);
        if (!$retval) {
            die('Query failed.');
        }
        // 使用$device_mac查找$firmware_required
        $sql = "SELECT `firmware_required` FROM `dev_tbl`
                WHERE BINARY `device_mac`='$device_mac'";
        $retval = mysqli_query($conn, $sql);
        if (!$retval) {
            die('Query failed.');
        }
        // 整理查询结果
        if (($row = mysqli_fetch_array($retval, MYSQLI_ASSOC)) !== null) {
            if ($row['firmware_required'] !== '') {
                if ($row['firmware_required'] !== $fw_version) {
                    // 设备固件运行版本与目标版本不符
                    $required_version = $row['firmware_required'];
                    $local_file = "/tmp/lem_$required_version.bin";
                    $server_file = "pub/firmware/lem/lem_$required_version.bin";
                    // 登录FTP服务器
                    $conn_id = ftp_connect(FTP_HOST);
                    ftp_login($conn_id, FTP_USER, FTP_PASS);
                    // 获取目标版本固件
                    if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
                        // 固件获取成功，发送数据到设备端，记录日志
                        $sql = "INSERT INTO `log_tbl` (`user`, `location`, `comment`)
                                VALUES ('$device_mac', '固件更新', '正在更新，从 $fw_version 到 $required_version')";
                        $retval = mysqli_query($conn, $sql);
                        if (!$retval) {
                            die('Query failed.');
                        }
                        $file = fopen($local_file, 'rb');
                        header("Content-type: application/octet-stream");
                        header("Accept-Ranges: bytes");
                        header("Accept-Length: ".filesize($local_file));
                        header("Content-Disposition: attachment; filename=lem_$required_version.bin");
                        echo fread($file, filesize($local_file));
                        fclose($file);
                    } else {
                        // 目标版本固件不存在，记录日志
                        $sql = "INSERT INTO `log_tbl` (`user`, `location`, `comment`)
                                VALUES ('$device_mac', '固件更新', '失败：目标版本 $required_version 不存在')";
                        $retval = mysqli_query($conn, $sql);
                        if (!$retval) {
                            die('Query failed.');
                        }
                    }
                    // 断开FTP连接
                    ftp_close($conn_id);
                } else {
                    // 没有新固件，记录日志
                    $sql = "INSERT INTO `log_tbl` (`user`, `location`, `comment`)
                            VALUES ('$device_mac', '固件更新', '已为最新，当前运行版本：$fw_version')";
                    $retval = mysqli_query($conn, $sql);
                    if (!$retval) {
                        die('Query failed.');
                    }
                }
            } else {
                // 更新已禁用，记录日志
                $sql = "INSERT INTO `log_tbl` (`user`, `location`, `comment`)
                        VALUES ('$device_mac', '固件更新', '更新已禁用，当前运行版本：$fw_version')";
                $retval = mysqli_query($conn, $sql);
                if (!$retval) {
                    die('Query failed.');
                }
            }
        }
    } else {
        // $device_mac记录不存在，记录日志
        $sql = "INSERT INTO `log_tbl` (`user`, `location`, `comment`)
                VALUES ('$device_mac', '固件更新', '失败：设备未经授权')";
        $retval = mysqli_query($conn, $sql);
        if (!$retval) {
            die('Query failed.');
        }
    }
}
