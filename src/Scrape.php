<?php

namespace App;

require 'vendor/autoload.php';


use Symfony\Component\DomCrawler\Crawler;

class Scrape
{
    use Utils;
   
    public function run(): void
    {  
        $result = [];
        file_put_contents('output.json', json_encode($result));
    }
}

$scrape = new Scrape();
$scrape->run();
