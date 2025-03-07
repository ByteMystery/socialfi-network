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
    'api_status' => 400
);

$limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Wo_Secure($_POST['limit']) : 20);
$offset = (!empty($_POST['offset']) && is_numeric($_POST['offset']) && $_POST['offset'] > 0 ? Wo_Secure($_POST['offset']) : 0);

$get_activities = Wo_GetActivities(array('me' => true, 'limit' => $limit, 'after_activity_id' => $offset));

foreach ($get_activities as $key => $activity) {
    foreach ($non_allowed as $key => $value) {
       unset($activity['activator'][$value]);
       if (!empty($activity['postData'])) {
          unset($activity['postData']['publisher'][$value]);
          unset($activity['postData']['get_post_comments']);
       	  $activity['postData']['shared_from'] = (empty($activity['postData']['shared_from'])) ? null : $activity['postData']['shared_from'];
       } else {
       		$activity['postData'] = null;
       }
    }
    $activity['activity_text'] = '';
	$post_publisher              =  $activity['postData']['publisher']['name'];
	$orginal_txt                 = array(
	    "{user}",
	);
	if ($activity['activity_type'] == 'following' ||$activity['activity_type'] == 'friend') {
	   $following  = Wo_UserData($activity['follow_id']);
	   $post_publisher              =  $following['name'];
	}
	$replaced_txt                = array(
	    $post_publisher,
	);
	if (!empty($activity['activity_type'])) {
	    if ( substr($activity['activity_type'] , 0, 8) == "reaction") {
	        $txt = str_replace( "reaction|", "",$activity['activity_type'] );
	        $arr = explode( "|", $txt );
	        if( $arr[0] == "post" ){
	           $activity['activity_text'] = str_replace($orginal_txt, $replaced_txt, 'reacted to {user} post.');
	        }
	    }
	    if ($activity['activity_type'] == 'friend') {
	       $activity['activity_text'] = str_replace($orginal_txt, $replaced_txt, 'become friends with {user}');
	    }
	    if ($activity['activity_type'] == 'following') {
	       $activity['activity_text'] = str_replace($orginal_txt, $replaced_txt, 'is following {user}');
	    }
	    if ($activity['activity_type'] == 'liked_post') {
	       $activity['activity_text'] = str_replace($orginal_txt, $replaced_txt, 'liked {user} post.');
	    }
	    if ($activity['activity_type'] == 'wondered_post') {
	        $lang_type = ($wo['config']['second_post_button'] == 'wonder') ? 'wondered {user} post.' : 'disliked {user} post.';
	        $activity['activity_text'] = str_replace($orginal_txt, $replaced_txt, $lang_type);
	    }
	    if ($activity['activity_type'] == 'shared_post') {
	       $activity['activity_text'] = str_replace($orginal_txt, $replaced_txt, 'shared {user} post.');
	    }
	    if ($activity['activity_type'] == 'commented_post') {
	       $activity['activity_text'] = str_replace($orginal_txt, $replaced_txt, 'commented on {user} post.');
	    }
	}
	$activity['activity_text'] = $activity['activator']['name'] .' '. $activity['activity_text'];
    $activities[] = $activity;
}

$response_data = array(
                    'api_status' => 200,
                    'data' => $activities
                );