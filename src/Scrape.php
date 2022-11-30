<?php

namespace App;

require 'vendor/autoload.php';


use Symfony\Component\DomCrawler\Crawler;

class Scrape
{
    use Utils;
    
    private array $smartphoness = [];
    private const TOTAL_PAGES = 3;
   
    public function run(): void
    {  
        for($i=1;  $i <= SELF::TOTAL_PAGES ; $i++)
        {
            $document = ScrapeHelper::fetchDocument($this->baseUrl.'/smartphones/?page='.$i);
            $document->filter('div.-mx-4 > div.mb-12')->each(function (Crawler $node)
            {
                try {
                  $node->filter('div.mb-12 > div.rounded-md')->each(function (Crawler $product)
                        {
                            $product->filter('div.my-4 > div.-mx-2 > div.px-2')->each(function (Crawler $color) use ($product) 
                                {   
                                        $productObj = new Product();
                                        $this->smartphones$smartphoness[]  = $productObj->properties($product, $color);
                                });    
                        });
                  
                } catch (\InvalidArgumentException $e) {
                    echo 'failed to scrape product data ' . $e->getMessage() . $e->getMessage();
                }
            });
        }

        $result = $this->dedup($this->smartphones$smartphoness);

        file_put_contents('output.json', json_encode($result));
    }

    public  function searchDuplicate($arr, $obj) {
        foreach ($arr as $value) {
            if ($value['title'] == $obj['title'] && $value['color'] == $obj['color'] && $value['price'] == $obj['price']) {
                return true;
            }
        }
        return false;
    }

    public function dedup($arr)
    {
        $result = array();
        foreach ($arr as $obj) {
            if ($this->searchDuplicate($result, $obj) === false) {
                $result[] = $obj;
            }
        }
        return $result;
    }
}

$scrape = new Scrape();
$scrape->run();