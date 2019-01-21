<?php

/**
 * Created by PhpStorm.
 * User: longjinwen
 * Date: 2018/5/23
 * Time: 16:54
 */
class Language {
    private $specialStr = [
        "\r\n", "\r", "\n", '"', ':', '?', '？', '.', ',', '，', '!', '！', '。', '-', '=', ' ', '/', '$', '%', '^', '&', '*', '(', ')', '+', '@', '#', '$', '%', '\'', '\\',
    ];
    private $translateLang = [];
    private $fileType = '';
    private $lang = [];
    private $maxLength = KEY_MAX_LENGTH;
    private $autoDir = '';
    private $backups = BACKUP;

    public function __construct() {
        $this->translateLang = json_decode(LANG_FILES, true);

        $this->lang = array_keys($this->translateLang);
        //获取文件后缀
        foreach ($this->translateLang as $k => $fileName) {
            $this->fileType = explode('.', $this->translateLang[$k])[count(explode('.', $this->translateLang[$k])) - 1];
            break;
        }

        $this->isFileExistAndType();
    }

    public function getLang() {
        $data = [];
        foreach ($this->translateLang as $l => $item) {
            switch ($this->fileType) {
                case 'php':
                    $tmp = $this->getDataByFile($this->translateLang[$l]);
                    if (!is_array($tmp)) {
                        $tmp = [];
                    }
                    $data[$l] = array_reverse($tmp);
                    break;
                case 'json':
                    $tmp = $this->getDataByFile($this->translateLang[$l]);
                    $data[$l] = array_reverse($tmp);
                    break;
            }
        }
        return $data;
    }

    public function getLast() {
        $data = $this->getLastContents();
        $type = isset($_POST['auto']) ? 'auto' : 'manual';
        return [
            'data' => $this->autoDir && $type === 'auto' ? $this->autoDir : $data[$type . 'TranslateDirPath'],
            'status' => 1
        ];
    }

    public function translate() {
        if (!isset($_POST['input']) || empty($_POST['input'])) {
            return [
                'msg' => '输入不能为空',
                'status' => 2
            ];
        }
        if (!isset($_POST['last'])) {
            $this->setLastContents(['manualTranslateDirPath' => trim($_POST['input'])]);
        }
        $input = array_filter(explode(',', trim($_POST['input'], ',')));

        $zhData = $this->getDataByFile($this->translateLang['zh']);

        $dataAll = [];
        $subscript = [];
        foreach ($this->lang as $l) {
            foreach ($input as $k => $value) {
                $this->sleep(ONE_REQUEST_SLEEP_TIME);
                if (in_array($value, $zhData)) {
                    continue;
                };
                if ($l !== 'zh') {

                    try {
                        $data = \TransApi::translate(trim($value), 'zh', $l);
                    } catch (\Exception $exception) {
                        echo 'catch：' . $exception->getMessage();
                        exit();
                    }

                    if (isset($data['error_code'])) {
                        echo
                            '错误代码：' . $data['error_code'];
                        exit();
                    }
                    $data = $data['trans_result'][0];
                    if ($l === 'en') {
                        $_tmp = $this->interceptNum(lcfirst($this->trimSpecialStr(ucwords(trim($data['dst'])))));
                        //如果是英文，将下标存起来,拼上前缀
                        $subscript[$k] = trim($_POST['prefix']) . $_tmp;
                    }
                    $dataAll[$l][$k] = $data['dst'];
                } else {
                    $dataAll[$l][$k] = trim($value);
                }
            }
        }

        $assembleData = $this->assemble($dataAll, $subscript);
        $this->writeData($assembleData);

        return [
            'msg' => '写入成功',
            'status' => 1
        ];

    }

    private function writeData($assembleData) {
        foreach ($assembleData as $l => $data) {
            switch ($this->fileType) {
                case 'php':
                    $old = $this->getDataByFile($this->translateLang[$l]);
                    $this->backups($l, $this->translateLang[$l]);
                    if (!is_array($old)) {
                        $old = [];
                    }
                    $data = var_export(array_merge($old, $data), true);
                    file_put_contents($this->translateLang[$l], "<?php return " . $data . ";");
                    break;
                case 'json':
                    $old = $this->getDataByFile($this->translateLang[$l]);
                    $this->backups($l, $this->translateLang[$l]);
                    $data = array_merge($old, $data);
                    file_put_contents($this->translateLang[$l], json_encode($data, JSON_UNESCAPED_UNICODE));
                    break;
            }
        }
    }

    public function resetInput() {
        if (isset($_POST['auto'])) {
            $this->setLastContents(['autoTranslateDirPath' => '']);
        } else {
            $this->setLastContents(['manualTranslateDirPath' => '']);
        }
        return ['status' => 1];
    }

