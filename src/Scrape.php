<?php

namespace App;

require 'vendor/autoload.php';
use Symfony\Component\DomCrawler\Crawler;

class Scrape {
    use Utils;
    
    private array $products = [];
    private const PAGES = 6;
    private array $attributes = [];
   
    private function extractImageUrl($imageText) {
        return $imageText;
    }
    public function run(): void {

        for($a = 0;  $a <= SELF::PAGES ; $a++) {
            $pages = ScrapeHelper::fetchDocument($this->baseUrl.'/products?pg='.$a);
            $pages->filter('div.listing.grid-listing.product-listing')->each(function (Crawler $node) {
                  $node->filter('li')->each(function (Crawler $item) {

                            $this->attributes['details'] = $item->filter('div.listing-details p')->text();
                            $this->attributes['product_link'] = $this->baseUrl.$item->filterXPath('.//div[contains(concat(" ",normalize-space(@class)," ")," listing-image ")]//a')->attr('href');
                            $image = $item->filterXPath('.//div[contains(concat(" ",normalize-space(@class)," ")," listing-image ")]//a')->attr('style');
                            $this->attributes['image'] = $this->extractImageUrl($image);
                            $this->attributes['price'] = $item->filter('div.product-links > div > strong')->text();
                            $this->products[] = $this->attributes;
                        });
            });
        }
        
        $result = $this->products;
        file_put_contents('output.json', json_encode($result));
    }
}

$scrape = new Scrape();
$scrape->run();