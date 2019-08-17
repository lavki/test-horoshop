<?php

namespace Horoshop;

use Horoshop\Exceptions\UnavailablePageException;

class ProductAggregator
{
    /**
     * @var string
     */
    private $filename;

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
        try {
            $data  = file_get_contents($this->filename);
            $info  = json_decode($data);
            $pages = array_chunk($info->products, $perPage);

            $products = [];
            foreach( $pages[$page-1] as $productRaw ) {
                foreach( $info->categories as $category ) {

                    if( $productRaw->category === $category->id ) {

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
            $result['pages']   = count($pages[$page]);
            $result['page']    = $page;

            /*echo '<pre>';
            print_r($result);
            echo '</pre>';
            exit;*/

            return json_encode($result);

        } catch (UnavailablePageException $error ) {
            return $error;
        }
    }

    /**
     * Set discount price
     * @param string $type
     * @param float $amount
     * @param float $discount
     * @return float
     */
    public function setDiscount( string $type, float $amount, float $discount ): string
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

        return number_format(ceil($price * 100) / 100, 2, '.', '');
    }

    /**
     * Converter currencies
     * @param float $amount
     * @param string $currency
     */
    public function currencyConvert( float $amount, string $currency )
    {
        if( $currency !== 'UAH' ) {
            $currencies = $this->getCurrencies();
            $curr = $currencies[0]->rates;

            return (float) round(number_format(ceil(($amount * $curr->$currency) * 100) / 100, 3, '.', ''), 2);
        } else {
            return (float) round(number_format(ceil($amount * 100) / 100, 3, '.', ''), 2);
        }
    }

    /**
     * Read currency rates
     * @return mixed
     */
    public function getCurrencies()
    {
        $data = json_decode(file_get_contents($this->filename));

        return $data->currencies;
    }
}