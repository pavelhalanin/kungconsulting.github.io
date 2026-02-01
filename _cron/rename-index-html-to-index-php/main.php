<?php

function renameIndexFiles($dir) {
    if (!is_dir($dir)) {
        echo "Директория не существует: $dir\n";
        return;
    }

    // Открываем директорию
    if ($handle = opendir($dir)) {
        // Перебираем все элементы директории
        while (false !== ($entry = readdir($handle))) {
            // Пропускаем текущую и родительскую директории
            if ($entry == "." || $entry == "..") {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                renameIndexFiles($path);
            }
            elseif ($entry == "index.html") {
                $newPath = $dir . DIRECTORY_SEPARATOR . "index.php";
                if (rename($path, $newPath)) {
                    echo "Переименовано: $path -> $newPath\n";
                } else {
                    echo "Ошибка при переименовании: $path\n";
                }
            }
            elseif ($entry == "404.html") {
                $newPath = $dir . DIRECTORY_SEPARATOR . "404.php";
                if (rename($path, $newPath)) {
                    echo "Переименовано: $path -> $newPath\n";
                } else {
                    echo "Ошибка при переименовании: $path\n";
                }
            }
        }
        closedir($handle);
    }
}

$rootDirectory = './../../_site/';
renameIndexFiles($rootDirectory);

echo "Обработка завершена.\n";
