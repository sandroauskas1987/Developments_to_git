<?php
    // проверяем если пришло сообщение
    if (array_key_exists('message', $data)) {
        //если пришла команда /start
        if ($data['message']['text'] == "/start") {
            $this->sendMessage($chat_id, "Приветствую! Загрузите картинку.");
        } elseif (array_key_exists('photo', $data['message'])) {
            // если пришла картинка то сохраняем ее у себя
            $text = $this->getPhoto($data['message']['photo'])
                ? "Спасибо! Можете еще загрузить мне понравилось их сохранять."
                : "Что-то пошло не так, попробуйте еще раз";
            // отправляем сообщение о результате   
            $this->sendMessage($chat_id, $text);
        } else {
            // если пришло что-то другое
            $this->sendMessage($chat_id, "Не понимаю команду! Просто загрузите картинку.");
        }
    }

//https://api.telegram.org/bot540574772:AAGMxv6Z5WomzZoyjCIGKUXTT2qzZHruwmw/getfile?file_id=AgADAgAD06kxGwtWGEjcLcbcNXX2BjXHRg4ABANhnozkiJ9AmmMCAAEC
//{"ok":true,"result":{"file_id":"AgADAgAD06kxGwtWGEjcLcbcNXX2BjXHRg4ABANhnozkiJ9AmmMCAAEC","file_size":63833,"file_path":"photos/file_28.jpg"}}
//https://api.telegram.org/file/bot540574772:AAGMxv6Z5WomzZoyjCIGKUXTT2qzZHruwmw/photos/file_28.jpg



    // общая функция загрузки картинки
    function getPhoto($data)
    {
    	// берем последнюю картинку в массиве
        $file_id = $data[count($data) - 1]['file_id'];
        // получаем file_path
        $file_path = $this->getPhotoPath($file_id);
        // возвращаем результат загрузки фото
        return $this->copyPhoto($file_path);
    }

    // функция получения метонахождения файла
     function getPhotoPath($file_id) {
    	// получаем объект File
        $array = json_decode($this->requestToTelegram(['file_id' => $file_id], "getFile"), TRUE);
        // возвращаем file_path
        return  $array['result']['file_path'];
    }

    // копируем фото к себе
     function copyPhoto($file_path) {
    	// ссылка на файл в телеграме
        $file_from_tgrm = "https://api.telegram.org/file/bot".$this->botToken."/".$file_path;
        // достаем расширение файла
        $ext =  end(explode(".", $file_path));
        // назначаем свое имя здесь время_в_секундах.расширение_файла
        $name_our_new_file = time().".".$ext;
        return copy($file_from_tgrm, "img/".$name_our_new_file);
    };
