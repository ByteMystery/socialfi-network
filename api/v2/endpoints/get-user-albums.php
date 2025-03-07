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
if (empty($error_code)) {
    $recipient_id   = Wo_Secure($_POST['user_id']);
    $recipient_data = Wo_UserData($recipient_id);
    if (empty($recipient_data)) {
        $error_code    = 6;
        $error_message = 'Recipient user not found';
    }
    elseif (empty($_POST['type']) || !in_array($_POST['type'], array('photos','video','music','files','maps','text'))) {
         $error_code    = 7;
         $error_message = 'type can not be empty';
     } else {
		$data = array(
            'filter_by' => Wo_Secure($_POST['type']),
            'limit' => (!empty($_POST['limit'])) ? (int) $_POST['limit'] : 35,
            'publisher_id' => $recipient_id,
            'after_post_id' => (!empty($_POST['offset'])) ? (int) $_POST['offset'] : 0,
        );
        $get_albums = Wo_GetPosts($data);
        $albums  = array();
        foreach ($get_albums as $key => $album) {
            $comments = array();
            foreach ($non_allowed as $key => $value) {
               unset($album['publisher'][$value]);
            }
            $album['shared_from'] = (!empty($album['shared_from'])) ? $album['shared_from'] : null;
            foreach ($album['get_post_comments'] as $key => $comment) {
                foreach ($non_allowed as $key => $value) {
                  unset($comment['publisher'][$value]);
                }
                $comments[] = $comment;
            }
            $album['get_post_comments'] = $comments;
            $albums[] = $album;
        }
        $response_data = array(
		    'api_status' => 200,
		    'data' => $albums
		);
    }
}