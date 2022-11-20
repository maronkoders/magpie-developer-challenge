<?php

namespace App;

require 'vendor/autoload.php';


use Symfony\Component\DomCrawler\Crawler;

class Scrape
{
    use Utils;
    
    private array $smartphone = [];
    private const PAGES = 3;
   
    public function run(): void
    {  
        for($a=1;  $a <= SELF::PAGES ; $a++)
        {

            // trying to fetch data on the url by referering to the base url in utils
            $items = ScrapeHelper::fetchitems($this->baseUrl.'developer-challenge/smartphones/?page='.$a);
            $items->filter(' div.mb-12')->each(function (Crawler $node)
            {
                
                  $node->filter(' div.rounded-md')->each(function (Crawler $item)
                        {
                            $item->filter('div.my-4 > div.-mx-2 > div.px-2')->each(function (Crawler $color) use ($item) 
                                {   
                                        $itemObj = new item();
                                        $this->smartphone[]  = $itemObj->properties($item, $color);
                                });    
                        });
                  
                
            });
        }

        $result = $this->smartphone;

        file_put_contents('output.json', json_encode($result));
    }