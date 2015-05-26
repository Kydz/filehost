<?php
// header('Content-type: text/html; charset=UTF-8');
$uploader = new Upload();
if($_POST['fileContent']){
    $re = $uploader->uploadProxyBinary();
}else{
    $re = $uploader->uploadProxy();    
}
echo json_encode($re);
exit;


class Upload{
    private $_root = 'img';
    private $_url = '';
    private $_tmp = 'temp';

    private $_errorMsg = '';

    private $_file = null;
    private $_fileContent = '';
    private $_fileName = '';
    private $_erxt = '';

    public function uploadProxy(){
        try {
            $this->setFile();
            $this->templizeFile();
            $this->checkExistence();
            $this->saveFile();
            $data = ['error' => 0, 'url' => $this->_url];
        } catch (Exception $e) {
            $data = ['error' => $e->getCode(), 'message' => $e->getMessage()];
        }
        return $data;
    }

    public function uploadProxyBinary(){
        if(!$_POST['fileContent']){
            throw new Exception("no file found", 1);
        }
        $name = $_POST['fileName'];
        $this->_fileContent = $_POST['fileContent'];
        $ext = explode('.', $name);
        $this->_ext = array_pop($ext);
        try{
            $this->checkExistence();
            $this->saveFile();
            $data = ['error' => 0, 'url' => $this->_url];
        } catch (Exception $e){
            $data = ['error' => $e->getCode(), 'message' => $e->getMessage()];
        }
        return $data;
    }

    private function setFile(){
        if(empty($_FILES['imgFile'])){
            throw new Exception("no file found", 1);
        }
        if(!empty($_FILES['imgFile']['error'])){
            switch ($_FILES['imgFile']['error']) { 
                case '1':
                case '2':
                    $this->_errorMsg = 'size too large';
                    break;
                case '3':
                    $this->_errorMsg = 'only part of img is uploaded';
                    break;
                case '4':
                    $this->_errorMsg = 'chose a img file';
                    break;
                case '6':
                    $this->_errorMsg = 'can`t find temp directory';
                    break;
                case '7':
                    $this->_errorMsg = 'fail to write into disc';
                    break;
                case '8':
                    $this->_errorMsg = 'file upload stopped by extension';
                    break;
                case '999':
                default:
                    $this->_errorMsg = 'unkonw error';
                    break;
            }
            throw new Exception($this->_errorMsg, 1);
        }
        $this->_file = $_FILES['imgFile'];
        $this->_file['name'] = $_POST['imgName'];
        return $this;
    }

    private function templizeFile(){
        $this->_ext = array_pop(explode('.', $this->_file['name']));
        //check allowed extensions
        //--todo
        //save file in temp dir
        $tempPath = $this->_tmp.'/'.time().'.'.$this->_ext;
        move_uploaded_file($this->_file['tmp_name'], $tempPath);
        $this->_fileContent = file_get_contents($tempPath);
        unlink($tempPath);
    }

    private function checkExistence(){
        $this->_fileName = md5($this->_fileContent);
        $files = scandir($this->_root);
        if(!in_array($this->_fileName.'.'.$this->_ext, $files)){
            $this->_url = '';
        }else{
            $this->_url = 'http://'.$_SERVER['HTTP_HOST'].'/getImg.php?file='.$this->_fileName.'.'.$this->_ext;
        }
    }

    private function saveFile(){
        if($this->_url) return;
        if(!$this->_fileContent) throw new Exception("no file content found", 1);
        $path = 'saekv://'.$this->_fileName.'.'.$this->_ext;
        if(!file_put_contents($path, $this->_fileContent)) throw new Exception("fail to save file", 1);
        $this->_url = 'http://'.$_SERVER['HTTP_HOST'].'/getImg.php?file='.$this->_fileName.'.'.$this->_ext;
    }
}