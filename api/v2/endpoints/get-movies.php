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
$products = array();

$options['limit'] = (!empty($_POST['limit'])) ? (int) $_POST['limit'] : 35;
$options['id'] = (!empty($_POST['id'])) ? (int) $_POST['id'] : 0;
$options['offset'] = (!empty($_POST['offset'])) ? (int) $_POST['offset'] : false;
$options['genre'] = (!empty($_POST['genre'])) ? $_POST['genre'] : false;
$options['country'] = (!empty($_POST['country'])) ? $_POST['country'] : false;

$movies = Wo_GetMovies($options);

$response_data = array(
    'api_status' => 200,
    'movies' => $movies
);