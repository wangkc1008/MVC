<?php
namespace controller;

use framework\UploadFile;

class IndexController extends Controller
{
    public function index()
    {
        $this->display('index/upload.html');
    }

    public function demo()
    {
        echo '这是demo';
    }

    public function test() {
        $title = 'hello';
        $data = ['hello', 'huhaha'];

        $this->assign('title', $title);
        $this->assign('data', $data);
        $this->assign('test', 'wangkc');

        $this->display('index/test.html');
    }

    public function upload()
    {
        $config = $GLOBALS['config'];
        if ($_FILES['haha']) {
            $file = new UploadFile(['path' => $config['upload_path']]);
            $file->upload('haha');
        }
    }
}