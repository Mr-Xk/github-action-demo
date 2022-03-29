<?php
ini_set('date.timezone', 'Asia/Shanghai');

/**
 * 获取每日一句
 * @return string
 */
function get_one_words()
{
    $channel = random_int(1, 4);
    switch ($channel) {
        case 1: // 彩虹屁
            return file_get_contents('https://chp.shadiao.app/api.php');
        case 2: // 土味情话
            return file_get_contents('https://api.lovelive.tools/api/SweetNothings');
        case 3: // 格言信息
            return json_decode(file_get_contents('http://open.iciba.com/dsapi'), true)['note'];
        case 4: // 一言
            return json_decode(file_get_contents('https://v1.hitokoto.cn/'), true)['hitokoto'];
        default:
            return 'API挂了';
    }
}

/**
 * 获取天气
 * 
 * 教程：https://www.sojson.com/blog/305.html
 * 
 * 旧api：http://wttr.in/Shanghai?format=3
 * 
 * @return string
 */
function get_weather()
{
    // 101020100:上海
    $data = json_decode(file_get_contents('http://t.weather.sojson.com/api/weather/city/101020100'), true);
    if ($data['status'] != 200) {
        return $data['message'] ?? 'API挂了';
    }

    // 这个天气的接口更新不及时，有时候当天1点的时候，还是昨天的天气信息，如果天气不一致，则取下一天(今天)的数据
    $weather_data = $data['data']['forecast'][0];
    $is_tomorrow = (int)date('H') >= 20;
    $date = '今日';
    if ($is_tomorrow || $weather_data['ymd'] != date('Y-m-d')) {
        $weather_data = $data['data']['forecast'][1];
        $date = '明日';
    }

    // 格式化数据
    /**
     *{
     *  "date": "05",
     *  "high": "高温 11℃",
     *  "low": "低温 6℃",
     *  "ymd": "2022-03-05",
     *  "week": "星期六",
     *  "sunrise": "06:17",
     *  "sunset": "17:55",
     *  "aqi": 59,
     *  "fx": "东北风",
     *  "fl": "3级",
     *  "type": "晴",
     *  "notice": "愿你拥有比阳光明媚的心情"
     *}
     *
     *2022-03-05,星期六 上海市
     *【今日天气】晴
     *【今日气温】低温 6℃ 高温 11℃
     *【今日风速】东北风3级
     *【出行提醒】愿你拥有比阳光明媚的心情
     */
    $format = "%s,%s %s【{$date}天气】%s【{$date}气温】%s %s【{$date}风速】%s【出行提醒】%s";
    return sprintf(
        $format,
        $weather_data['ymd'],
        $weather_data['week'],
        $data['cityInfo']['city'] . PHP_EOL,
        $weather_data['type'] . PHP_EOL,
        $weather_data['low'],
        $weather_data['high'] . PHP_EOL,
        $weather_data['fx'] . $weather_data['fl'] . PHP_EOL,
        $weather_data['notice']
    );
}

// 一句话
$one_words = get_one_words();
// 天气
$weather = get_weather();
// 输出文件
file_put_contents('email.html', $one_words . PHP_EOL . PHP_EOL . $weather);