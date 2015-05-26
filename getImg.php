<?php
$name = $_GET['file'];
if(!$name){
    $contentType = 'text/html';
    $content = '';
}else{
    $content = file_get_contents('saekv://'.$name);
    $ext = explode('.', $name);
    $ext = array_pop($ext);
    $ext = strtolower($ext);
    switch ($ext) {
        case 'jpe';
        case 'jpg':
        case 'jpeg':
            $contentType = 'image/jpeg';
            break;
        case 'png':
            $contentType = 'image/png';
            break;
        case 'gif':
            $contentType = 'image/gif';
            break;
        case 'pdf':
            $contentType = 'application/pdf';
            break;
        default:
            $contentType = 'text/html';
            break;
    }
}
header('Content-Type:'.$contentType);
echo $content;
exit();