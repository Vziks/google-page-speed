<?php

require __DIR__ . '/vendor/autoload.php';

$strategy = isset($argv[1]) ? $argv[1] : 'desktop';
$url = isset($argv[2]) ? $argv[2] : 'http://www.example.com/';
$score = isset($argv[3]) ? $argv[3] : 70;
$apiKey = isset($argv[4]) ? $argv[4] : '<GoogleAPIkey>';

$console = new \Vziks\PageSpeed\Helpers\Colors();

try {
    $googlePageSpeed = new Vziks\PageSpeed\Collector($apiKey, $url, $strategy, $score);

    /**
     * @var \Vziks\PageSpeed\InfoGraphic $InfoGraphic
     */
    $InfoGraphic = $googlePageSpeed->getGooglePageSpeedInfo($url);

    $InfoGraphic->getColoredInfographic();

} catch (\Vziks\PageSpeed\Exception\CollectorException $e) {
    echo $console->getColoredString($e->getMessage(), "red", "black", true);
}