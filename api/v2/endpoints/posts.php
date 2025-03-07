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

$required_fields =  array(
                        'get_news_feed',
                        'get_user_posts',
                        'get_group_posts',
                        'get_page_posts',
                        'get_event_posts',
                        'share_post_on_timeline',
                        'share_post_on_page',
                        'share_post_on_group',
                        'saved',
                        'hashtag',
                        'get_random_groups',
                        'get_random_pages',
                        'get_random_videos'
                    );

$limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Wo_Secure($_POST['limit']) : 20);
$after_post_id = (!empty($_POST['after_post_id']) && is_numeric($_POST['after_post_id']) && $_POST['after_post_id'] > 0 ? Wo_Secure($_POST['after_post_id']) : 0);

if (!empty($_POST['type']) && in_array($_POST['type'], $required_fields)) {
	if ($_POST['type'] != 'get_news_feed' && $_POST['type'] != 'saved' && $_POST['type'] != 'hashtag' && $_POST['type'] != 'get_random_groups' && $_POST['type'] != 'get_random_pages' && $_POST['type'] != 'get_random_videos') {
		if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
			$error_code    = 5;
	        $error_message = 'id must be numeric and greater than 0';
		}
	}

	

	if (empty($error_code)) {
		if ($_POST['type'] == 'get_news_feed') {
			$type = 0;
	        if ($_POST['filter'] == 1) {
	            $type = 1;
	        }
	        $update = Wo_UpdateUserData($wo['user']['user_id'], array(
	            'order_posts_by' => $type
	        ));
	        $wo['user'] = Wo_UserData($wo['user']['user_id']);

			$postsData = array(
                'limit' => $limit,
                'publisher_id' => 0,
                'after_post_id' => $after_post_id,
                'placement' => 'multi_image_post',
                'anonymous' => true
            );
            if (!empty($_POST['ad_id']) && is_numeric($_POST['ad_id']) && $_POST['ad_id'] > 0) {
            	$postsData['ad-id'] = Wo_Secure($_POST['ad_id']);
            }
            if (!empty($_POST['post_type']) && in_array($_POST['post_type'], array('photos','video','music','files','maps','text'))) {
            	$postsData['filter_by'] = Wo_Secure($_POST['post_type']);
            }
			$posts = Wo_GetPosts($postsData);
			foreach ($posts as $key => $value) {
				$posts[$key]['shared_info'] = null;

				if (!empty($posts[$key]['postFile'])) {
					$posts[$key]['postFile'] = Wo_GetMedia($posts[$key]['postFile']);
				}
				if (!empty($posts[$key]['postFileThumb'])) {
					$posts[$key]['postFileThumb'] = Wo_GetMedia($posts[$key]['postFileThumb']);
				}

				if (!empty($posts[$key]['postPlaytube'])) {
					$posts[$key]['postText'] = strip_tags($posts[$key]['postText']);
				}



				if (!empty($posts[$key]['publisher'])) {
					foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['publisher'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['publisher'] = null;
			    }

			    if (!empty($posts[$key]['user_data'])) {
			    	foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['user_data'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['user_data'] = null;
			    }

			    if (!empty($posts[$key]['parent_id'])) {
			    	$shared_info = Wo_PostData($posts[$key]['parent_id']);
			    	if (!empty($shared_info)) {
			    		if (!empty($shared_info['publisher'])) {
							foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['publisher'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['publisher'] = null;
					    }

					    if (!empty($shared_info['user_data'])) {
					    	foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['user_data'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['user_data'] = null;
					    }

					    if (!empty($shared_info['get_post_comments'])) {
					        foreach ($shared_info['get_post_comments'] as $key3 => $comment) {

						        foreach ($non_allowed as $key5 => $value5) {
						          unset($shared_info['get_post_comments'][$key3]['publisher'][$value5]);
						        }
						    }
						}
			    	}
			    	$posts[$key]['shared_info'] = $shared_info;
			    }

			    if (!empty($value['get_post_comments'])) {
			        foreach ($value['get_post_comments'] as $key3 => $comment) {

				        foreach ($non_allowed as $key5 => $value5) {
				          unset($posts[$key]['get_post_comments'][$key3]['publisher'][$value5]);
				        }
				    }
				}
			}

			
			$response_data = array(
		                        'api_status' => 200,
		                        'data' => $posts
		                    );
		}

		if ($_POST['type'] == 'get_user_posts') {
			$user_id = Wo_Secure($_POST['id']);

			$postsData = array(
                'limit' => $limit,
                'publisher_id' => $user_id,
                'after_post_id' => $after_post_id,
                'placement' => 'multi_image_post'
            );
			$posts = Wo_GetPosts($postsData);
			foreach ($posts as $key => $value) {
				$posts[$key]['shared_info'] = null;

				if (!empty($posts[$key]['postFile'])) {
					$posts[$key]['postFile'] = Wo_GetMedia($posts[$key]['postFile']);
				}
				if (!empty($posts[$key]['postFileThumb'])) {
					$posts[$key]['postFileThumb'] = Wo_GetMedia($posts[$key]['postFileThumb']);
				}
				if (!empty($posts[$key]['postPlaytube'])) {
					$posts[$key]['postText'] = strip_tags($posts[$key]['postText']);
				}



				if (!empty($posts[$key]['publisher'])) {
					foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['publisher'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['publisher'] = null;
			    }

			    if (!empty($posts[$key]['user_data'])) {
			    	foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['user_data'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['user_data'] = null;
			    }

			    if (!empty($posts[$key]['parent_id'])) {
			    	$shared_info = Wo_PostData($posts[$key]['parent_id']);
			    	if (!empty($shared_info)) {
			    		if (!empty($shared_info['publisher'])) {
							foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['publisher'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['publisher'] = null;
					    }

					    if (!empty($shared_info['user_data'])) {
					    	foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['user_data'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['user_data'] = null;
					    }

					    if (!empty($shared_info['get_post_comments'])) {
					        foreach ($shared_info['get_post_comments'] as $key3 => $comment) {

						        foreach ($non_allowed as $key5 => $value5) {
						          unset($shared_info['get_post_comments'][$key3]['publisher'][$value5]);
						        }
						    }
						}
			    	}
			    	$posts[$key]['shared_info'] = $shared_info;
			    }

			    if (!empty($value['get_post_comments'])) {
			        foreach ($value['get_post_comments'] as $key3 => $comment) {

				        foreach ($non_allowed as $key5 => $value5) {
				          unset($posts[$key]['get_post_comments'][$key3]['publisher'][$value5]);
				        }
				    }
				}
			}

			
			$response_data = array(
		                        'api_status' => 200,
		                        'data' => $posts
		                    );
		}

		if ($_POST['type'] == 'get_group_posts') {
			$group_id = Wo_Secure($_POST['id']);

			$postsData = array(
                'limit' => $limit,
                'group_id' => $group_id,
                'after_post_id' => $after_post_id,
                'placement' => 'multi_image_post'
            );
			$posts = Wo_GetPosts($postsData);
			foreach ($posts as $key => $value) {
				$posts[$key]['shared_info'] = null;

				if (!empty($posts[$key]['postFile'])) {
					$posts[$key]['postFile'] = Wo_GetMedia($posts[$key]['postFile']);
				}
				if (!empty($posts[$key]['postFileThumb'])) {
					$posts[$key]['postFileThumb'] = Wo_GetMedia($posts[$key]['postFileThumb']);
				}
				if (!empty($posts[$key]['postPlaytube'])) {
					$posts[$key]['postText'] = strip_tags($posts[$key]['postText']);
				}



				if (!empty($posts[$key]['publisher'])) {
					foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['publisher'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['publisher'] = null;
			    }

			    if (!empty($posts[$key]['user_data'])) {
			    	foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['user_data'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['user_data'] = null;
			    }

			    if (!empty($posts[$key]['parent_id'])) {
			    	$shared_info = Wo_PostData($posts[$key]['parent_id']);
			    	if (!empty($shared_info)) {
			    		if (!empty($shared_info['publisher'])) {
							foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['publisher'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['publisher'] = null;
					    }

					    if (!empty($shared_info['user_data'])) {
					    	foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['user_data'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['user_data'] = null;
					    }

					    if (!empty($shared_info['get_post_comments'])) {
					        foreach ($shared_info['get_post_comments'] as $key3 => $comment) {

						        foreach ($non_allowed as $key5 => $value5) {
						          unset($shared_info['get_post_comments'][$key3]['publisher'][$value5]);
						        }
						    }
						}
			    	}
			    	$posts[$key]['shared_info'] = $shared_info;
			    }

			    if (!empty($value['get_post_comments'])) {
			        foreach ($value['get_post_comments'] as $key3 => $comment) {

				        foreach ($non_allowed as $key5 => $value5) {
				          unset($posts[$key]['get_post_comments'][$key3]['publisher'][$value5]);
				        }
				    }
				}
			}

			
			$response_data = array(
		                        'api_status' => 200,
		                        'data' => $posts
		                    );
		}

		if ($_POST['type'] == 'get_page_posts') {
			$page_id = Wo_Secure($_POST['id']);

			$postsData = array(
                'limit' => $limit,
                'page_id' => $page_id,
                'after_post_id' => $after_post_id,
                'placement' => 'multi_image_post'
            );
			$posts = Wo_GetPosts($postsData);
			foreach ($posts as $key => $value) {
				$posts[$key]['shared_info'] = null;

				if (!empty($posts[$key]['postFile'])) {
					$posts[$key]['postFile'] = Wo_GetMedia($posts[$key]['postFile']);
				}
				if (!empty($posts[$key]['postFileThumb'])) {
					$posts[$key]['postFileThumb'] = Wo_GetMedia($posts[$key]['postFileThumb']);
				}
				if (!empty($posts[$key]['postPlaytube'])) {
					$posts[$key]['postText'] = strip_tags($posts[$key]['postText']);
				}



				if (!empty($posts[$key]['publisher'])) {
					foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['publisher'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['publisher'] = null;
			    }

			    if (!empty($posts[$key]['user_data'])) {
			    	foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['user_data'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['user_data'] = null;
			    }

			    if (!empty($posts[$key]['parent_id'])) {
			    	$shared_info = Wo_PostData($posts[$key]['parent_id']);
			    	if (!empty($shared_info)) {
			    		if (!empty($shared_info['publisher'])) {
							foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['publisher'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['publisher'] = null;
					    }

					    if (!empty($shared_info['user_data'])) {
					    	foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['user_data'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['user_data'] = null;
					    }

					    if (!empty($shared_info['get_post_comments'])) {
					        foreach ($shared_info['get_post_comments'] as $key3 => $comment) {

						        foreach ($non_allowed as $key5 => $value5) {
						          unset($shared_info['get_post_comments'][$key3]['publisher'][$value5]);
						        }
						    }
						}
			    	}
			    	$posts[$key]['shared_info'] = $shared_info;
			    }

			    if (!empty($value['get_post_comments'])) {
			        foreach ($value['get_post_comments'] as $key3 => $comment) {

				        foreach ($non_allowed as $key5 => $value5) {
				          unset($posts[$key]['get_post_comments'][$key3]['publisher'][$value5]);
				        }
				    }
				}
			}

			
			$response_data = array(
		                        'api_status' => 200,
		                        'data' => $posts
		                    );
		}

		if ($_POST['type'] == 'get_event_posts') {
			$event_id = Wo_Secure($_POST['id']);

			$postsData = array(
                'limit' => $limit,
                'event_id' => $event_id,
                'after_post_id' => $after_post_id,
                'placement' => 'multi_image_post'
            );
			$posts = Wo_GetPosts($postsData);
			foreach ($posts as $key => $value) {
				$posts[$key]['shared_info'] = null;

				if (!empty($posts[$key]['postFile'])) {
					$posts[$key]['postFile'] = Wo_GetMedia($posts[$key]['postFile']);
				}
				if (!empty($posts[$key]['postFileThumb'])) {
					$posts[$key]['postFileThumb'] = Wo_GetMedia($posts[$key]['postFileThumb']);
				}
				if (!empty($posts[$key]['postPlaytube'])) {
					$posts[$key]['postText'] = strip_tags($posts[$key]['postText']);
				}



				if (!empty($posts[$key]['publisher'])) {
					foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['publisher'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['publisher'] = null;
			    }

			    if (!empty($posts[$key]['user_data'])) {
			    	foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['user_data'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['user_data'] = null;
			    }

			    if (!empty($posts[$key]['parent_id'])) {
			    	$shared_info = Wo_PostData($posts[$key]['parent_id']);
			    	if (!empty($shared_info)) {
			    		if (!empty($shared_info['publisher'])) {
							foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['publisher'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['publisher'] = null;
					    }

					    if (!empty($shared_info['user_data'])) {
					    	foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['user_data'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['user_data'] = null;
					    }

					    if (!empty($shared_info['get_post_comments'])) {
					        foreach ($shared_info['get_post_comments'] as $key3 => $comment) {

						        foreach ($non_allowed as $key5 => $value5) {
						          unset($shared_info['get_post_comments'][$key3]['publisher'][$value5]);
						        }
						    }
						}
			    	}
			    	$posts[$key]['shared_info'] = $shared_info;
			    }

			    if (!empty($value['get_post_comments'])) {
			        foreach ($value['get_post_comments'] as $key3 => $comment) {

				        foreach ($non_allowed as $key5 => $value5) {
				          unset($posts[$key]['get_post_comments'][$key3]['publisher'][$value5]);
				        }
				    }
				}
			}

			
			$response_data = array(
		                        'api_status' => 200,
		                        'data' => $posts
		                    );
		}

		if ($_POST['type'] == 'share_post_on_timeline') {
			$user = Wo_UserData(Wo_Secure($_POST['user_id']));
			$post = Wo_PostData(Wo_Secure($_POST['id']));
	        $user_id = $post['user_id'];
	        if (empty($post['user_id']) && !empty($post['page_id'])) {
	            $page = Wo_PageData($post['page_id']);
	            $user_id = $page['user_id'];
	        }
	        if (!empty($post) && !empty($user)) {
	            $result = Wo_SharePostOn($post['id'],$user['user_id'],'user');
	            if (!empty($_POST['text'])) {
		            $updatePost = Wo_UpdatePost(array(
		                'post_id' => $result,
		                'text' => $_POST['text']
		            ));
		        }
	            $new_post = Wo_PostData($result);
	            if (!empty($new_post['publisher'])) {
                    foreach ($non_allowed as $key4 => $value4) {
                      unset($new_post['publisher'][$value4]);
                    }
                }
                if (!empty($new_post['get_post_comments'])) {
			        foreach ($new_post['get_post_comments'] as $key3 => $comment) {

				        foreach ($non_allowed as $key5 => $value5) {
				          unset($new_post['get_post_comments'][$key3]['publisher'][$value5]);
				        }
				    }
				}

				$notification_data_array = array(
		            'recipient_id' => $user_id,
		            'post_id' => $post['id'],
		            'type' => 'shared_your_post',
		            'url' => 'index.php?link1=post&id=' . $result
		        );
		        Wo_RegisterNotification($notification_data_array);

	            $notification_data_array = array(
	                'recipient_id' => $user['id'],
	                'post_id' => $post['id'],
	                'type' => 'shared_a_post_in_timeline',
	                'url' => 'index.php?link1=post&id=' . $result
	            );
	            Wo_RegisterNotification($notification_data_array);

	            $response_data = array(
		                        'api_status' => 200,
		                        'data' => $new_post
		                    );
	        }
	        else{
	        	$error_code    = 5;
			    $error_message = 'id and user_id can not be empty';
	        }
		}

		if ($_POST['type'] == 'share_post_on_page') {
			$page = Wo_PageData(Wo_Secure($_POST['page_id']));
	        $post = Wo_PostData(Wo_Secure($_POST['id']));
	        $user_id = $post['user_id'];
	        if (empty($post['user_id'])) {
	            $user_id = $page['user_id'];
	        }
	        if (!empty($post) && !empty($page) && $page['user_id'] == $wo['user']['id']) {
	            $result = Wo_SharePostOn($post['id'],$page['id'],'page');
	            if (!empty($_POST['text'])) {
		            $updatePost = Wo_UpdatePost(array(
		                'post_id' => $result,
		                'text' => $_POST['text']
		            ));
		        }
	            $new_post = Wo_PostData($result);
	            if (!empty($new_post['publisher'])) {
                    foreach ($non_allowed as $key4 => $value4) {
                      unset($new_post['publisher'][$value4]);
                    }
                }
                if (!empty($new_post['get_post_comments'])) {
			        foreach ($new_post['get_post_comments'] as $key3 => $comment) {

				        foreach ($non_allowed as $key5 => $value5) {
				          unset($new_post['get_post_comments'][$key3]['publisher'][$value5]);
				        }
				    }
				}

				$notification_data_array = array(
		            'recipient_id' => $user_id,
		            'post_id' => $post['id'],
		            'type' => 'shared_your_post',
		            'url' => 'index.php?link1=post&id=' . $result
		        );
		        Wo_RegisterNotification($notification_data_array);

	            $response_data = array(
		                        'api_status' => 200,
		                        'data' => $new_post
		                    );
	        }
	        else{
	        	$error_code    = 6;
			    $error_message = 'id and page_id can not be empty';
	        }
		}

		if ($_POST['type'] == 'share_post_on_group') {
			$group = Wo_GroupData(Wo_Secure($_POST['group_id']));
	        $post = Wo_PostData(Wo_Secure($_POST['id']));
	        $user_id = $post['user_id'];
	        if (empty($post['user_id'])) {
	            $user_id = $page['user_id'];
	        }
	        if (!empty($post) && !empty($group) && $group['user_id'] == $wo['user']['id']) {
	            $result = Wo_SharePostOn($post['id'],$group['id'],'group');
	            if (!empty($_POST['text'])) {
		            $updatePost = Wo_UpdatePost(array(
		                'post_id' => $result,
		                'text' => $_POST['text']
		            ));
		        }
	            $new_post = Wo_PostData($result);
	            if (!empty($new_post['publisher'])) {
                    foreach ($non_allowed as $key4 => $value4) {
                      unset($new_post['publisher'][$value4]);
                    }
                }
                if (!empty($new_post['get_post_comments'])) {
			        foreach ($new_post['get_post_comments'] as $key3 => $comment) {

				        foreach ($non_allowed as $key5 => $value5) {
				          unset($new_post['get_post_comments'][$key3]['publisher'][$value5]);
				        }
				    }
				}

				$notification_data_array = array(
		            'recipient_id' => $user_id,
		            'post_id' => $post['id'],
		            'type' => 'shared_your_post',
		            'url' => 'index.php?link1=post&id=' . $result
		        );
		        Wo_RegisterNotification($notification_data_array);

	            $response_data = array(
		                        'api_status' => 200,
		                        'data' => $new_post
		                    );
	        }
	        else{
	        	$error_code    = 7;
			    $error_message = 'id and group_id can not be empty';
	        }
		}

		if ($_POST['type'] == 'saved') {
			$posts = Wo_GetSavedPosts($wo['user']['user_id'],$after_post_id,$limit);

			foreach ($posts as $key => $value) {
				$posts[$key]['shared_info'] = null;

				if (!empty($posts[$key]['postFile'])) {
					$posts[$key]['postFile'] = Wo_GetMedia($posts[$key]['postFile']);
				}
				if (!empty($posts[$key]['postFileThumb'])) {
					$posts[$key]['postFileThumb'] = Wo_GetMedia($posts[$key]['postFileThumb']);
				}

				if (!empty($posts[$key]['postPlaytube'])) {
					$posts[$key]['postText'] = strip_tags($posts[$key]['postText']);
				}



				if (!empty($posts[$key]['publisher'])) {
					foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['publisher'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['publisher'] = null;
			    }

			    if (!empty($posts[$key]['user_data'])) {
			    	foreach ($non_allowed as $key4 => $value4) {
			          unset($posts[$key]['user_data'][$value4]);
			        }
			    }
			    else{
			    	$posts[$key]['user_data'] = null;
			    }

			    if (!empty($posts[$key]['parent_id'])) {
			    	$shared_info = Wo_PostData($posts[$key]['parent_id']);
			    	if (!empty($shared_info)) {
			    		if (!empty($shared_info['publisher'])) {
							foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['publisher'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['publisher'] = null;
					    }

					    if (!empty($shared_info['user_data'])) {
					    	foreach ($non_allowed as $key4 => $value4) {
					          unset($shared_info['user_data'][$value4]);
					        }
					    }
					    else{
					    	$shared_info['user_data'] = null;
					    }

					    if (!empty($shared_info['get_post_comments'])) {
					        foreach ($shared_info['get_post_comments'] as $key3 => $comment) {

						        foreach ($non_allowed as $key5 => $value5) {
						          unset($shared_info['get_post_comments'][$key3]['publisher'][$value5]);
						        }
						    }
						}
			    	}
			    	$posts[$key]['shared_info'] = $shared_info;
			    }

			    if (!empty($value['get_post_comments'])) {
			        foreach ($value['get_post_comments'] as $key3 => $comment) {

				        foreach ($non_allowed as $key5 => $value5) {
				          unset($posts[$key]['get_post_comments'][$key3]['publisher'][$value5]);
				        }
				    }
				}
			}
			$response_data = array(
		                        'api_status' => 200,
		                        'data' => $posts
		                    );
		}

		if ($_POST['type'] == 'hashtag') {
			if (!empty($_POST['hash'])) {
				$posts = Wo_GetHashtagPosts($_POST['hash'],$after_post_id,$limit);

				foreach ($posts as $key => $value) {
					$posts[$key]['shared_info'] = null;
					$posts[$key]['postText']    = strip_tags($posts[$key]['postText']);

					if (!empty($posts[$key]['postFile'])) {
						$posts[$key]['postFile'] = Wo_GetMedia($posts[$key]['postFile']);
					}
					if (!empty($posts[$key]['postFileThumb'])) {
						$posts[$key]['postFileThumb'] = Wo_GetMedia($posts[$key]['postFileThumb']);
					}

					if (!empty($posts[$key]['postPlaytube'])) {
						$posts[$key]['postText'] = strip_tags($posts[$key]['postText']);
					}



					if (!empty($posts[$key]['publisher'])) {
						foreach ($non_allowed as $key4 => $value4) {
				          unset($posts[$key]['publisher'][$value4]);
				        }
				    }
				    else{
				    	$posts[$key]['publisher'] = null;
				    }

				    if (!empty($posts[$key]['user_data'])) {
				    	foreach ($non_allowed as $key4 => $value4) {
				          unset($posts[$key]['user_data'][$value4]);
				        }
				    }
				    else{
				    	$posts[$key]['user_data'] = null;
				    }

				    if (!empty($posts[$key]['parent_id'])) {
				    	$shared_info = Wo_PostData($posts[$key]['parent_id']);
				    	if (!empty($shared_info)) {
				    		if (!empty($shared_info['publisher'])) {
								foreach ($non_allowed as $key4 => $value4) {
						          unset($shared_info['publisher'][$value4]);
						        }
						    }
						    else{
						    	$shared_info['publisher'] = null;
						    }

						    if (!empty($shared_info['user_data'])) {
						    	foreach ($non_allowed as $key4 => $value4) {
						          unset($shared_info['user_data'][$value4]);
						        }
						    }
						    else{
						    	$shared_info['user_data'] = null;
						    }

						    if (!empty($shared_info['get_post_comments'])) {
						        foreach ($shared_info['get_post_comments'] as $key3 => $comment) {

							        foreach ($non_allowed as $key5 => $value5) {
							          unset($shared_info['get_post_comments'][$key3]['publisher'][$value5]);
							        }
							    }
							}
				    	}
				    	$posts[$key]['shared_info'] = $shared_info;
				    }

				    if (!empty($value['get_post_comments'])) {
				        foreach ($value['get_post_comments'] as $key3 => $comment) {

					        foreach ($non_allowed as $key5 => $value5) {
					          unset($posts[$key]['get_post_comments'][$key3]['publisher'][$value5]);
					        }
					    }
					}
				}
				$response_data = array(
			                        'api_status' => 200,
			                        'data' => $posts
			                    );
			}
			else{
				$error_code    = 6;
		        $error_message = 'hash (post) is missing';
			}
		}

		if ($_POST['type'] == 'get_random_groups') {
			$sql = "";
			if (!empty($after_post_id)) {
				$sql = " AND P.id < ".$after_post_id;
			}
			$posts = $db->rawQuery("SELECT P.id FROM ".T_GROUP_MEMBERS." M , ".T_GROUPS." G , ".T_POSTS." P WHERE ((M.user_id = ".$wo['user']['user_id']." AND M.group_id = G.id) OR G.privacy = 1) AND P.group_id = G.id ".$sql." GROUP BY P.id ORDER BY P.id DESC LIMIT ".$limit);
			$posts_data = array();
			if (!empty($posts)) {
				foreach ($posts as $key => $value9) {
					$post = Wo_PostData($value9->id);
					$post['shared_info'] = null;

					if (!empty($post['postFile'])) {
						$post['postFile'] = Wo_GetMedia($post['postFile']);
					}
					if (!empty($post['postFileThumb'])) {
						$post['postFileThumb'] = Wo_GetMedia($post['postFileThumb']);
					}

					if (!empty($post['postPlaytube'])) {
						$post['postText'] = strip_tags($post['postText']);
					}



					if (!empty($post['publisher'])) {
						foreach ($non_allowed as $key4 => $value4) {
				          unset($post['publisher'][$value4]);
				        }
				    }
				    else{
				    	$post['publisher'] = null;
				    }

				    if (!empty($post['user_data'])) {
				    	foreach ($non_allowed as $key4 => $value4) {
				          unset($post['user_data'][$value4]);
				        }
				    }
				    else{
				    	$post['user_data'] = null;
				    }

				    if (!empty($post['parent_id'])) {
				    	$shared_info = Wo_PostData($post['parent_id']);
				    	if (!empty($shared_info)) {
				    		if (!empty($shared_info['publisher'])) {
								foreach ($non_allowed as $key4 => $value4) {
						          unset($shared_info['publisher'][$value4]);
						        }
						    }
						    else{
						    	$shared_info['publisher'] = null;
						    }

						    if (!empty($shared_info['user_data'])) {
						    	foreach ($non_allowed as $key4 => $value4) {
						          unset($shared_info['user_data'][$value4]);
						        }
						    }
						    else{
						    	$shared_info['user_data'] = null;
						    }

						    if (!empty($shared_info['get_post_comments'])) {
						        foreach ($shared_info['get_post_comments'] as $key3 => $comment) {

							        foreach ($non_allowed as $key5 => $value5) {
							          unset($shared_info['get_post_comments'][$key3]['publisher'][$value5]);
							        }
							    }
							}
				    	}
				    	$post['shared_info'] = $shared_info;
				    }

				    if (!empty($post['get_post_comments'])) {
				        foreach ($post['get_post_comments'] as $key3 => $comment) {

					        foreach ($non_allowed as $key5 => $value5) {
					          unset($post['get_post_comments'][$key3]['publisher'][$value5]);
					        }
					    }
					}
					$posts_data[] = $post;
				}
			}
			$response_data = array(
			                        'api_status' => 200,
			                        'data' => $posts_data
			                    );
		}
		if ($_POST['type'] == 'get_random_pages') {
			$sql = "";
			if (!empty($after_post_id)) {
				$sql = " AND P.id < ".$after_post_id;
			}
			$posts = $db->rawQuery("SELECT P.id FROM ".T_POSTS." P WHERE P.page_id > 0 ".$sql." GROUP BY P.id ORDER BY P.id DESC LIMIT ".$limit);
			$posts_data = array();
			if (!empty($posts)) {
				foreach ($posts as $key => $value9) {
					$post = Wo_PostData($value9->id);
					$post['shared_info'] = null;

					if (!empty($post['postFile'])) {
						$post['postFile'] = Wo_GetMedia($post['postFile']);
					}
					if (!empty($post['postFileThumb'])) {
						$post['postFileThumb'] = Wo_GetMedia($post['postFileThumb']);
					}

					if (!empty($post['postPlaytube'])) {
						$post['postText'] = strip_tags($post['postText']);
					}



					if (!empty($post['publisher'])) {
						foreach ($non_allowed as $key4 => $value4) {
				          unset($post['publisher'][$value4]);
				        }
				    }
				    else{
				    	$post['publisher'] = null;
				    }

				    if (!empty($post['user_data'])) {
				    	foreach ($non_allowed as $key4 => $value4) {
				          unset($post['user_data'][$value4]);
				        }
				    }
				    else{
				    	$post['user_data'] = null;
				    }

				    if (!empty($post['parent_id'])) {
				    	$shared_info = Wo_PostData($post['parent_id']);
				    	if (!empty($shared_info)) {
				    		if (!empty($shared_info['publisher'])) {
								foreach ($non_allowed as $key4 => $value4) {
						          unset($shared_info['publisher'][$value4]);
						        }
						    }
						    else{
						    	$shared_info['publisher'] = null;
						    }

						    if (!empty($shared_info['user_data'])) {
						    	foreach ($non_allowed as $key4 => $value4) {
						          unset($shared_info['user_data'][$value4]);
						        }
						    }
						    else{
						    	$shared_info['user_data'] = null;
						    }

						    if (!empty($shared_info['get_post_comments'])) {
						        foreach ($shared_info['get_post_comments'] as $key3 => $comment) {

							        foreach ($non_allowed as $key5 => $value5) {
							          unset($shared_info['get_post_comments'][$key3]['publisher'][$value5]);
							        }
							    }
							}
				    	}
				    	$post['shared_info'] = $shared_info;
				    }

				    if (!empty($post['get_post_comments'])) {
				        foreach ($post['get_post_comments'] as $key3 => $comment) {

					        foreach ($non_allowed as $key5 => $value5) {
					          unset($post['get_post_comments'][$key3]['publisher'][$value5]);
					        }
					    }
					}
					$posts_data[] = $post;
				}
			}
			$response_data = array(
			                        'api_status' => 200,
			                        'data' => $posts_data
			                    );

		}
		if ($_POST['type'] == 'get_random_videos') {
			$sql = "";
			if (!empty($after_post_id)) {
				$sql = " AND id < ".$after_post_id;
			}
			$posts = $db->rawQuery("SELECT id FROM ".T_POSTS."  WHERE postPrivacy = '0' ".$sql." AND (`postYoutube` <> '' OR `postVine` <> '' OR `postFacebook` <> '' OR `postDailymotion` <> '' OR `postVimeo` <> '' OR `postPlaytube` <> '' OR `postFile` LIKE '%_video%') ORDER BY id DESC LIMIT ".$limit);
			$posts_data = array();
			if (!empty($posts)) {
				foreach ($posts as $key => $value9) {
					$post = Wo_PostData($value9->id);
					$post['shared_info'] = null;

					if (!empty($post['postFile'])) {
						$post['postFile'] = Wo_GetMedia($post['postFile']);
					}
					if (!empty($post['postFileThumb'])) {
						$post['postFileThumb'] = Wo_GetMedia($post['postFileThumb']);
					}

					if (!empty($post['postPlaytube'])) {
						$post['postText'] = strip_tags($post['postText']);
					}



					if (!empty($post['publisher'])) {
						foreach ($non_allowed as $key4 => $value4) {
				          unset($post['publisher'][$value4]);
				        }
				    }
				    else{
				    	$post['publisher'] = null;
				    }

				    if (!empty($post['user_data'])) {
				    	foreach ($non_allowed as $key4 => $value4) {
				          unset($post['user_data'][$value4]);
				        }
				    }
				    else{
				    	$post['user_data'] = null;
				    }

				    if (!empty($post['parent_id'])) {
				    	$shared_info = Wo_PostData($post['parent_id']);
				    	if (!empty($shared_info)) {
				    		if (!empty($shared_info['publisher'])) {
								foreach ($non_allowed as $key4 => $value4) {
						          unset($shared_info['publisher'][$value4]);
						        }
						    }
						    else{
						    	$shared_info['publisher'] = null;
						    }

						    if (!empty($shared_info['user_data'])) {
						    	foreach ($non_allowed as $key4 => $value4) {
						          unset($shared_info['user_data'][$value4]);
						        }
						    }
						    else{
						    	$shared_info['user_data'] = null;
						    }

						    if (!empty($shared_info['get_post_comments'])) {
						        foreach ($shared_info['get_post_comments'] as $key3 => $comment) {

							        foreach ($non_allowed as $key5 => $value5) {
							          unset($shared_info['get_post_comments'][$key3]['publisher'][$value5]);
							        }
							    }
							}
				    	}
				    	$post['shared_info'] = $shared_info;
				    }

				    if (!empty($post['get_post_comments'])) {
				        foreach ($post['get_post_comments'] as $key3 => $comment) {

					        foreach ($non_allowed as $key5 => $value5) {
					          unset($post['get_post_comments'][$key3]['publisher'][$value5]);
					        }
					    }
					}
					$posts_data[] = $post;
				}
			}
			$response_data = array(
			                        'api_status' => 200,
			                        'data' => $posts_data
			                    );

		}
	}

}
else{
	$error_code    = 4;
    $error_message = 'type can not be empty';
}