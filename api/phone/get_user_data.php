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
if ($type == 'get_user_data') {
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
    } else if (empty($_POST['user_profile_id'])) {
        $json_error_data = array(
            'api_status' => '400',
            'api_text' => 'failed',
            'api_version' => $api_version,
            'errors' => array(
                'error_id' => '5',
                'error_text' => 'No profile id sent.'
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
        $s               = Wo_Secure($_POST['s']);
        $user_login_data = Wo_UserData($user_id);
        $wo['lang']      = Wo_LangsFromDB($user_login_data['language']);
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
            $user_profile_id   = Wo_Secure($_POST['user_profile_id']);
            $user_profile_data = Wo_UserData($user_profile_id);
            $user_data = Wo_UpdateUserDetails($user_profile_data, true, true, true);
            if (is_array($user_data)) {
                $user_profile_data = $user_data;
            }
            if (empty($user_profile_data)) {
                $json_error_data = array(
                    'api_status' => '400',
                    'api_text' => 'failed',
                    'api_version' => $api_version,
                    'errors' => array(
                        'error_id' => '6',
                        'error_text' => 'User profile is not exists.'
                    )
                );
                header("Content-type: application/json");
                echo json_encode($json_error_data, JSON_PRETTY_PRINT);
                exit();
            }
            foreach ($non_allowed as $key => $value) {
                unset($user_profile_data[$value]);
            }
            $user_profile_data['is_following'] = 0;
            $user_profile_data['can_follow'] = 0;
            $logged_user_id                    = $user_id;
            if (Wo_IsFollowing($user_profile_id, $logged_user_id)) {
                $user_profile_data['is_following'] = 1;
                $user_profile_data['can_follow'] = 1;
            } else {
                if (Wo_IsFollowRequested($user_profile_id, $logged_user_id)) {
                    $user_profile_data['is_following'] = 2;
                    $user_profile_data['can_follow'] = 1;
                } else {
                    if ($user_profile_data['follow_privacy'] == 1) {
                        if (Wo_IsFollowing($logged_user_id, $user_profile_id)) {
                            $user_profile_data['is_following'] = 0;
                            $user_profile_data['can_follow'] = 1;
                        }
                    } else if ($user_profile_data['follow_privacy'] == 0) {
                        $user_profile_data['can_follow'] = 1;
                    }
                }
            }
            $user_profile_data['post_count']         = $user_profile_data['details']['post_count'];
            $user_profile_data['gender_text']        = ($user_profile_data['gender'] == 'male') ? $wo['lang']['male'] : $wo['lang']['female'];
            $user_profile_data['lastseen_time_text'] = Wo_Time_Elapsed_String($user_profile_data['lastseen']);
            $user_profile_data['is_blocked']         = Wo_IsBlocked($user_profile_data['user_id']);
            $user_profile_data['following_number']   = $user_profile_data['details']['following_count'];
            $user_profile_data['followers_number']   = $user_profile_data['details']['followers_count'];
            $user_profile_data['followers']          = Wo_GetFollowers($user_profile_data['user_id']);
            $user_profile_data['following']          = Wo_GetFollowing($user_profile_data['user_id']);
            $user_profile_data['liked_pages']        = Wo_GetLikes($user_profile_data['user_id'], 'profile', 2000);
            $user_profile_data['groups']             = Wo_GetUsersGroups($user_profile_data['user_id']);
            $json_success_data                       = array(
                'api_status' => '200',
                'api_text' => 'success',
                'api_version' => $api_version,
                'user_data' => $user_profile_data
            );
            header("Content-type: application/json");
            echo json_encode($json_success_data, JSON_PRETTY_PRINT);
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