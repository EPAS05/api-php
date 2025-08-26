<?php
$apiKey = 'bfe22cf083d83e8d0f1ac3b55bf70cc1';
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

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Weather</title>
</head>
<body>
    <h2>Погода в различных городах</h2>
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

foreach ($cities as $city){
    $weather = get_weather($city, $apiKey);
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
</body>
</html>';
?>