<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/13
 * Time: 14:49
 */
 
//百度翻译接口地址
define("BAIDU_FANYI_URL", "http://api.fanyi.baidu.com/api/trans/vip/translate");
//APPID
define("BAIDU_FANYI_APP_ID", "20180523000164602");
//密钥
define("BAIDU_FANYI_KEY", "yGWJ2O2hJvlNZZ2E6uW6");
//KEY值最大长度
define("KEY_MAX_LENGTH", 30);
//备份
define("BACKUP", true);
//防止过渡频繁请求导致失败的问题、每次请求间隔时间(秒、支持0.xx秒、建议0.05)
define("ONE_REQUEST_SLEEP_TIME", 0.05);
//自动翻译时文件格式编码
define('TEXT_CODE', 'utf-8');   /* utf-8 | GBK */

//需要写入到的文件(支持php和json)
define('LANG_FILES', json_encode(       /*json | php*/
    /*
     [ 
        key => file  ---> key代表是需要将前端输入的中文将要被转换成(例)英文(en)
                          以下只需配置'转换后'的语言需要写入对应文件列表
                          文件后缀需一致
                          自动读取并展示到前端
                          至少提供zh和en
     ]
     * */
    [
//        'zh' => __DIR__ . '/../test/a.json',
//        'en' => __DIR__ . '/../test/g.json',
//        'kor' => __DIR__ . '/../test/z.json',

        'zh' => __DIR__ . '/../test/123.php',
        'en' => __DIR__ . '/../test/456.php',
        'th' => __DIR__ . '/../test/789.php',

    ]
/*
 * // 支持
    zh	中文
    en	英语
    yue	粤语
    wyw	文言文
    jp	日语
    kor	韩语
    fra	法语
    spa	西班牙语
    th	泰语
    ara	阿拉伯语
    ru	俄语
    pt	葡萄牙语
    de	德语
    it	意大利语
    el	希腊语
    nl	荷兰语
    pl	波兰语
    bul	保加利亚语
    est	爱沙尼亚语
    dan	丹麦语
    fin	芬兰语
    cs	捷克语
    rom	罗马尼亚语
    slo	斯洛文尼亚语
    swe	瑞典语
    hu	匈牙利语
    cht	繁体中文
    vie	越南语

* */
));
