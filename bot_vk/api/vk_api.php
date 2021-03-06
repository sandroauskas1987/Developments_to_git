<?php

define('VK_API_VERSION', '5.92'); //Используемая версия API
define('VK_API_ENDPOINT', 'https://api.vk.com/method/');

function vkApi_messagesSend($peer_id, $message,$buttons,$coordinate=array()) {
    $ar= array(
        'peer_id'    => $peer_id,
        'message'    => $message,
        'random_id'  => rand(),
        'keyboard'   => json_encode($buttons,JSON_UNESCAPED_UNICODE),
    );
    if(count($coordinate)==2){
        $ar['lat']=$coordinate['lat'];
        $ar['long']=$coordinate['long'];
    }
  return _vkApi_call('messages.send', $ar);
}

function vkApi_usersGet($user_id) {
  return _vkApi_call('users.get', array(
    'user_id'            => $user_id,
    'is_closed  '        =>'false',
    'can_access_closed'  =>'true',
  ));
}

function vkApi_photosGetMessagesUploadServer($peer_id) {
  return _vkApi_call('photos.getMessagesUploadServer', array(
    'peer_id' => $peer_id,
  ));
}

function vkApi_photosSaveMessagesPhoto($photo, $server, $hash) {
  return _vkApi_call('photos.saveMessagesPhoto', array(
    'photo'  => $photo,
    'server' => $server,
    'hash'   => $hash,
  ));
}

function vkApi_docsGetMessagesUploadServer($peer_id, $type) {
  return _vkApi_call('docs.getMessagesUploadServer', array(
    'peer_id' => $peer_id,
    'type'    => $type,
  ));
}

function vkApi_docsSave($file, $title) {
  return _vkApi_call('docs.save', array(
    'file'  => $file,
    'title' => $title,
  ));
}

function _vkApi_call($method, $params = array()) {
  $params['access_token'] = VK_API_ACCESS_TOKEN;
  $params['v'] = VK_API_VERSION;
  //$query = http_build_query($params);
  $url = VK_API_ENDPOINT.$method;

  $curl = curl_init($url);
  curl_setopt_array( $curl, array(
        CURLOPT_POST    => TRUE,            // это именно POST запрос!
        CURLOPT_RETURNTRANSFER  => TRUE,    // вернуть ответ ВК в переменную
        CURLOPT_SSL_VERIFYPEER  => FALSE,   // не проверять https сертификаты
        CURLOPT_SSL_VERIFYHOST  => FALSE,
        CURLOPT_POSTFIELDS      => $params,   // здесь параметры запроса:
        CURLOPT_URL             => $url,    // веб адрес запроса
  ));

  $json = curl_exec($curl);
  $error = curl_error($curl);
  if ($error) {
    log_error($error);
    throw new Exception("Failed {$method} request");
  }

  curl_close($curl);

  $response = json_decode($json, true);
  if (!$response || !isset($response['response'])) {
    log_error($json);
    throw new Exception("Invalid response for {$method} request");
  }

  return $response['response'];
}

function vkApi_upload($url, $file_name) {
  if (!file_exists($file_name)) {
    throw new Exception('File not found: '.$file_name);
  }

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CURLfile($file_name)));
  $json = curl_exec($curl);
  $error = curl_error($curl);
  if ($error) {
    log_error($error);
    throw new Exception("Failed {$url} request");
  }

  curl_close($curl);

  $response = json_decode($json, true);
  if (!$response) {
    throw new Exception("Invalid response for {$url} request");
  }

  return $response;
}
