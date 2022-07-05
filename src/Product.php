<?php

namespace App;

use DateTime;

class Product
{

    use Utils;
    private array $product = [];
    private const DATE_LENGTH  = 12;
    private const NUMBER_LINE = "0123456789";
    private const DATE_FORMAT ='Y-m-d';

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

    public function properties($product, $color)
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

        return $this->product;
    }

}
