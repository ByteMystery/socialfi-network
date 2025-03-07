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
$json_success_data_2 = array();
$user_ids = array();
if (empty($_GET['type']) || !isset($_GET['type'])) {
    $json_error_data = array(
        'api_status' => '400',
        'api_text' => 'failed',
        'api_version' => $api_version,
        'errors' => array(
            'error_id' => '1',
            'error_text' => 'Bad request, no type specified.'
        )
    );
    header("Content-type: application/json");
    echo json_encode($json_error_data, JSON_PRETTY_PRINT);
    exit();
}
$type = Wo_Secure($_GET['type'], 0);
if ($type == 'get_users_friends') {
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
    }
    if (empty($json_error_data)) {
        $user_id         = $_POST['user_id'];
        $user_login_data = Wo_UserData($user_id);
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
            $wo['lang'] = Wo_LangsFromDB($user_login_data['language']);
            $timezone = new DateTimeZone($user_login_data['timezone']);
            $get = Wo_GetFollowing($user_id, 'sidebar', 50);
            $users = array();
            foreach ($get as $user_list) {
                $lastseen = ($user_list['lastseen'] > (time() - 60)) ? 'on' : 'off';
                $json_data   = array();
                $user_ids[] = $user_list['user_id'];
                $json_data = Wo_UserData($user_list['user_id']);
                foreach ($non_allowed as $key => $value) {
                    unset($json_data[$value]);
                }
                $json_data['is_following'] = 0;
                $json_data['can_follow'] = 0;
                if (Wo_IsFollowing($recipient_id, $logged_user_id)) {
                    $json_data['is_following'] = 1;
                    $json_data['can_follow'] = 1;
                } else {
                    if (Wo_IsFollowRequested($recipient_id, $logged_user_id)) {
                        $json_data['is_following'] = 2;
                        $json_data['can_follow'] = 1;
                    } else {
                        if ($json_data['follow_privacy'] == 1) {
                            if (Wo_IsFollowing($logged_user_id, $recipient_id)) {
                                $json_data['is_following'] = 0;
                                $json_data['can_follow'] = 1;
                            }
                        } else if ($json_data['follow_privacy'] == 0) {
                            $json_data['can_follow'] = 1;
                        }
                    }
                }
                $json_data['is_following_me'] = (Wo_IsFollowing( $wo['user']['user_id'], $json_data['user_id'])) ? 1 : 0;
                $json_data['lastseen_time_text'] = Wo_Time_Elapsed_String($json_data['lastseen']);
                $json_data['is_blocked']         = Wo_IsBlocked($json_data['user_id']);
               // array_push($json_success_data['users'], $json_data);
                $users[] = $json_data;
            }
            $online = array();
            $get = Wo_GetChatUsersAPP($user_id, 'online', '', $user_ids);
            foreach ($get as $user_list) {
                $lastseen = ($user_list['lastseen'] > (time() - 60)) ? 'on' : 'off';
                $json_data   = array();
                $json_data = Wo_UserData($user_list['user_id']);
                foreach ($non_allowed as $key => $value) {
                    unset($json_data[$value]);
                }
                $json_data['is_following'] = 0;
                $json_data['can_follow'] = 0;
                if (Wo_IsFollowing($recipient_id, $logged_user_id)) {
                    $json_data['is_following'] = 1;
                    $json_data['can_follow'] = 1;
                } else {
                    if (Wo_IsFollowRequested($recipient_id, $logged_user_id)) {
                        $json_data['is_following'] = 2;
                        $json_data['can_follow'] = 1;
                    } else {
                        if ($json_data['follow_privacy'] == 1) {
                            if (Wo_IsFollowing($logged_user_id, $recipient_id)) {
                                $json_data['is_following'] = 0;
                                $json_data['can_follow'] = 1;
                            }
                        } else if ($json_data['follow_privacy'] == 0) {
                            $json_data['can_follow'] = 1;
                        }
                    }
                }
                $json_data['is_following_me'] = (Wo_IsFollowing( $wo['user']['user_id'], $json_data['user_id'])) ? 1 : 0;
                $json_data['lastseen_time_text'] = Wo_Time_Elapsed_String($json_data['lastseen']);
                $json_data['is_blocked']         = Wo_IsBlocked($json_data['user_id']);
                //array_push($json_success_data_2, $json_data);
                $online[] = $json_data;
            }
            echo json_encode(array(
                'api_status' => 200,
                'api_text' => 'success',
                'api_version' => $api_version,
                'theme_url' => $config['theme_url'],
                'users' => $users,
                'online' => $online
            ));
            exit();
        }
    } else {
        header("Content-type: application/json");
        echo json_encode($json_error_data);
        exit();
    }
}
header("Content-type: application/json");
echo json_encode($json_success_data);
exit();
?>