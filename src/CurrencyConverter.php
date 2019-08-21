<?php

namespace Horoshop;

/**
 * Class CurrencyConverter
 * @package Horoshop
 */
trait CurrencyConverter
{
    /**
     * @var array
     */
    private $currencies = [];

    /**
     * @return mixed
     */
    abstract function setCurrencies();

    /**
     * Converter currencies
     * @param float $amount
     * @param string $currency
     * @return float
     */
    private function convert( float $amount, string $currency ): float
    {
        if( $currency !== 'UAH' ) {
            $curr = $this->currencies[0]['rates'];

            if( !isset($curr[$currency]) ) {
                throw new UnavailableCurrencyException();
            }

            $price = sprintf("%01.2f", ceil(($amount * $curr[$currency]) * 100) / 100);
            $convertPrice = round($price, 2);
        } else {
            $convertPrice = $amount;
        }

        return $convertPrice;
    }
}