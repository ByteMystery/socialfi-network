<?php
// +------------------------------------------------------------------------+
// | @author Deen Doughouz (DoughouzForest)
// | @author_url 1: http://www.socialfi.com
// | @author_url 2: http://codecanyon.net/user/doughouzforest
// | @author_email: socialfisocial@gmail.com   
// +------------------------------------------------------------------------+
// | socialfi - The Ultimate Social Networking Platform
// | Copyright (c) 2016 socialfi. All rights reserved.
// +------------------------------------------------------------------------+
$json_error_data     = array();
$json_success_data   = array();
$type                = Wo_Secure($_GET['type'], 0);
if ($type == 'register_typing') {
    if (empty($_POST['user_id'])) {
        $json_error_data = array(
            'api_status' => '400',
            'api_text' => 'failed',
            'api_version' => $api_version,
            'errors' => array(
                'error_id' => '3',
                'error_text' => 'No user id sent.'
            )
        );
    } else if (empty($_POST['recipient_id'])) {
        $json_error_data = array(
            'api_status' => '400',
            'api_text' => 'failed',
            'api_version' => $api_version,
            'errors' => array(
                'error_id' => '5',
                'error_text' => 'No recipient id sent.'
            )
        );
    }
    if (empty($json_error_data)) {
        $user_id         = $_POST['user_id'];
        $user_login_data = Wo_UserData($user_id);
        $wo['lang'] = Wo_LangsFromDB($user_login_data['language']);
        if (empty($user_login_data)) {
            $json_error_data = array(
                'api_status' => '400',
                'api_text' => 'failed',
                'api_version' => $api_version,
                'errors' => array(
                    'error_id' => '6',
                    'error_text' => 'Username is not exists.'
                )
            );
            header("Content-type: application/json");
            echo json_encode($json_error_data, JSON_PRETTY_PRINT);
            exit();
        } else if ($wo['loggedin'] == false) {
            $json_error_data = array(
                'api_status' => '400',
                'api_text' => 'failed',
                'api_version' => $api_version,
                'errors' => array(
                    'error_id' => '6',
                    'error_text' => 'Session id is wrong.'
                )
            );
            header("Content-type: application/json");
            echo json_encode($json_error_data, JSON_PRETTY_PRINT);
            exit();
        } else {
            $recipient_id     = $_POST['recipient_id'];
            $user_login_data2 = Wo_UserData($recipient_id);
            if (empty($user_login_data2)) {
                $json_error_data = array(
                    'api_status' => '400',
                    'api_text' => 'failed',
                    'api_version' => $api_version,
                    'errors' => array(
                        'error_id' => '6',
                        'error_text' => 'User Profile is not exists.'
                    )
                );
                header("Content-type: application/json");
                echo json_encode($json_error_data, JSON_PRETTY_PRINT);
                exit();
            }
            $regsiter_typing = Wo_RegisterTyping($recipient_id, 1);
            if ($regsiter_typing) {
            	$json_success_data = array(
                    'api_status' => '200',
                    'api_text' => 'success',
                    'api_version' => $api_version
                );
                header("Content-type: application/json");
                echo json_encode($json_success_data);
                exit();
            } else {
            	$json_error_data = array(
                    'api_status' => '400',
                    'api_text' => 'failed',
                    'api_version' => $api_version,
                    'errors' => array(
                        'error_id' => '6',
                        'error_text' => 'Error while inserting typing.'
                    )
                );
                header("Content-type: application/json");
                echo json_encode($json_error_data, JSON_PRETTY_PRINT);
                exit();
            }
        }
    } else {
        header("Content-type: application/json");
        echo json_encode($json_error_data, JSON_PRETTY_PRINT);
        exit();
    }
}
header("Content-type: application/json");
echo json_encode($json_success_data);
exit();
?>