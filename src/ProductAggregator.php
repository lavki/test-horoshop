<?php

namespace Horoshop;

use Horoshop\Exceptions\UnavailablePageException;

/**
 * Class ProductAggregator
 * @package Horoshop
 */
class ProductAggregator extends DiscountInterface
{
    use CurrencyConverter;
    use DiscountManager;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $info;

    /**
     * ProductAggregator constructor.
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;

        $this->readFile();
        $this->setCurrencies();
    }

    /**
     * Read data from file and set $info property
     */
    public function readFile()
    {
        $data       = file_get_contents($this->filename);    // read data from file
        $this->info = json_decode($data, true ); // parse json
    }

    /**
     * Set currencies to private property
     */
    private function setCurrencies()
    {
        $this->currencies = $this->info['currencies'];
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
        $pages = array_chunk($this->info['products'], $perPage); // break the array into pieces

        if( !array_key_exists($page, $pages) ) throw new UnavailablePageException();

        $products = []; // future collections of products
        foreach( $pages[$page-1] as $productRaw ) {
            foreach( $this->info['categories'] as $category ) {

                $this->setCurrencies();
                if( $productRaw['category'] === $category['id'] ) { // if matched ids
                    // build needed data
                    $products[] = [
                        'id'       => $productRaw['id'],
                        'title'    => $productRaw['title'],
                        'category' => [
                            'id'    => $category['id'],
                            'title' => $category['title'],
                        ],
                        'price' => [
                            'amount'           => $productRaw['amount'],
                            'discounted_price' => 0,
                            'currency'         => $currency,
                            'discount'         => [
                                'type'     => '',
                                'value'    => 0.00,
                                'relation' => '',
                            ]
                        ]
                    ];
                    break;
                }
            }
        }

        $isDiscount = false;
        $productsCatalog = [];
        foreach( $products as $index => $product ) {

            if ($index < count($products)) {
                foreach ($this->info['discounts'] as $discount) {

                    if ($product['id'] == $discount['related_id'] || $product['category']['id'] == $discount['related_id']) {
                        $isDiscount = true;
                        $discountPrice = $this->getDiscountPrice($discount['type'], $product['price']['amount'], $discount['value']);
                        $product['price']['discounted_price']     = $this->convert($discountPrice, $currency);
                        $product['price']['discount']['type']     = $discount['type'];
                        $product['price']['discount']['value']    = $discount['value'];
                        $product['price']['discount']['relation'] = $discount['relation'];
                        $productsCatalog[$index] = $product;
                    }
                }

                if( !$isDiscount ) {
                    $product['price']['discounted_price'] = $this->convert($product['price']['amount'], $currency);
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
}