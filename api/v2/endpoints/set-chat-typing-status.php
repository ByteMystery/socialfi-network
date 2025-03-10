<?php
// +------------------------------------------------------------------------+
// | @author Deen Doughouz (DoughouzForest)
// | @author_url 1: http://www.socialfi.com
// | @author_url 2: http://codecanyon.net/user/doughouzforest
// | @author_email: socialfisocial@gmail.com
// +------------------------------------------------------------------------+
// | socialfi - The Ultimate Social Networking Platform
// | Copyright (c) 2018 socialfi. All rights reserved.
// +------------------------------------------------------------------------+
$response_data = array(
    'api_status' => 400,
);
if (empty($_POST['user_id'])) {
    $error_code    = 3;
    $error_message = 'user_id (POST) is missing';
}
if (empty($_POST['status'])) {
    $error_code    = 4;
    $error_message = 'status (POST) is missing';
}
if (empty($error_code)) {
    $recipient_id   = Wo_Secure($_POST['user_id']);
    $recipient_data = Wo_UserData($recipient_id);
    if (empty($recipient_data)) {
        $error_code    = 6;
        $error_message = 'Recipient user not found';
    } else {
    	$typing = ($_POST['status'] == 'typing') ? 1 : 0;
        if ($_POST['status'] == 'recording') {
            $typing = 2;
        }
    	$register = Wo_RegisterTyping($recipient_id, $typing);
    	if ($register) {
    		$response_data = array(
			    'api_status' => 200,
			);
    	}
    }
}