<?php

use Symfony\Component\DomCrawler\Crawler;

class Product {

    public $product = [];
    public function getProductAttributes(Crawler $element) {
        // $product = [];
        $this->product['image'] = $element->filter('div.listing-image')->first()->link();
        // $product['price']
        return $this->product;
    }

}