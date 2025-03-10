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
$json_error_data   = array();
$json_success_data = array();
$type              = Wo_Secure($_GET['type'], 0);
if ($type == 'get_multi_users') {
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
    } else if (empty($_POST['usersIDs'])) {
        $json_error_data = array(
            'api_status' => '400',
            'api_text' => 'failed',
            'api_version' => $api_version,
            'errors' => array(
                'error_id' => '5',
                'error_text' => 'No ids sent.'
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
            $usersIDs = Wo_Secure($_POST['usersIDs']);
            $users    = @explode(',', $usersIDs);
            $continue = true;
            foreach ($users as $key => $user_profile_id) {
                if (!empty($user_profile_id) && is_numeric($user_profile_id)) {
                    $user_profile_data = Wo_UserData($user_profile_id);
                    foreach ($non_allowed as $key => $value) {
                        unset($user_profile_data[$value]);
                    }
                    if (empty($user_profile_data)) {
                        $continue = false;
                    } else {
                        $user_profile_data['is_following'] = 0;
                        $user_profile_data['can_follow'] = 0;
                        if (Wo_IsFollowing($recipient_id, $logged_user_id)) {
                            $user_profile_data['is_following'] = 1;
                            $user_profile_data['can_follow'] = 1;
                        } else {
                            if (Wo_IsFollowRequested($recipient_id, $logged_user_id)) {
                                $user_profile_data['is_following'] = 2;
                                $user_profile_data['can_follow'] = 1;
                            } else {
                                if ($user_profile_data['follow_privacy'] == 1) {
                                    if (Wo_IsFollowing($logged_user_id, $recipient_id)) {
                                        $user_profile_data['is_following'] = 0;
                                        $user_profile_data['can_follow'] = 1;
                                    }
                                } else if ($user_profile_data['follow_privacy'] == 0) {
                                    $user_profile_data['can_follow'] = 1;
                                }
                            }
                        }
                        $user_profile_data['is_following_me'] = (Wo_IsFollowing( $wo['user']['user_id'], $user_profile_data['user_id'])) ? 1 : 0;
                        $user_profile_data['lastseen_time_text'] = Wo_Time_Elapsed_String($user_profile_data['lastseen']);
                        $user_profile_data['is_blocked']         = Wo_IsBlocked($user_profile_data['user_id']);

                    	$user_profile_data['gender'] = ($user_profile_data['gender'] == 'male') ? $wo['lang']['male']: $wo['lang']['female'];
                        $user_profile_data['lastseen_status'] = ($user_profile_data['lastseen'] > (time() - 60)) ? 'online': 'offline';
                        $user_profile_data['lastseen_text'] = ($user_profile_data['lastseen'] > (time() - 60)) ? $wo['lang']['online']: $wo['lang']['last_seen'] . ' ' . Wo_Time_Elapsed_String($user_profile_data['lastseen']);
                        array_push($json_success_data, $user_profile_data);
                    }
                }
            }
        }
    } else {
        header("Content-type: application/json");
        echo json_encode($json_error_data, JSON_PRETTY_PRINT);
        exit();
    }
}
if ($continue == false) {
    $json_error_data = array(
        'api_status' => '400',
        'api_text' => 'failed',
        'api_version' => $api_version,
        'errors' => array(
            'error_id' => '6',
            'error_text' => 'One of the users is not exists.'
        )
    );
    header("Content-type: application/json");
    echo json_encode($json_error_data, JSON_PRETTY_PRINT);
    exit();
} else {
    $json_success_data = array(
        'api_status' => '200',
        'api_text' => 'success',
        'api_version' => $api_version,
        'users' => $json_success_data
    );
    header("Content-type: application/json");
    echo json_encode($json_success_data);
    exit();
}
header("Content-type: application/json");
echo json_encode($json_success_data);
exit();
?>