<?php

namespace Horoshop;

use Horoshop\Exceptions\UnavailablePageException;

/**
 * Class ProductAggregator
 * @package Horoshop
 */
class ProductAggregator
{
    /**
     * @var string
     */
    private $filename;

    /**
     * ProductAggregator constructor.
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param string $currency
     * @param int    $page
     * @param int    $perPage
     *
     * @return string Json
     * @throws UnavailablePageException
     */
    public function find(string $currency, int $page, int $perPage): string
    {
        $data  = file_get_contents($this->filename);        // read data from file
        $info  = json_decode($data);                        // parse json
        $pages = array_chunk($info->products, $perPage);    // break the array into pieces

        /*
        array_unshift($pages,'');
        unset($pages[0]);
        */

        if( !array_key_exists($page, $pages) ) throw new UnavailablePageException();

        $products = []; // future collections of products
        foreach( $pages[$page-1] as $productRaw ) {
            foreach( $info->categories as $category ) {

                if( $productRaw->category === $category->id ) { // if matched ids

                    // build needed data
                    $products[] = [
                        'id'       => $productRaw->id,
                        'title'    => $productRaw->title,
                        'category' => [
                            'id'    => $category->id,
                            'title' => $category->title,
                        ],
                        'price' => [
                            'amount'           => $productRaw->amount,
                            'discounted_price' => 0,
                            'currency'         => $currency,
                            'discount'         => [
                                'type'     => '',
                                'value'    => 0.00,
                                'relation' => '',
                            ]
                        ]
                    ];
                }
            }
        }

        $isDiscount = false;
        $productsCatalog = [];
        foreach( $products as $index => $product ) {
            if ($index < count($products)) {
                foreach ($info->discounts as $discount) {

                    if ($product['id'] == $discount->related_id || $product['category']['id'] == $discount->related_id) {
                        $isDiscount = true;
                        $discountPrice = $this->setDiscount($discount->type, $product['price']['amount'], $discount->value);
                        $product['price']['discounted_price']     = $this->currencyConvert($discountPrice, $currency);
                        $product['price']['discount']['type']     = $discount->type;
                        $product['price']['discount']['value']    = $discount->value;
                        $product['price']['discount']['relation'] = $discount->relation;
                        $productsCatalog[$index] = $product;
                    }
                }

                if( !$isDiscount ) {
                    $product['price']['discounted_price'] = $this->currencyConvert($product['price']['amount'], $currency);
                    $productsCatalog[$index] = $product;
                }

                $isDiscount = false;
            }
        }

        $result['items']   = $productsCatalog;
        $result['perPage'] = $perPage;
        $result['pages']   = count($pages);
        $result['page']    = $page;

        return json_encode($result);
    }

    /**
     * Set and return price with discount
     * @param string $type
     * @param float $amount
     * @param float $discount
     * @return float
     */
    private function setDiscount( string $type, float $amount, float $discount ): float
    {
        switch( $type ) {
            case 'absolute' :
                $price = $amount - $discount;
                break;
            case 'percent' :
                $price = $amount - ($amount / 100 * $discount);
                break;
            case 'wholesale' :
                $price = $amount;
                break;
            default :
                $price = $amount;
                break;
        }

        $price = round(sprintf("%01.2f", ceil($price * 100) / 100), 2);

        return $price;
    }

    /**
     * Converter currencies
     * @param float $amount
     * @param string $currency
     * @return float
     */
    private function currencyConvert( float $amount, string $currency ): float
    {
        if( $currency !== 'UAH' ) {
            $currencies = $this->getCurrencies();
            $curr       = $currencies[0]->rates;

            if( !isset($curr->$currency) ) {
                throw new UnavailableCurrencyException();
            }

            $price = sprintf("%01.2f", ceil(($amount * $curr->$currency) * 100) / 100);
            $convertPrice = round($price, 2);
        } else {
            $convertPrice = $amount;
        }

        return $convertPrice;
    }

    /**
     * Read currency rates
     * @return mixed
     */
    private function getCurrencies()
    {
        $data = json_decode(file_get_contents($this->filename));

        return $data->currencies;
    }
}