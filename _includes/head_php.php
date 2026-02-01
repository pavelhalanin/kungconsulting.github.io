<?php

try {
    $HOME = strlen($_SERVER['DOCUMENT_ROOT']) != 0 ? $_SERVER['DOCUMENT_ROOT'] : "";
    include_once "$HOME/php/VisitCount.php";
}
catch(Throwable $exception) {

}

?>
