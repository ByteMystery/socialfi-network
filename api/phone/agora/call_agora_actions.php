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
if ($type == 'call_agora_actions') {
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
    } else if (empty($_POST['s'])) {
        $json_error_data = array(
            'api_status' => '400',
            'api_text' => 'failed',
            'api_version' => $api_version,
            'errors' => array(
                'error_id' => '5',
                'error_text' => 'No session sent.'
            )
        );
    } 
    if (empty($json_error_data)) {
        $user_id         = $_POST['user_id'];
        $s               = Wo_Secure(md5($_POST['s']));
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
            if (!empty($_POST['answer_type'])) {
            	if (!empty($_POST['call_id'])) {
            		if ($_POST['answer_type'] == 'answer') {
            			$id = Wo_Secure($_POST['call_id']);
				        $query = mysqli_query($sqlConnect, "UPDATE " . T_AGORA . " SET `status` = 'answered' WHERE `id` = '$id'");
				        if ($query) {
				            $data = array(
				                'status' => 200,
				            );
				        }
            		} else if ($_POST['answer_type'] == 'close') {
                        $query   = mysqli_query($sqlConnect, "DELETE FROM " . T_AGORA . " WHERE `from_id` = '$user_id'");
                        if ($query) {
                            $data = array(
                                'status' => 200
                            );
                        }
                    } else {
            			$id = Wo_Secure($_POST['call_id']);
				        $query = mysqli_query($sqlConnect, "UPDATE " . T_AGORA . " SET `status` = 'declined' WHERE `id` = '$id'");
				        if ($query) {
				            $data = array(
				                'status' => 200
				            );
				        }
            		}
            		header("Content-type: application/json");
			        echo json_encode($data, JSON_PRETTY_PRINT);
			        exit();
			    }
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