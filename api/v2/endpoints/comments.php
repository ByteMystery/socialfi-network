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
                        'create',
                        'delete',
                        'edit',
                        'create_reply',
                        'edit_reply',
                        'delete_reply',
                        'fetch_comments',
                        'fetch_comments_reply',
                        'reaction_comment',
                        'reaction_reply',
                        'comment_like',
                        'comment_dislike',
                        'reply_like',
                        'reply_dislike',
                        'get_comment_likes',
                        'get_comment_dislikes'
                    );

$limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Wo_Secure($_POST['limit']) : 20);
$after_post_id = (!empty($_POST['after_post_id']) && is_numeric($_POST['after_post_id']) && $_POST['after_post_id'] > 0 ? Wo_Secure($_POST['after_post_id']) : 0);
$offset = (!empty($_POST['offset']) && is_numeric($_POST['offset']) && $_POST['offset'] > 0 ? Wo_Secure($_POST['offset']) : 0);

if (!empty($_POST['type']) && in_array($_POST['type'], $required_fields)) {

    if ($_POST['type'] == 'create') {
        if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && $_POST['post_id'] > 0) {
            if (!empty($_FILES['image']) || !empty($_POST['text']) || !empty($_FILES['audio']) || !empty($_POST['image_url'])) {

                $page_id = '';
                if (!empty($_POST['page_id'])) {
                    $page_id = $_POST['page_id'];
                }
                $comment_image = '';
                $record = '';
                if (!empty($_POST['image_url'])) {
                    $comment_image = Wo_ImportImageFromUrl($_POST['image_url']);
                }

                if (isset($_FILES['image']['name'])) {
                    $fileInfo = array(
                        'file' => $_FILES["image"]["tmp_name"],
                        'name' => $_FILES['image']['name'],
                        'size' => $_FILES["image"]["size"],
                        'type' => $_FILES["image"]["type"]
                    );
                    $media    = Wo_ShareFile($fileInfo);
                    if (!empty($media)) {
                        $comment_image    = $media['filename'];
                    }
                }
                if (!empty($_FILES["audio"])) {
                    $fileInfo         = array(
                        'file' => $_FILES["audio"]["tmp_name"],
                        'name' => $_FILES['audio']['name'],
                        'size' => $_FILES["audio"]["size"],
                        'type' => $_FILES["audio"]["type"],
                        'types' => 'mp3,wav'
                    );
                    $media            = Wo_ShareFile($fileInfo);
                    $record = $media['filename'];
                }
                if (empty($comment_image) && empty($_POST['text']) && empty($record)) {
                    $error_code    = 5;
                    $error_message = 'Please check your details.';
                    $response_data       = array(
                        'api_status'     => '404',
                        'errors'         => array(
                            'error_id'   => $error_code,
                            'error_text' => $error_message
                        )
                    );
                    echo json_encode($response_data, JSON_PRETTY_PRINT);
                    exit();
                }
                $text_comment = '';
                if (!empty($_POST['text']) && !ctype_space($_POST['text'])) {
                    $text_comment = $_POST['text'];
                }
                
                $C_Data = array(
                    'user_id' => Wo_Secure($wo['user']['user_id']),
                    'page_id' => Wo_Secure($page_id),
                    'post_id' => Wo_Secure($_POST['post_id']),
                    'text' => Wo_Secure($text_comment),
                    'c_file' => Wo_Secure($comment_image),
                    'time' => time()
                );
                if (!empty($record)) {
                    $C_Data['record'] = $record;
                }
                
                $R_Comment     = Wo_RegisterPostComment($C_Data);
                $comment       = Wo_GetPostComment($R_Comment);
                if (!empty($comment['publisher'])) {
                    foreach ($non_allowed as $key4 => $value4) {
                      unset($comment['publisher'][$value4]);
                    }
                }
                if (!empty($text_comment)) {
                    //$comment['text'] = $text_comment;
                }
                if (!empty($comment['c_file'])) {
                    $comment['c_file'] = Wo_GetMedia($comment['c_file']);
                }
                if (!empty($comment['record'])) {
                    $comment['record'] = Wo_GetMedia($comment['record']);
                }

                $response_data = array(
                                        'api_status' => 200,
                                        'data' => $comment
                                    );
            }
            else{
                $error_code    = 5;
                $error_message = 'Please check your details.';
            }
        }
        else{
            $error_code    = 6;
            $error_message = 'post_id can not be empty.';
        }
    }

    if ($_POST['type'] == 'delete') {
        if (!empty($_POST['comment_id']) && is_numeric($_POST['comment_id']) && $_POST['comment_id'] > 0) {
            $comment_id = Wo_Secure($_POST['comment_id']);
            $DeleteComment = Wo_DeletePostComment($comment_id);
            $response_data = array(
                'api_status' => 200,
                'message' => "comment successfully deleted."
            );
        }
        else{
            $error_code    = 7;
            $error_message = 'comment_id can not be empty.';
        }
    }

    if ($_POST['type'] == 'edit') {
        if (!empty($_POST['comment_id']) && !empty($_POST['text'])) {
            $updateComment = Wo_UpdateComment(array(
                'comment_id' => $_POST['comment_id'],
                'text' => $_POST['text']
            ));
            if (!empty($updateComment)) {
                $response_data = array(
                    'api_status' => 200,
                    'message' => "comment successfully edited."
                );
            }
            else{
                $error_code    = 8;
                $error_message = 'something wrong';
            }
        }
        else{
            $error_code    = 9;
            $error_message = 'comment_id and text can not be empty';
        }
    }

    if ($_POST['type'] == 'create_reply') {
        if (!empty($_POST['comment_id']) && (!empty($_POST['text']) || !empty($_FILES['image']))) {
            $page_id = '';
            if (!empty($_POST['page_id'])) {
                $page_id = $_POST['page_id'];
            }
            $comment_image = '';

            if (isset($_FILES['image']['name'])) {
                $fileInfo = array(
                    'file' => $_FILES["image"]["tmp_name"],
                    'name' => $_FILES['image']['name'],
                    'size' => $_FILES["image"]["size"],
                    'type' => $_FILES["image"]["type"]
                );
                $media    = Wo_ShareFile($fileInfo);
                if (!empty($media)) {
                    $comment_image    = $media['filename'];
                }
            }
            if (empty($comment_image) && empty($_POST['text'])) {
                header("Content-type: application/json");
                echo json_encode($data);
                exit();
            }

            $C_Data      = array(
                'user_id' => Wo_Secure($wo['user']['user_id']),
                'page_id' => Wo_Secure($page_id),
                'comment_id' => Wo_Secure($_POST['comment_id']),
                'text' => Wo_Secure($_POST['text']),
                'c_file' => Wo_Secure($comment_image),
                'time' => time()
            );
            $R_Comment   = Wo_RegisterCommentReply($C_Data);
            $comment = Wo_GetCommentReply($R_Comment);
            if (!empty($comment)) {
                if (!empty($comment['publisher'])) {
                    foreach ($non_allowed as $key4 => $value4) {
                      unset($comment['publisher'][$value4]);
                    }
                }
                if (!empty($comment['c_file'])) {
                    $comment['c_file'] = Wo_GetMedia($comment['c_file']);
                }
                $comment['text'] = strip_tags($comment['text']);
                $response_data = array(
                                        'api_status' => 200,
                                        'data' => $comment
                                    );
            }
        }
        else{
            $error_code    = 5;
            $error_message = 'Please check your details.';
        }
    }
    if ($_POST['type'] == 'edit_reply') {
        if (!empty($_POST['reply_id']) && !empty($_POST['text'])) {
            $id           = Wo_Secure($_POST['reply_id']);
            $update_datau = array(
                'text' => Wo_Secure($_POST['text'])
            );

            if (Wo_UpdateCommentReply($id, $update_datau)) {
                $response_data = array(
                    'api_status' => 200,
                    'message' => "reply successfully edited."
                );
            }
            else{
                $error_code    = 8;
                $error_message = 'something wrong';
            }
        }
        else{
            $error_code    = 10;
            $error_message = 'reply_id and text can not be empty';
        }
    }

    if ($_POST['type'] == 'delete_reply') {
        if (!empty($_POST['reply_id'])) {
            $DeleteComment = Wo_DeletePostReplyComment($_POST['reply_id']);
            $response_data = array(
                'api_status' => 200,
                'message' => "comment reply successfully deleted."
            );
        }
        else{
            $error_code    = 11;
            $error_message = 'reply_id can not be empty.';
        }
    }

    if ($_POST['type'] == 'fetch_comments') {
        if (!empty($_POST['post_id'])) {
            $post = Wo_PostData($_POST['post_id']);
            if (!empty($post)) {
                $limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Wo_Secure($_POST['limit']) : 20);
                $offset = (!empty($_POST['offset']) && is_numeric($_POST['offset']) && $_POST['offset'] > 0 ? Wo_Secure($_POST['offset']) : 0);
                $comments = Wo_GetPostCommentsAPI($_POST['post_id'], $limit, $offset);
                foreach ($comments as $key => $value) {
                    if (!empty($value)) {
                        foreach ($non_allowed as $key4 => $value4) {
                          unset($comments[$key]['publisher'][$value4]);
                        }
                    }
                    $comments[$key]['text'] = strip_tags($comments[$key]['text']);
                    $comments[$key]['replies'] = Wo_CountCommentReplies($comments[$key]['id']);
                    if (!empty($comments[$key]['record'])) {
                        $comments[$key]['record'] = Wo_GetMedia($comments[$key]['record']);
                    }
                    if (!empty($comments[$key]['c_file'])) {
                        $comments[$key]['c_file'] = Wo_GetMedia($comments[$key]['c_file']);
                    }
                }

                $response_data = array(
                                        'api_status' => 200,
                                        'data' => $comments
                                    );
            }
            else{
                $error_code    = 13;
                $error_message = 'post not found.';
            }
        }
        else{
            $error_code    = 12;
            $error_message = 'post_id can not be empty.';
        }
    }

    if ($_POST['type'] == 'fetch_comments_reply') {
        if (!empty($_POST['comment_id'])) {
            $limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Wo_Secure($_POST['limit']) : 20);
            $offset = (!empty($_POST['offset']) && is_numeric($_POST['offset']) && $_POST['offset'] > 0 ? Wo_Secure($_POST['offset']) : 0);

            $replies = Wo_GetCommentRepliesAPI($_POST['comment_id'], $limit, 'ASC', $offset);
            foreach ($replies as $key => $value) {
                if (!empty($value)) {
                    foreach ($non_allowed as $key4 => $value4) {
                      unset($replies[$key]['publisher'][$value4]);
                    }
                }
                $replies[$key]['text'] = strip_tags($replies[$key]['text']);
                if (!empty($replies[$key]['c_file'])) {
                    $replies[$key]['c_file'] = Wo_GetMedia($replies[$key]['c_file']);
                }
            }

            $response_data = array(
                                        'api_status' => 200,
                                        'data' => $replies
                                    );
        }
        else{
            $error_code    = 7;
            $error_message = 'comment_id can not be empty.';
        }
    }
    if ($_POST['type'] == 'reaction_comment') {
        if (!empty($_POST['comment_id']) && is_numeric($_POST['comment_id']) && $_POST['comment_id'] > 0) {
            $comment_id = Wo_Secure($_POST['comment_id']);
            $comment = Wo_GetPostComment($comment_id);
            if (!empty($comment)) {

                $reactions_types = array_keys($wo['reactions_types']);
                if (!empty($_POST['reaction']) && in_array($_POST['reaction'], $reactions_types)) {
                    $reaction = Wo_Secure($_POST['reaction']);
                    Wo_AddCommentReactions($comment_id, $reaction);

                    Wo_CanSenEmails();
                    $response_data = array(
                                    'api_status' => 200,
                                    'message' => "comment successfully reacted."
                                );
                }
                elseif (Wo_IsReacted($comment_id, $wo['user']['user_id'],'comment') == true) {
                    Wo_DeleteCommentReactions($comment_id);
                    $response_data = array(
                                    'api_status' => 200,
                                    'message' => "reaction successfully deleted."
                                );
                }
                else{
                    $error_code    = 8;
                    $error_message = 'reaction (POST) is missing.';
                }
            }
            else{
                $error_code    = 7;
                $error_message = 'comment not found.';
            }
        }
        else{
            $error_code    = 6;
            $error_message = 'comment_id can not be empty.';
        }
    }
    if ($_POST['type'] == 'reaction_reply') {
        if (!empty($_POST['reply_id']) && is_numeric($_POST['reply_id']) && $_POST['reply_id'] > 0) {
            $reply_id = Wo_Secure($_POST['reply_id']);
            $reply = Wo_GetCommentReply($reply_id);
            if (!empty($reply)) {

                $reactions_types = array_keys($wo['reactions_types']);
                if (!empty($_POST['reaction']) && in_array($_POST['reaction'], $reactions_types)) {
                    $reaction = Wo_Secure($_POST['reaction']);
                    Wo_AddReplayReactions($wo['user']['id'],$reply_id, $reaction);

                    Wo_CanSenEmails();
                    $response_data = array(
                                    'api_status' => 200,
                                    'message' => "comment successfully reacted."
                                );
                }
                elseif (Wo_IsReacted($reply_id, $wo['user']['user_id'],'replay') == true) {
                    Wo_DeleteReplayReactions($reply_id);
                    $response_data = array(
                                    'api_status' => 200,
                                    'message' => "reaction successfully deleted."
                                );
                }
                else{
                    $error_code    = 8;
                    $error_message = 'reaction (POST) is missing.';
                }
            }
            else{
                $error_code    = 7;
                $error_message = 'comment not found.';
            }
        }
        else{
            $error_code    = 6;
            $error_message = 'reply_id can not be empty.';
        }
    }
    if ($_POST['type'] == 'comment_like') {
        if (!empty($_POST['comment_id']) && is_numeric($_POST['comment_id']) && $_POST['comment_id'] > 0) {
            $comment_id = Wo_Secure($_POST['comment_id']);
            $comment = Wo_GetPostComment($comment_id);
            if (!empty($comment)) {
                if (Wo_AddCommentLikes($_POST['comment_id']) == 'unliked') {
                    $response_data = array(
                                        'api_status' => 200,
                                        'code' => 0
                                    );
                } else {
                    $response_data = array(
                                        'api_status' => 200,
                                        'code' => 1
                                    );
                }
            }
            else{
                $error_code    = 7;
                $error_message = 'comment not found.';
            }
        }
        else{
            $error_code    = 6;
            $error_message = 'comment_id can not be empty.';
        }  
    }

    if ($_POST['type'] == 'comment_dislike') {
        if (!empty($_POST['comment_id']) && is_numeric($_POST['comment_id']) && $_POST['comment_id'] > 0) {
            $comment_id = Wo_Secure($_POST['comment_id']);
            $comment = Wo_GetPostComment($comment_id);
            if (!empty($comment)) {
                if (Wo_AddCommentWonders($_POST['comment_id']) == 'unwonder') {
                    $response_data = array(
                                        'api_status' => 200,
                                        'code' => 0
                                    );
                } else {
                    $response_data = array(
                                        'api_status' => 200,
                                        'code' => 1
                                    );
                }
            }
            else{
                $error_code    = 7;
                $error_message = 'comment not found.';
            }
        }
        else{
            $error_code    = 6;
            $error_message = 'comment_id can not be empty.';
        }  
    }
    if ($_POST['type'] == 'reply_like') {
        if (!empty($_POST['reply_id']) && is_numeric($_POST['reply_id']) && $_POST['reply_id'] > 0) {
            if (Wo_AddCommentReplyLikes($_POST['reply_id'], '') == 'unliked') {
                $response_data = array(
                    'api_status' => 200,
                    'likes' => Wo_CountCommentReplyLikes($_POST['reply_id'])
                );
            } else {
                $response_data = array(
                    'api_status' => 200,
                    'likes' => Wo_CountCommentReplyLikes($_POST['reply_id'])
                );
            }
            //$response_data['dislike'] = 0;
            if ($wo['config']['second_post_button'] == 'dislike') {
                //$response_data['dislike']   = 1;
                $response_data['dislike'] = Wo_CountCommentReplyWonders($_POST['reply_id']);
            }
        }
        else{
            $error_code    = 6;
            $error_message = 'reply_id can not be empty.';
        }
    }
    if ($_POST['type'] == 'reply_dislike') {
        if (!empty($_POST['reply_id']) && is_numeric($_POST['reply_id']) && $_POST['reply_id'] > 0) {
            if (Wo_AddCommentReplyWonders($_POST['reply_id'], '') == 'unwonder') {
                $response_data = array(
                    'status' => 200,
                    'dislike' => Wo_CountCommentReplyWonders($_POST['reply_id'])
                );
            } else {
                $response_data = array(
                    'status' => 200,
                    'dislike' => Wo_CountCommentReplyWonders($_POST['reply_id'])
                );
            }
            if ($wo['config']['second_post_button'] == 'dislike') {
                $response_data['likes'] = Wo_CountCommentReplyLikes($_POST['reply_id']);
            }
        }
        else{
            $error_code    = 6;
            $error_message = 'reply_id can not be empty.';
        }
    }
    if ($_POST['type'] == 'get_comment_likes') {
        if (!empty($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) {
            $id = Wo_Secure($_POST['id']);
            $liked_data = array();
            $likedUsers = Wo_GetPostCommentLikes($id,$limit,$offset);
            if (!empty($likedUsers)) {
                foreach ($likedUsers as $key => $value) {
                    foreach ($non_allowed as $key4 => $value4) {
                      unset($likedUsers[$key][$value4]);
                    }
                }
                $liked_data = $likedUsers;
            }
            $response_data = array(
                'status' => 200,
                'likedUsers' => $liked_data
            );
        }
        else{
            $error_code    = 4;
            $error_message = 'id can not be empty';
        }
    }
    if ($_POST['type'] == 'get_comment_dislikes') {
        if (!empty($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) {
            $id = Wo_Secure($_POST['id']);
            $liked_data = array();
            $likedUsers = Wo_GetPostCommentWonders($id,$limit,$offset);
            if (!empty($likedUsers)) {
                foreach ($likedUsers as $key => $value) {
                    foreach ($non_allowed as $key4 => $value4) {
                      unset($likedUsers[$key][$value4]);
                    }
                }
                $liked_data = $likedUsers;
            }
            $response_data = array(
                'status' => 200,
                'dislikedUsers' => $liked_data
            );
        }
        else{
            $error_code    = 4;
            $error_message = 'id can not be empty';
        }
    }


}
else{
    $error_code    = 4;
    $error_message = 'type can not be empty';
}