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
                        'leave',
                        'add_user',
                        'remove_user',
                        'send',
                        'fetch_messages',
                        'get_list',
                        'accept',
                        'reject',
                        'get_by_id',
                        'get_message_by_id',
                        'search',
                        'join',
                        'destruct_at',
                    );
if (!empty($_POST['type']) && in_array($_POST['type'], $required_fields)) {
    if ($_POST['type'] == 'join') {
        if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
            $error_code    = 7;
            $error_message = 'id must be numeric and greater than 0';
        }
        $group_id = Wo_Secure($_POST['id']);

        $group_tab = Wo_GroupTabData($group_id);
        if (empty($group_tab)) {
            $error_code    = 8;
            $error_message = 'group not found or removed';
        }

        if (empty($error_code)) {
            if (!Wo_IsGChatMemeberExists($group_id, $wo['user']['id'])) {
                @mysqli_query($sqlConnect, "INSERT INTO " . T_GROUP_CHAT_USERS . " (`id`,`user_id`,`group_id`,`active`) VALUES (null,".$wo['user']['id'].",$group_id,'1')");
                $response_data = array(
                    'api_status' => 200,
                    'message_data' => 'joined group successfully'
                );
            }
            else{
                $error_code    = 8;
                $error_message = 'you are already joined this group';
            }
        }


    }
    if ($_POST['type'] == 'search') {
        if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
            $error_code    = 7;
            $error_message = 'id must be numeric and greater than 0';
        }
        elseif (empty($_POST['keyword'])) {
            $error_code    = 8;
            $error_message = 'keyword can not be empty';
        }

        $group_id = Wo_Secure($_POST['id']);
        $keyword = Wo_Secure($_POST['keyword']);

        $sql   = "(
        `text`     LIKE '%$filter_keyword%'
        )";

        $messages = $db->where('group_id',$group_id)->where($sql)->get(T_MESSAGES);

        $messagesData = [];
        foreach ($messages as $key => $value) {

            $message_info = array(
                'group_id' => $group_id,
                'id' => $last_id
            );
            $message_info = Wo_GetGroupMessages($message_info);

            $messagesData[] = messageMarkup($message_info);

            


            
            




        }
        $response_data = array(
                                'api_status' => 200,
                                'data' => $messagesData
                            );


    }
    if ($_POST['type'] == 'create') {

        $required_fields = array(
            'group_name',
            'parts'
        );

        foreach ($required_fields as $key => $value) {
            if (empty($_POST[$value]) && empty($error_code)) {
                $error_code    = 4;
                $error_message = $value . ' (POST) is missing';
            }
        }

        if (empty($error_code) && (strlen($_POST['group_name']) < 4 || strlen($_POST['group_name']) > 25)) {
            $error_code    = 5;
            $error_message = 'group_name must be between 4 and 25 character';
        }

        if (empty($error_code) && isset($_FILES["avatar"])) {
            if (file_exists($_FILES["avatar"]["tmp_name"])) {
                $image = getimagesize($_FILES["avatar"]["tmp_name"]);
                if (!in_array($image[2], array(
                    IMAGETYPE_GIF,
                    IMAGETYPE_JPEG,
                    IMAGETYPE_PNG,
                    IMAGETYPE_BMP
                ))) {
                    $error_code    = 6;
                    $error_message = 'Group avatar must be an image';
                }
            }
        }

        if (empty($error_code)) {
            $users   = explode(',', Wo_Secure($_POST['parts']));
            $users[] = $wo['user']['id'];
            $name    = Wo_Secure($_POST['group_name']);
            $type    = 'group';
            if (!empty($_POST['group_type'])) {
                $type    = Wo_Secure($_POST['group_type']);
            }
            $id      = Wo_CreateGChat($name, $users,$type);
            
            if (isset($_FILES["avatar"]["tmp_name"])) {
                $fileInfo      = array(
                    'file' => $_FILES["avatar"]["tmp_name"],
                    'name' => $_FILES['avatar']['name'],
                    'size' => $_FILES["avatar"]["size"],
                    'type' => $_FILES["avatar"]["type"],
                    'types' => 'jpg,png,bmp,gif,jpeg',
                    'compress' => false,
                    'crop' => array(
                        'width' => 70,
                        'height' => 70
                    )
                );
                $media         = Wo_ShareFile($fileInfo);
                $mediaFilename = $media['filename'];
                @Wo_UpdateGChat($id, array(
                    "avatar" => $mediaFilename
                ));
            }
            if ($id && is_numeric($id)) {
                $group_data = Wo_GetChatGroupData($id);
                foreach ($group_data as $key_g => $group) {
                    foreach ($non_allowed as $key => $value) {
                        unset($group_data[$key_g]['user_data'][$value]);
                    }
                    foreach ($group_data[$key_g]['parts'] as $key1 => $part) {
                        foreach ($non_allowed as $key => $value) {
                            unset($group_data[$key_g]['parts'][$key1][$value]);
                        }
                    }
                }
                $response_data = array(
                    'api_status' => 200,
                    'data' => $group_data
                );
            }
        }
    }

    if ($_POST['type'] == 'delete') {
        if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
            $error_code    = 7;
            $error_message = 'id must be numeric and greater than 0';
        }

        if (empty($error_code)) {
            $result = Wo_DeleteGChat($_POST['id']);
            if ($result === true) {
                $response_data = array(
                    'api_status' => 200,
                    'message_data' => 'group successfully deleted'
                );
            }
            else{
                $error_code    = 8;
                $error_message = 'group not found or removed';
            }
        }
    }

    if ($_POST['type'] == 'destruct_at') {
        if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
            $error_code    = 7;
            $error_message = 'id must be numeric and greater than 0';
        }

        
        if (!empty($_POST['destruct_at']) && !is_numeric($_POST['destruct_at'])) {
            $error_code    = 8;
            $error_message = 'destruct_at must be numeric';
        }

        if (empty($error_code)) {
            $destruct_at = 0;
            if (!empty($_POST['destruct_at'])) {
                $destruct_at = Wo_Secure($_POST['destruct_at']);
            }
            $group_id = Wo_Secure($_POST['id']);
            $group_tab = Wo_GroupTabData($group_id);
            if ($group_tab && is_array($group_tab) && $group_tab['type'] == 'secret' && Wo_IsGChatMemeberExists($group_id, $wo['user']['id'])) {
                $db->where('group_id',$group_id)->update(T_GROUP_CHAT,[
                    'destruct_at' => $destruct_at
                ]);

                $response_data = array(
                    'api_status' => 200,
                    'message' => 'chat updated successfully'
                );
            }
            else{
                $error_code    = 8;
                $error_message = 'chat not found or not secret';
            }
        }
    }

    if ($_POST['type'] == 'edit') {
        if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
            $error_code    = 7;
            $error_message = 'id must be numeric and greater than 0';
        }
        if (empty($_POST['group_name']) && empty($_FILES["avatar"])) {
            $error_code      = 9;
            $error_message   = 'group_name and image can not be empty';
        }
        if (!empty($_POST['group_name']) && (strlen($_POST['group_name']) < 4 || strlen($_POST['group_name']) > 25) ) {
            $error_code      = 5;
            $error_message   = 'group_name must be between 4 and 25 character';
        }
        if (!empty($_FILES["avatar"]) && isset($_FILES["avatar"])) {
            if (file_exists($_FILES["avatar"]["tmp_name"])) {
                $image = getimagesize($_FILES["avatar"]["tmp_name"]);
                if (!in_array($image[2], array(
                    IMAGETYPE_GIF,
                    IMAGETYPE_JPEG,
                    IMAGETYPE_PNG,
                    IMAGETYPE_BMP
                ))) {
                    $error_code    = 6;
                    $error_message = 'Group avatar must be an image';
                }
            }
        }

        if (empty($error_code)) {
            $group_id = $id = Wo_Secure($_POST['id']);
            $group_tab = Wo_GroupTabData($group_id);
            if ($group_tab && is_array($group_tab)) {
                $update_data = array();
                if (!empty($_POST['group_name'])) {
                    $update_data['group_name']    = Wo_Secure($_POST['group_name']);
                }
                if (isset($_FILES["avatar"]["tmp_name"])) {
                    $fileInfo      = array(
                        'file' => $_FILES["avatar"]["tmp_name"],
                        'name' => $_FILES['avatar']['name'],
                        'size' => $_FILES["avatar"]["size"],
                        'type' => $_FILES["avatar"]["type"],
                        'types' => 'jpg,png,bmp,gif,jpeg',
                        'compress' => false,
                        'crop' => array(
                            'width' => 70,
                            'height' => 70
                        )
                    );
                    $media         = Wo_ShareFile($fileInfo);
                    $mediaFilename = $media['filename'];
                    $update_data['avatar']    = $mediaFilename;
                    
                }
                if (!empty($update_data)) {
                    @Wo_UpdateGChat($id, $update_data);
                    $group_data = Wo_GetChatGroupData($id);
                    foreach ($group_data as $key_g => $group) {
                        foreach ($non_allowed as $key => $value) {
                            unset($group_data[$key_g]['user_data'][$value]);
                        }
                        foreach ($group_data[$key_g]['parts'] as $key1 => $part) {
                            foreach ($non_allowed as $key => $value) {
                                unset($group_data[$key_g]['parts'][$key1][$value]);
                            }
                        }
                    }
                    $response_data = array(
                        'api_status' => 200,
                        'data' => $group_data
                    );
                }
            }
            else{
                $error_code    = 8;
                $error_message = 'group not found or removed';
            }
        }
    }

    if ($_POST['type'] == 'leave') {
        if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
            $error_code    = 7;
            $error_message = 'id must be numeric and greater than 0';
        }
        if (empty($error_code)) {
            $id        = Wo_Secure($_POST['id']);
            $group_tab = Wo_GroupTabData($_POST['id']);
            if (!empty($group_tab)) {
                Wo_ExitGChat($id);
                $response_data = array(
                    'api_status' => 200,
                    'message_data' => 'you are successfully leaved this group'
                );
            }
            else{
                $error_code    = 8;
                $error_message = 'group not found or removed';
            }
        }
    }

    if ($_POST['type'] == 'add_user') {
        if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
            $error_code    = 7;
            $error_message = 'id must be numeric and greater than 0';
        }
        if (empty($_POST['parts'])) {
            $error_code    = 4;
            $error_message = 'parts (POST) is missing';
        }
        if (empty($error_code)) {
            $id        = Wo_Secure($_POST['id']);
            $users   = explode(',', Wo_Secure($_POST['parts']));
            $group_tab = Wo_GroupTabData($_POST['id']);
            if (!empty($group_tab)) {
                if ($group_tab['user_id'] == $wo['user']['id']) {
                    foreach ($users as $key => $user) {
                        if (!Wo_IsGChatMemeberExists($id, $user)) {
                            $active = 0;
                            if ($type == 'channel') {
                                $active = 1;
                            }
                            @mysqli_query($sqlConnect, "INSERT INTO " . T_GROUP_CHAT_USERS . " (`id`,`user_id`,`group_id`,`active`) VALUES (null,$user,$id,'".$active."')");
                        }
                    }
                    $response_data = array(
                        'api_status' => 200,
                        'message_data' => 'users successfully joined to group'
                    );
                }
                else{
                    $error_code    = 11;
                    $error_message = 'sorry you are not the group owner';
                }
            }
            else{
                $error_code    = 8;
                $error_message = 'group not found or removed';
            }
        }
    }

    if ($_POST['type'] == 'remove_user') {
        if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
            $error_code    = 7;
            $error_message = 'id must be numeric and greater than 0';
        }
        if (empty($_POST['parts'])) {
            $error_code    = 4;
            $error_message = 'parts (POST) is missing';
        }
        if (empty($error_code)) {
            $id        = Wo_Secure($_POST['id']);
            $users   = explode(',', Wo_Secure($_POST['parts']));
            $group_tab = Wo_GroupTabData($_POST['id']);
            if (!empty($group_tab)) {
                if ($group_tab['user_id'] == $wo['user']['id']) {
                    foreach ($users as $key => $user) {
                        if (Wo_IsGChatMemeberExists($id, $user)) {
                            @mysqli_query($sqlConnect, "DELETE FROM " . T_GROUP_CHAT_USERS . " WHERE `user_id` = {$user} AND `group_id` = {$id}");
                        }
                    }
                    $response_data = array(
                        'api_status' => 200,
                        'message_data' => 'users successfully removed from group'
                    );
                }
                else{
                    $error_code    = 11;
                    $error_message = 'sorry you are not the group owner';
                }
            }
            else{
                $error_code    = 8;
                $error_message = 'group not found or removed';
            }
        }
    }



    if ($_POST['type'] == 'send') {
        if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
            $error_code    = 7;
            $error_message = 'id must be numeric and greater than 0';
        }
        if (empty($_FILES['file']) && empty($_POST['text']) && empty($_POST['image_url']) && empty($_POST['gif']) && empty($_POST['lng']) && empty($_POST['lat'])) {
            $error_code    = 12;
            $error_message = 'text and file and image_url and gif can not be empty';
        }
        $group_tab = Wo_GroupTabData($_POST['id']);
        if (empty($group_tab)) {
            $error_code    = 8;
            $error_message = 'group not found or removed';
        }
        if (!Wo_IsGChatMemeberExists($_POST['id'], $wo['user']['id']) && $group_tab['user_id'] != $wo['user']['id']) {
            $error_code    = 13;
            $error_message = 'sorry you are not a group memeber';

        }
        if ($group_tab['type'] == 'channel' && $wo['user']['id'] != $group_tab['user_id']) {
            $error_code    = 14;
            $error_message = 'you are not the channel owner';
        }
        if (empty($error_code)) {
            $group_id   = Wo_Secure($_POST['id']);
            $mediaFilename = '';
            $mediaName     = '';
            if (isset($_FILES['file']['name'])) {
                $fileInfo      = array(
                    'file' => $_FILES["file"]["tmp_name"],
                    'name' => $_FILES['file']['name'],
                    'size' => $_FILES["file"]["size"],
                    'type' => $_FILES["file"]["type"]
                );
                $media         = Wo_ShareFile($fileInfo);
                $mediaFilename = $media['filename'];
                $mediaName     = $_FILES['file']['name'];
            }
            if (!empty($_POST['image_url'])) {
                $fileend = '_url_image';
                if (!empty($_POST['sticker_id'])) {
                    $fileend =  '_' . Wo_Secure($_POST['sticker_id']);
                }
                $mediaFilename = Wo_ImportImageFromUrl($_POST['image_url'], $fileend);
            }
            $gif = '';
            if (!empty($_POST['gif'])) {
                if (strpos($_POST['gif'], '.mp4') !== false) {
                    $gif = Wo_Secure($_POST['gif']);
                }
            }
            $lng = 0;
            $lat = 0;
            if (!empty($_POST['lng']) && !empty($_POST['lat'])) {
                $lng = Wo_Secure($_POST['lng']);
                $lat = Wo_Secure($_POST['lat']);
            }
            $message_data = array(
                'from_id' => Wo_Secure($wo['user']['user_id']),
                'group_id' => Wo_Secure($group_id),
                'media' => Wo_Secure($mediaFilename),
                'mediaFileName' => Wo_Secure($mediaName),
                'time' => time(),
                'type_two' => (!empty($_POST['contact'])) ? 'contact' : '',
                'text' => '',
                'stickers' => $gif,
                'lng' => $lng,
                'lat' => $lat,
            );
            if (!empty($_POST['text'])) {
                $message_data['text'] = Wo_Secure($_POST['text']);

                $mentions = [];
                $mention_regex = '/@([A-Za-z0-9_]+)/i';
                preg_match_all($mention_regex, $message_data['text'], $matches);
                foreach ($matches[1] as $match) {
                    $match = Wo_Secure($match);
                    $match_user = Wo_UserData(Wo_UserIdFromUsername($match));
                    $match_search = '@' . $match;
                    $match_replace = '@[' . $match_user['user_id'] . ']';
                    if (isset($match_user['user_id'])) {
                        $message_data['text'] = str_replace($match_search, $match_replace, $message_data['text']);
                        $mentions[] = $match_user['user_id'];
                    }
                }

                if (isset($mentions) && is_array($mentions)) {
                    foreach ($mentions as $mention) {
                        $notification_data_array = array(
                            'recipient_id' => $mention,
                            'type' => 'chat_group_mention',
                            'url' => 'index.php?link1=messages'
                        );
                        Wo_RegisterNotification($notification_data_array);
                    }
                }
            }

            $last_id      = Wo_RegisterMessageGroup($message_data);

            if (!empty($last_id)) {
                if ($group_tab['type'] == 'secret') {
                    $db->where('group_id',$group_id)->where('from_id',$wo['user']['id'],'!=')->where('seen',0)->update(T_MESSAGES,[
                        'seen' => time(),
                        'remove_at' => ($group_tab['destruct_at'] > 0 ? (time() + $group_tab['destruct_at']) : 0),
                    ]);

                    $removed_messages = $db->where('group_id',$group_id)->where('remove_at',0,'!=')->where('remove_at',time(),'<')->get(T_MESSAGES);
                    foreach ($removed_messages as $key => $value) {
                        $db->where('id',$value->id)->update(T_MESSAGES,array(
                            'deleted_one' => '1',
                            'deleted_two' => '1',
                        ));
                        Wo_DeleteMessage($value->id,'',$value->from_id);
                    }
                }

                if (!empty($_POST['reply_id']) && is_numeric($_POST['reply_id']) && $_POST['reply_id'] > 0) {
                    $reply_id = Wo_Secure($_POST['reply_id']);
                    $db->where('id',$last_id)->update(T_MESSAGES,array('reply_id' => $reply_id));
                }
                $message_info = array(
                    'group_id' => $group_id,
                    'id' => $last_id
                );
                $message_info = Wo_GetGroupMessages($message_info);
                foreach ($non_allowed as $key => $value) {
                   unset($message_info[0]['messageUser'][$value]);
                }
                if (empty($wo['user']['timezone'])) {
                    $wo['user']['timezone'] = 'UTC';
                }
                $timezone = new DateTimeZone($wo['user']['timezone']);
                $messages = array();

                foreach ($message_info as $key => $message) {
                    $message['time_text'] = Wo_Time_Elapsed_String($message['time']);
                    $message_po           = 'left';
                    if ($message['from_id'] == $wo['user']['user_id']) {
                        $message_po = 'right';
                    }
                    $message['position'] = $message_po;
                    $message['type']     = Wo_GetFilePosition($message['media']);
                    if ($message['type_two'] == 'contact') {
                        $message['type']   = 'contact';
                    }
                    if (!empty($message['lng']) && !empty($message['lat'])) {
                        $message['type']   = 'map';
                    }
                    $message['type']     = $message_po . '_' . $message['type'];
                    $message['file_size'] = 0;
                    if (!empty($message['media'])) {
                        $message['file_size'] = '0MB';
                        if (file_exists($message['file_size'])) {
                            $message['file_size'] = Wo_SizeFormat(filesize($message['media']));
                        }
                        $message['media']     = Wo_GetMedia($message['media']);
                    }
                    if (!empty($message['time'])) {
                        $time_today = time() - 86400;
                        if ($message['time'] < $time_today) {
                            $message['time_text'] = date('m.d.y', $message['time']);
                        } else {
                            $time = new DateTime('now', $timezone);
                            $time->setTimestamp($message['time']);
                            $message['time_text'] = $time->format('H:i');
                        }
                    }
                    $message['message_hash_id'] = '';
                    if (!empty($_POST['message_hash_id'])) {
                        $message['message_hash_id'] = $_POST['message_hash_id'];
                    }

                    if (!empty($message['reply'])) {
                        foreach ($non_allowed as $key => $value) {
                           unset($message['reply']['messageUser'][$value]);
                        }

                        $message['reply']['time_text'] = Wo_Time_Elapsed_String($message['reply']['time']);
                        $message_po           = 'left';
                        if ($message['reply']['from_id'] == $wo['user']['user_id']) {
                            $message_po = 'right';
                        }
                        $message['reply']['position'] = $message_po;
                        $message['reply']['type']     = Wo_GetFilePosition($message['reply']['media']);
                        if ($message['reply']['type_two'] == 'contact') {
                            $message['reply']['type']   = 'contact';
                        }
                        if (!empty($message['reply']['lng']) && !empty($message['reply']['lat'])) {
                            $message['reply']['type']   = 'map';
                        }
                        $message['reply']['type']     = $message_po . '_' . $message['reply']['type'];
                        $message['reply']['file_size'] = 0;
                        if (!empty($message['reply']['media'])) {
                            $message['reply']['file_size'] = '0MB';
                            if (file_exists($message['reply']['file_size'])) {
                                $message['reply']['file_size'] = Wo_SizeFormat(filesize($message['reply']['media']));
                            }
                            $message['reply']['media']     = Wo_GetMedia($message['reply']['media']);
                        }
                        if (!empty($message['reply']['time'])) {
                            $time_today = time() - 86400;
                            if ($message['reply']['time'] < $time_today) {
                                $message['reply']['time_text'] = date('m.d.y', $message['reply']['time']);
                            } else {
                                $time = new DateTime('now', $timezone);
                                $time->setTimestamp($message['reply']['time']);
                                $message['reply']['time_text'] = $time->format('H:i');
                            }
                        }
                        $message['reply']['message_hash_id'] = '';
                        if (!empty($_POST['message_hash_id'])) {
                            $message['reply']['message_hash_id'] = $_POST['message_hash_id'];
                        }
                    }
                    array_push($messages, $message);
                }
                foreach ($messages as $m_id => $value) {
                    foreach ($non_allowed as $key => $value) {
                        unset($messages[$m_id]['user_data'][$value]);
                    }
                }
                if (!empty($messages)) {
                    $response_data = array(
                        'api_status' => 200,
                        'data' => $messages
                    );
                }
            }
        }
    }

    if ($_POST['type'] == 'fetch_messages') {
        if (empty($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id'] < 1) {
            $error_code    = 7;
            $error_message = 'id must be numeric and greater than 0';
        }
        $group_tab = Wo_GroupTabData($_POST['id']);
        if (empty($group_tab)) {
            $error_code    = 8;
            $error_message = 'group not found or removed';
        }
        if (!Wo_IsGChatMemeberExists($_POST['id'], $wo['user']['id']) && $group_tab['user_id'] != $wo['user']['id']) {
            $error_code    = 13;
            $error_message = 'sorry you are not a group memeber';

        }

        if (empty($error_code)) {
            $group_id   = Wo_Secure($_POST['id']);
            $limit             = 20;
            $new               = false;
            $old               = false;
            $offset            = 0;
            if (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0) {
                $limit = $_POST['limit'];
            }
            if (!empty($_POST['after_message_id']) && is_numeric($_POST['after_message_id']) && $_POST['after_message_id'] > 0) {
                $offset = $_POST['after_message_id'];
                $new    = true;
            }
            if (!empty($_POST['before_message_id']) && is_numeric($_POST['before_message_id']) && $_POST['before_message_id'] > 0) {
                $offset = $_POST['before_message_id'];
                $old    = true;
            }

            if ($group_tab['type'] == 'secret') {
                $db->where('group_id',$group_id)->where('from_id',$wo['user']['id'],'!=')->where('seen',0)->update(T_MESSAGES,[
                    'seen' => time(),
                    'remove_at' => ($group_tab['destruct_at'] > 0 ? (time() + $group_tab['destruct_at']) : 0),
                ]);

                $removed_messages = $db->where('group_id',$group_id)->where('remove_at',0,'!=')->where('remove_at',time(),'<')->get(T_MESSAGES);
                foreach ($removed_messages as $key => $value) {
                    $db->where('id',$value->id)->update(T_MESSAGES,array(
                        'deleted_one' => '1',
                        'deleted_two' => '1',
                    ));
                    Wo_DeleteMessage($value->id,'',$value->from_id);
                }
            }

            $messages = Wo_GetGroupMessagesAPP(array('group_id' => $group_id,
                                                                  'limit'    => $limit,
                                                                  'new'      => $new,
                                                                  'old'      => $old,
                                                                  'offset'   => $offset));
            $not_include_status = false;
            $not_include_array = array();
            if (!empty($_POST['not_include'])) {
                $not_include_array = @explode(',', $_POST['not_include']);
                $not_include_status = true;
            }
            $timezone = new DateTimeZone($wo['user']['timezone']);
            $group_messages = array();
            foreach ($messages as $message) {
                $message['text'] = openssl_encrypt($message['text'], "AES-128-ECB", $message['time']);
                $message['org_text'] = $message['text'];
                if ($not_include_status == true) {
                    foreach ($not_include_array as $value) {
                        if (!empty($value)) {
                            $value = Wo_Secure($value);
                            unset($message[$value]);
                        }
                    }
                }
                $message['time_text'] = Wo_Time_Elapsed_String($message['time']);
                $message_po  = 'left';
                if ($message['from_id'] == $wo['user']['id']) {
                    $message_po  = 'right';
                }
                
                $message['position']  = $message_po;
                $message['type']      = Wo_GetFilePosition($message['media']);
                if ($message['type_two'] == 'contact') {
                    $message['type']   = 'contact';
                }
                if (!empty($message['lng']) && !empty($message['lat'])) {
                    $message['type']   = 'map';
                }
                $message['type']     = $message_po . '_' . $message['type'];
                $message['file_size'] = 0;
                if (!empty($message['media'])) {
                    $message['file_size'] = '0MB';
                    if (file_exists($message['file_size'])) {
                        $message['file_size'] = Wo_SizeFormat(filesize($message['media']));
                    }
                    $message['media']     = Wo_GetMedia($message['media']);
                }
                if (!empty($message['time'])) {
                    $time_today  = time() - 86400;
                    if ($message['time'] < $time_today) {
                        $message['time_text'] = date('m.d.y', $message['time']);
                    } else {
                        $time = new DateTime('now', $timezone);
                        $time->setTimestamp($message['time']);
                        $message['time_text'] = $time->format('H:i');
                    }
                }


                if (!empty($message['reply'])) {
                    foreach ($non_allowed as $key => $value) {
                       unset($message['reply']['messageUser'][$value]);
                    }

                    $message['reply']['time_text'] = Wo_Time_Elapsed_String($message['reply']['time']);
                    $message_po  = 'left';
                    if ($message['reply']['from_id'] == $wo['user']['id']) {
                        $message_po  = 'right';
                    }
                    
                    $message['reply']['position']  = $message_po;
                    $message['reply']['type']      = Wo_GetFilePosition($message['reply']['media']);
                    if ($message['reply']['type_two'] == 'contact') {
                        $message['reply']['type']   = 'contact';
                    }
                    if (!empty($message['reply']['lng']) && !empty($message['reply']['lat'])) {
                        $message['reply']['type']   = 'map';
                    }
                    $message['reply']['type']     = $message_po . '_' . $message['reply']['type'];
                    $message['reply']['file_size'] = 0;
                    if (!empty($message['reply']['media'])) {
                        $message['reply']['file_size'] = '0MB';
                        if (file_exists($message['reply']['file_size'])) {
                            $message['reply']['file_size'] = Wo_SizeFormat(filesize($message['reply']['media']));
                        }
                        $message['reply']['media']     = Wo_GetMedia($message['reply']['media']);
                    }
                    if (!empty($message['reply']['time'])) {
                        $time_today  = time() - 86400;
                        if ($message['reply']['time'] < $time_today) {
                            $message['reply']['time_text'] = date('m.d.y', $message['reply']['time']);
                        } else {
                            $time = new DateTime('now', $timezone);
                            $time->setTimestamp($message['reply']['time']);
                            $message['reply']['time_text'] = $time->format('H:i');
                        }
                    }
                }
                array_push($group_messages, $message);
            }
            $send_messages_to_phones = Wo_MessagesPushNotifier();
            $group_tab['messages'] = $group_messages;
            foreach ($group_tab['messages'] as $m_id => $value) {
                foreach ($non_allowed as $key => $value) {
                    if (empty($group_tab['messages'][$m_id]['user_data'])) {
                        $group_tab['messages'][$m_id]['user_data'] = null;
                    }
                    else{
                        unset($group_tab['messages'][$m_id]['user_data'][$value]);
                    }
                    
                }
            }

            $response_data = array(
                                'api_status' => 200,
                                'data' => $group_tab
                            );

        }
    }


    if ($_POST['type'] == 'get_list') {

        $offset = (!empty($_POST['offset']) && is_numeric($_POST['offset']) && $_POST['offset'] > 0 ? Wo_Secure($_POST['offset']) : 0);
        $limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Wo_Secure($_POST['limit']) : 20);

        $groups = Wo_GetGroupsListAPP(array('offset' => $offset , 'limit' => $limit));
        foreach ($groups as $key => $value) {
            if (!empty($groups[$key]['user_data'])) {
                foreach ($non_allowed as $key4 => $value4) {
                  unset($groups[$key]['user_data'][$value4]);
                }
            }
            if (!empty($groups[$key]['parts'])) {
                foreach ($groups[$key]['parts'] as $key3 => $g_user) {
                    if (!empty($g_user)) {
                        foreach ($non_allowed as $key5 => $value5) {
                          unset($groups[$key]['parts'][$key3][$value5]);
                        }
                    }
                }
            }

            if (!empty($groups[$key]['last_message'])) {
                foreach ($groups[$key]['last_message'] as $key3 => $g_user) {
                    foreach ($non_allowed as $key5 => $value5) {
                      unset($groups[$key]['last_message']['user_data'][$value5]);
                    }
                }
                $groups[$key]['last_message']['text'] = openssl_encrypt($groups[$key]['last_message']['text'], "AES-128-ECB", $groups[$key]['last_message']['time']);
            }
            $groups[$key]['mute'] = array('notify' => 'yes',
                               'call_chat' => 'yes',
                               'archive' => 'no',
                               'fav' => 'no',
                               'pin' => 'no');
            $mute = $db->where('user_id',$wo['user']['id'])->where('chat_id',$groups[$key]['chat_id'])->where('type','group')->getOne(T_MUTE);
            if (!empty($mute)) {
                $groups[$key]['mute']['notify'] = $mute->notify;
                $groups[$key]['mute']['call_chat'] = $mute->call_chat;
                $groups[$key]['mute']['archive'] = $mute->archive;
                $groups[$key]['mute']['fav'] = $mute->fav;
                $groups[$key]['mute']['pin'] = $mute->pin;
            }
        }

        $response_data = array(
                                'api_status' => 200,
                                'data' => $groups
                            );
    }

    if ($_POST['type'] == 'accept') {
        if (!empty($_POST['group_id']) && is_numeric($_POST['group_id']) && $_POST['group_id'] > 0) {
            $group_id = Wo_Secure($_POST['group_id']);
            $db->where('user_id',$wo['user']['id'])->where('group_id',$group_id)->update(T_GROUP_CHAT_USERS,array('last_seen' => time(),'active' => '1'));

            $group_chat = Wo_GroupTabData($group_id);

            $notification_data = array(
                'recipient_id' => $group_chat['user_id'],
                'notifier_id' => $wo['user']['id'],
                'group_chat_id' => $group_id,
                'type' => 'accept_group_chat_request',
                'url' => 'index.php?link1=timeline&u=' . $wo['user']['username']
            );
            Wo_RegisterNotification($notification_data);

            $response_data = array(
                                    'api_status' => 200,
                                    'message_data' => 'Request successfully accepted'
                                );
        }
        else{
            $error_code    = 8;
            $error_message = 'group_id can not be empty';
        }
    }

    if ($_POST['type'] == 'reject') {
        if (!empty($_POST['group_id']) && is_numeric($_POST['group_id']) && $_POST['group_id'] > 0) {
            $group_id = Wo_Secure($_POST['group_id']);

            $db->where('user_id',$wo['user']['id'])->where('group_id',$group_id)->delete(T_GROUP_CHAT_USERS);

            $group_chat = Wo_GroupTabData($group_id);

            $notification_data = array(
                'recipient_id' => $group_chat['user_id'],
                'notifier_id' => $wo['user']['id'],
                'group_chat_id' => $group_id,
                'type' => 'declined_group_chat_request',
                'url' => 'index.php?link1=timeline&u=' . $wo['user']['username']
            );
            Wo_RegisterNotification($notification_data);

            $response_data = array(
                                    'api_status' => 200,
                                    'message_data' => 'Request successfully rejected'
                                );

        }
        else{
            $error_code    = 8;
            $error_message = 'group_id can not be empty';
        }

    }
    if ($_POST['type'] == 'get_by_id') {
        if (!empty($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) {
            $id = Wo_Secure($_POST['id']);
            $group_data = Wo_GetChatGroupData($id);
            foreach ($group_data as $key_g => $group) {
                foreach ($non_allowed as $key => $value) {
                    unset($group_data[$key_g]['user_data'][$value]);
                }
                foreach ($group_data[$key_g]['parts'] as $key1 => $part) {
                    foreach ($non_allowed as $key => $value) {
                        unset($group_data[$key_g]['parts'][$key1][$value]);
                    }
                }
            }
            $response_data = array(
                'api_status' => 200,
                'data' => $group_data
            );
        }
        else{
            $error_code    = 4;
            $error_message = 'id can not be empty';
        }
    }
    if ($_POST['type'] == 'get_message_by_id') {
        if (!empty($_POST['group_id']) && is_numeric($_POST['group_id']) && $_POST['group_id'] > 0 && !empty($_POST['message_id']) && is_numeric($_POST['message_id']) && $_POST['message_id'] > 0) {
            $last_id = Wo_Secure($_POST['message_id']);
            $group_id = Wo_Secure($_POST['group_id']);
            if (!empty($last_id)) {
                if (!empty($_POST['reply_id']) && is_numeric($_POST['reply_id']) && $_POST['reply_id'] > 0) {
                    $reply_id = Wo_Secure($_POST['reply_id']);
                    $db->where('id',$last_id)->update(T_MESSAGES,array('reply_id' => $reply_id));
                }
                $message_info = array(
                    'group_id' => $group_id,
                    'id' => $last_id
                );
                $message_info = Wo_GetGroupMessages($message_info);
                foreach ($non_allowed as $key => $value) {
                   unset($message_info[0]['messageUser'][$value]);
                }
                if (empty($wo['user']['timezone'])) {
                    $wo['user']['timezone'] = 'UTC';
                }
                $timezone = new DateTimeZone($wo['user']['timezone']);
                $messages = array();

                foreach ($message_info as $key => $message) {
                    $message['time_text'] = Wo_Time_Elapsed_String($message['time']);
                    $message_po           = 'left';
                    if ($message['from_id'] == $wo['user']['user_id']) {
                        $message_po = 'right';
                    }
                    $message['position'] = $message_po;
                    $message['type']     = Wo_GetFilePosition($message['media']);
                    if ($message['type_two'] == 'contact') {
                        $message['type']   = 'contact';
                    }
                    if (!empty($message['lng']) && !empty($message['lat'])) {
                        $message['type']   = 'map';
                    }
                    $message['type']     = $message_po . '_' . $message['type'];
                    $message['file_size'] = 0;
                    if (!empty($message['media'])) {
                        $message['file_size'] = '0MB';
                        if (file_exists($message['file_size'])) {
                            $message['file_size'] = Wo_SizeFormat(filesize($message['media']));
                        }
                        $message['media']     = Wo_GetMedia($message['media']);
                    }
                    if (!empty($message['time'])) {
                        $time_today = time() - 86400;
                        if ($message['time'] < $time_today) {
                            $message['time_text'] = date('m.d.y', $message['time']);
                        } else {
                            $time = new DateTime('now', $timezone);
                            $time->setTimestamp($message['time']);
                            $message['time_text'] = $time->format('H:i');
                        }
                    }
                    $message['message_hash_id'] = '';
                    if (!empty($_POST['message_hash_id'])) {
                        $message['message_hash_id'] = $_POST['message_hash_id'];
                    }

                    if (!empty($message['reply'])) {
                        foreach ($non_allowed as $key => $value) {
                           unset($message['reply']['messageUser'][$value]);
                        }

                        $message['reply']['time_text'] = Wo_Time_Elapsed_String($message['reply']['time']);
                        $message_po           = 'left';
                        if ($message['reply']['from_id'] == $wo['user']['user_id']) {
                            $message_po = 'right';
                        }
                        $message['reply']['position'] = $message_po;
                        $message['reply']['type']     = Wo_GetFilePosition($message['reply']['media']);
                        if ($message['reply']['type_two'] == 'contact') {
                            $message['reply']['type']   = 'contact';
                        }
                        if (!empty($message['reply']['lng']) && !empty($message['reply']['lat'])) {
                            $message['reply']['type']   = 'map';
                        }
                        $message['reply']['type']     = $message_po . '_' . $message['reply']['type'];
                        $message['reply']['file_size'] = 0;
                        if (!empty($message['reply']['media'])) {
                            $message['reply']['file_size'] = '0MB';
                            if (file_exists($message['reply']['file_size'])) {
                                $message['reply']['file_size'] = Wo_SizeFormat(filesize($message['reply']['media']));
                            }
                            $message['reply']['media']     = Wo_GetMedia($message['reply']['media']);
                        }
                        if (!empty($message['reply']['time'])) {
                            $time_today = time() - 86400;
                            if ($message['reply']['time'] < $time_today) {
                                $message['reply']['time_text'] = date('m.d.y', $message['reply']['time']);
                            } else {
                                $time = new DateTime('now', $timezone);
                                $time->setTimestamp($message['reply']['time']);
                                $message['reply']['time_text'] = $time->format('H:i');
                            }
                        }
                        $message['reply']['message_hash_id'] = '';
                        if (!empty($_POST['message_hash_id'])) {
                            $message['reply']['message_hash_id'] = $_POST['message_hash_id'];
                        }
                    }
                    $message['reaction'] = Wo_GetPostReactionsTypes($message['id'], 'message');
                    array_push($messages, $message);
                }
                foreach ($messages as $m_id => $value) {
                    foreach ($non_allowed as $key => $value) {
                        unset($messages[$m_id]['user_data'][$value]);
                    }
                }
                if (!empty($messages)) {
                    $response_data = array(
                        'api_status' => 200,
                        'data' => $messages
                    );
                }
            }
        }
        else{
            $error_code    = 5;
            $error_message = 'group_id and message_id can not be empty';
        }
    }
}
else{
    $error_code    = 4;
    $error_message = 'type can not be empty';
}