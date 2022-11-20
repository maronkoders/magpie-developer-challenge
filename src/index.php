<?php


require 'vendor/autoload.php';


use Symfony\Component\DomCrawler\Crawler;

$url = 'https://www.magpiehq.com/developer-challenge/smartphones/';

$client = new \GuzzleHttp\Client();

// get data from the url
$res = $client->request('GET', $url);
$html = ''.$res->getBody();

  
$crawler = new Crawler($html);

// trying to loop through the images` data
$nodeValues = $crawler->filter('div.my-4 > div.-mx-2 > div.px-2')->each(function (Crawler $node, $i) {
    
// store values into an array
$text = $node->text();
$price = $node->price();
$color = $node->color();
$image= $node->filter('img')->attr('src');

$items = [$text, $image,$color, $price];
return $item;

});
$result = $this->dedup($this->items);
// trying to output results in output.json file
file_put_contents('output.json', json_encode($result));

// trying to remove duplicate
  function findDuplicate($arr, $obj) {
    foreach ($arr as $value) {
        if ($value['title'] == $obj['title'] && $value['color'] == $obj['color'] && $value['price'] == $obj['price']) {
            return true;
        }
    }
    return false;
}
 function dedup($arr)
{
    $result = array();
    foreach ($arr as $obj) {
        if ($this->findDuplicate($result, $obj) === false) {
            $result[] = $obj;
        }
    }
    return $result;
}

