<?php

require 'vendor/autoload.php'; 

use Symfony\Component\DomCrawler\Crawler;

$url = "https://www.magpiehq.com/developer-challenge/smartphones/?page=1";
$html = file_get_contents($url); 

$crawler = new Crawler($html);
$item = [];
// loop through the values

$nodeValues = $crawler->filter('div.mb-12 > div.rounded-md')->each(function (Crawler $node, $i) {
    //searching for the desired values
   

// storing values in any array
$text = $node->text();
$image = $node->filter('img')->attr('src');
$item = [$image, $text];

//echo($item);

file_put_contents('output.json', json_encode($item));

});


