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
if ($type == 'get_events') {
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
            $my_offset = 0;
            if (!empty($_POST['my_offset']) && is_numeric($_POST['my_offset'])) {
            	$my_offset = Wo_Secure($_POST['my_offset']);
            }
            $offset = 0;
            if (!empty($_POST['offset']) && is_numeric($_POST['offset'])) {
            	$offset = Wo_Secure($_POST['offset']);
            }
            $events = array();
            $get_events = Wo_GetEvents(array('offset' => $offset, 'limit' => 20));
            foreach ($get_events as $key => $event) {
            	foreach ($non_allowed as $key => $value) {
                   unset($event['user_data'][$value]);
                }
            	$event['is_going'] = Wo_EventGoingExists($event['id']);
            	$event['is_interested'] = Wo_EventInterestedExists($event['id']);
                $event['going_count'] = Wo_TotalGoingUsers($event['id']);
                $event['interested_count'] = Wo_TotalInterestedUsers($event['id']);
            	$events[] = $event;
            }
            $my_events_array = array();
            $my_events = Wo_GetMyEvents($my_offset);
            foreach ($my_events as $key => $event) {
            	foreach ($non_allowed as $key => $value) {
                   unset($event['user_data'][$value]);
                }
            	$my_events_array[] = $event;
            }
            $json_success_data  = array(
                'api_status' => '200',
                'api_text' => 'success',
                'api_version' => $api_version,
                'events' => $events,
                'my_events' => $my_events_array
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