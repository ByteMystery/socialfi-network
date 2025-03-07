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

$required_fields =  array(
                        'users',
                        'groups',
                        'pages'
                    );
$limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Wo_Secure($_POST['limit']) : 5);
if (!empty($_POST['type']) && in_array($_POST['type'], $required_fields)) {
    if ($_POST['type'] == 'users') {
    	$users = Wo_UserSug($limit);
        foreach ($users as $key => $user) {
            foreach ($non_allowed as $key => $value) {
               unset($users[$key][$value]);
            }
        }
    	$response_data = array(
                                'api_status' => 200,
                                'data' => $users
                            );
    }
    elseif ($_POST['type'] == 'groups') {
        $groups = Wo_GroupSug($limit);
        foreach ($groups as $key => $group) {
            $groups[$key]['members'] = Wo_CountGroupMembers($group['id']);
            $groups[$key]['is_joined'] = Wo_IsGroupJoined($group['id']);
            $groups[$key]['is_owner'] = Wo_IsGroupOnwer($group['id']);
        }
        $response_data = array(
                                'api_status' => 200,
                                'data' => $groups
                            );
    }
    elseif ($_POST['type'] == 'pages') {
        $pages = Wo_PageSug($limit);
        foreach ($pages as $key => $page) {
            $pages[$key]['likes'] = Wo_CountPageLikes($page['page_id']);
            $pages[$key]['is_liked'] = Wo_IsPageLiked($page['page_id'], $wo['user']['id']);
        }
        $response_data = array(
                                'api_status' => 200,
                                'data' => $pages
                            );
    }
}
else{
    $error_code    = 4;
    $error_message = 'type can not be empty';
}