    //根据路径返回数据
    private function getDataByFile($file) {
        $data = [];
        switch ($this->fileType) {
            case 'php':
                $data = require $file;
                if (!is_array($data)) {
                    $data = [];
                }
                break;
            case 'json':
                $data = file_get_contents($file);
                $data = json_decode($data, true);
                if (!$data) {
                    $data = [];
                }
                break;
        }
        return $data;
    }

    //组装数据
    private function assemble($dataAll, $subscript) {
        $newData = [];
        foreach ($dataAll as $langKey => $langData) {
            foreach ($langData as $k => $data) {
                $newData[$langKey][$subscript[$k]] = $data;
            }
        }
        return $newData;
    }


    //去掉特殊字符
    private function trimSpecialStr($str) {
        $str = str_replace($this->specialStr, "", $str);
        return $str;
    }

    //如果超过指定长度截取
    private function interceptNum($str) {
        if (strlen($str) > $this->maxLength) {
            return substr($str, 0, $this->maxLength);
        }
        return $str;
    }

    //判断文件是否存在和后缀是否一致
    private function isFileExistAndType() {
        if(!isset($this->translateLang['zh']) || !isset($this->translateLang['en'])){
            exit('至少提供"zh"和"en"两种语言');
        }

        foreach ($this->translateLang as $l => $file) {
            if (!file_exists($file)) {
                exit('文件：' . $file . ' 不存在');
            }
            $fileType = explode('.', $this->translateLang[$l])[count(explode('.', $this->translateLang[$l])) - 1];
            if ($fileType !== $this->fileType) {
                exit('所有文件后缀不一致');
            }
        }
    }

    private function getTimeToMs() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }

    //备份
    private function backups($lang, $file) {
        if (!$this->backups) return '';
        try {
            $date = date('Y-m-d');
            $dir = __DIR__ . '/../../backups/' . $date;
            $backupsFileName = date('H.i.s__') . $lang . '___' . $this->getTimeToMs();
            if (!is_dir($dir)) {
                mkdir($dir);
                $this->sleep(2);
            }
            copy($file, $dir . '/' . $backupsFileName . '.' . $this->fileType);
        } catch (\Exception $exception) {
        }
    }


    public function autoTranslate() {
        if (!isset($_POST['dirPath']) && empty(trim($_POST['dirPath']))) {
            return ['msg' => '输入不能为空', 'status' => 2];
        }
        if (!is_dir(trim($_POST['dirPath']))) {
            return ['msg' => '文件夹不存在', 'status' => 2];
        }
        $files = [];
        $data = [];
        $path = trim($_POST['dirPath']);
        $this->readAllDirFile($path, $files);
        $this->setLastContents(['autoTranslateDirPath' => $path]);
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            if (TEXT_CODE !== 'utf-8') {
                $contents = iconv(TEXT_CODE, "utf-8//IGNORE", $contents);
            }
            if (preg_match_all("/([\x{4e00}-\x{9fa5}]+)/u", $contents, $match)) {
                $data = array_merge($data, $match[0]);
            }
        }
        $data = array_unique($data);
        $newData = [];
        foreach ($data as $datum) {
            $newData[] = $datum;
        }
        return [
            'path' => $path,
            'count' => count($data),
            'status' => 1,
            'limit' => [
                'start' => 0,
                'end' => count($data) > 4 ? 4 : count($data),   //一次条数
                'data' => $newData
            ],
        ];
    }

    private function readAllDirFile($dir, &$result) {
        $d = scandir($dir);
        foreach ($d as $item) {
            if (in_array($item, ['.', '..'])) continue;
            if (is_dir($dir . '/' . $item)) {
                $this->readAllDirFile($dir . '/' . $item, $result);
            } else {

                $type = explode(',', trim($_POST['file_type']) ?: '*');
                $fileType = explode('.', $item)[count(explode('.', $item)) - 1];
                if (in_array($fileType, $type) || $type[0] === '*') {
                    array_push($result, $dir . '/' . $item);
                }
            }
        }
    }

    private function sleep($s = 0.1) {

        $t = microtime(true);
        while (true) {
            if (round(microtime(true) - $t, 3) > $s) {
                break;
            }
        }
    }

    private function getLastContents() {
        return json_decode(file_get_contents(__DIR__ . '/last.json'), true);
    }

    private function setLastContents($appendData) {
        $data = json_decode(file_get_contents(__DIR__ . '/last.json'), true);
        return file_put_contents(__DIR__ . '/last.json', json_encode(array_merge($data, $appendData)));
    }

}