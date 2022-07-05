<?php

namespace App;

require 'vendor/autoload.php';

use DateTime;
use Symfony\Component\DomCrawler\Crawler;

class Scrape
{
    private array $products = [];
    private array $product = [];
    private const TOTAL_PAGES = 3;
    private const DATE_LENGTH  = 12;
    private const NUMBER_LINE = "0123456789";
    private const DATE_FORMAT ='Y-m-d';

    private string  $baseUrl = "https://www.magpiehq.com/developer-challenge";

    public function convertCapacity($storage) : int
    {
        $numericCapacity = (int) filter_var($storage, FILTER_SANITIZE_NUMBER_INT);
        return str_contains($storage,"GB") ? 1000 * $numericCapacity : $numericCapacity;
    }

    public function imgPath($src) : string
    {
        return $this->baseUrl.str_replace('..','',$src);
    }
    
    private function removeCurrencySymbol($price)
    {
        return str_replace('£','',$price);
    }

    public function shippingText($text)
    {
        return str_contains($text, "Availability:") ? '': $text;
    }

    public function dateFormatter($date)
    {
        return (new DateTime($date))->format(SELF::DATE_FORMAT);
    }

    public function getDate($date)
    {
        return substr($date, strcspn($date, SELF::NUMBER_LINE));
    }

    public function formatShippingDate($text)
    {
        $date = $this->shippingDate($text);

        if(strlen($date) > SELF::DATE_LENGTH)
        {
           $simpleDate = substr($date, -SELF::DATE_LENGTH);
           $date = $this->getDate($simpleDate);
           return  $this->dateFormatter($date);
        }

        return $date !=="" ? $this->dateFormatter($date) :"";
    }   

    public function shippingDate($text)
    {
        $date = "";
        if(str_contains($text,"tomorrow"))
        {
            $date = $this->dateFormatter("tomorrow");
        }else{
            $date = !is_null($text) ? $this->getDate($text): "";
        }
        return $date;
    }

    public function itemAvailability($availability)
    {
        $status = $this->getStockStatus($availability);
        return  str_contains(strtolower($status),'out of stock') ? false : true;
    }

    public function getStockStatus($availability)
    {
        $status = str_replace('Availability:','', $availability);
        return trim($status);
    }

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
                                        $this->product['title'] = $product->filter('span.product-name')->html()." ".$product->filter('span.product-capacity')->html();
                                        $this->product['price'] = $this->removeCurrencySymbol($product->filter('div.text-lg')->text());
                                        $this->product['imageUrl'] = $this->imgPath($product->filter('img.mx-auto')->attr('src'));
                                        $this->product['capacityMB'] =  $this->convertCapacity($product->filter('span.product-capacity')->html());
                                        $this->product['color'] = strtolower($color->filter('span')->attr('data-colour'));
                                        $this->product['availabilityText'] =  $this->getStockStatus($product->filter('div.rounded-md > div.text-sm')->first()->text());
                                        $this->product['isAvailable'] =  $this->itemAvailability($product->filter('div.rounded-md > div.text-sm')->first()->text());
                                        $this->product['shippingText'] = $this->shippingText($product->filter('div.my-4')->last()->text());
                                        $this->product['shippingDate'] = $this->formatShippingDate($product->filter('div.my-4')->last()->text());
    
                                        $this->products[]  = $this->product;
                                });    
                        });
                  
                } catch (\InvalidArgumentException $e) {
                    echo 'failed to scrape product data ' . $e->getMessage() . $e->getMessage();
                }
            });
        }

        $result = $this->dedup($this->products);

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

