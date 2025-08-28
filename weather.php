<?php
/*$apiKey = trim(explode('=', trim(file_get_contents(__DIR__.'/.env')), 2)[1]);
$cities = ['Saint Petersburg', 'Moscow', 'Volgograd', 'Archangelsk', 'Zvenigovo',
           'London', 'Khabarovsk', 'Magadan', 'Paris', 'Ekaterinburg',
        ];

function get_weather($city, $apiKey){
    $params = ['q'=> $city,'appid' => $apiKey, 'units' => 'metric', 'lang' =>'ru'];
    $url = "http://api.openweathermap.org/data/2.5/weather?" . http_build_query($params);
    $data = file_get_contents($url);
    $json = json_decode($data, true); 
    $weather =['city'=> $json['name'],
            'temperature'=> $json['main']['temp'],
            'feels'=>$json['main']['feels_like'],
            'humidity'=> $json['main']['humidity'],
            'pressure'=> $json['main']['pressure'],
            'description'=> $json['weather'][0]['description'],
            'wind_speed'=> $json['wind']['speed'], 
        ];
    return $weather;
} 

$weatherToDownload = [];
foreach ($cities as $city){
    $weather = get_weather($city, $apiKey);
    array_push($weatherToDownload, $weather);
}
function generate_file($weatherToDownload){
    header("Content-Type: text/csv; charset=UTF-8");
    header('Content-Disposition: attachment; filename="weather' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output,[
        'Город', 
        'Температура', 
        'Ощущается', 
        'Влажность', 
        'Давление', 
        'Описание', 
        'Ветер'
    ], ',', '"', "\\");
    foreach ($weatherToDownload as $temp) {
        fputcsv($output, [
            $temp['city'],
            $temp['temperature'],
            $temp['feels'],
            $temp['humidity'],
            $temp['pressure'],
            $temp['description'],
            $temp['wind_speed'],
        ], ',', '"', "\\");
    }
    fclose($output);
    exit;
}

if(isset($_POST['download'])){
   generate_file($weatherToDownload);
}

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Погода</title>
</head>
<body>
    <h2>Погода</h2>
    <table>
        <tr>
            <th>Город</th>
            <th>Температура</th>
            <th>Ощущается</th>
            <th>Влажность</th>
            <th>Давление</th>
            <th>Описание</th>
            <th>Ветер</th>
        </tr>';

foreach ($weatherToDownload as $weather){
    echo '<tr>
            <td>'. $weather['city'] .'</td>
            <td>'. $weather['temperature'] . '</td>
            <td>'. $weather['feels'] .'</td>
            <td>'. $weather['humidity'] . '</td>
            <td>'. $weather['pressure'] .'</td>
            <td>'. $weather['description'] . '</td>
            <td>'. $weather['wind_speed'] . '</td>
        </tr>';
}


echo '</table>
    <form method="post">
        <input type="submit" name="download" value="Скачать" /><br/>
    </form>
</body>
</html>';
*/

class Weather {
    public $city;
    public $temperature;
    public $feels;
    public $humidity;
    public $pressure;
    public $description;
    public $wind_speed;
    public function __construct($city, $temperature, $feels, $humidity, $pressure, $description, $wind_speed) { 
        $this->city = $city;  
        $this->temperature = $temperature;
        $this->feels = $feels;
        $this->humidity = $humidity;
        $this->pressure = $pressure;
        $this->description = $description;
        $this->wind_speed = $wind_speed;
    }  
}

class Request{
    private $api_key;
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function getWeather($city): Weather {
        $params = ['q'=> $city,'appid' => $this->api_key, 'units' => 'metric', 'lang' =>'ru'];
        $url = "http://api.openweathermap.org/data/2.5/weather?" . http_build_query($params);
        $json = json_decode(file_get_contents($url), true); 
        return new Weather(
            $json['name'],
            $json['main']['temp'],
            $json['main']['feels_like'],
            $json['main']['humidity'],
            $json['main']['pressure'],
            $json['weather'][0]['description'],
            $json['wind']['speed'], 
        );
    }
}

class Table{
    public $weather_in_cities = [];
    public function __construct($cities, Request $request) {
        foreach ($cities as $city) {
            $this->weather_in_cities[] = $request->getWeather($city);
        }
    }
}

class Application {
    public Table $table;
    public function __construct($table) {
        $this->table = $table;
    }
    public function render() {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Погода</title>
        </head>
        <body>
            <h2>Погода</h2>
            <table>
                <tr>
                    <th>Город</th>
                    <th>Температура</th>
                    <th>Ощущается</th>
                    <th>Влажность</th>
                    <th>Давление</th>
                    <th>Описание</th>
                    <th>Ветер</th>
                </tr>';

        foreach ($this->table->weather_in_cities as $weather){
            echo '<tr>
                <td>'. $weather->city .'</td>
                <td>'. $weather->temperature . '</td>
                <td>'. $weather->feels .'</td>
                <td>'. $weather->humidity . '</td>
                <td>'. $weather->pressure .'</td>
                <td>'. $weather->description . '</td>
                <td>'. $weather->wind_speed . '</td>
            </tr>';
        }
        echo '</table>
            <form method="post">
                <input type="submit" name="download" value="Скачать" /><br/>
            </form>
        </body>
        </html>';
    }
    public function generateFile(){
        header("Content-Type: text/csv; charset=UTF-8");
    header('Content-Disposition: attachment; filename="weather' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output,[
        'Город', 
        'Температура', 
        'Ощущается', 
        'Влажность', 
        'Давление', 
        'Описание', 
        'Ветер'
    ], ',', '"', "\\");
    foreach ($this->table->weather_in_cities as $temp) {
        fputcsv($output, [
            $temp->city,
            $temp->temperature,
            $temp->feels,
            $temp->humidity,
            $temp->pressure,
            $temp->description,
            $temp->wind_speed,
        ], ',', '"', "\\");
    }
    fclose($output);
    exit;
    }
}


$apiKey = trim(explode('=', trim(file_get_contents(__DIR__.'/.env')), 2)[1]);
$cities = ['Saint Petersburg', 'Moscow', 'Volgograd', 'Archangelsk', 'Zvenigovo',
           'London', 'Khabarovsk', 'Magadan', 'Paris', 'Ekaterinburg',
        ];

$request = new Request($apiKey);
$table = new Table($cities, $request);
$app = new Application($table);
if(isset($_POST['download'])){
   $app->generateFile();
}
$app->render();
?>