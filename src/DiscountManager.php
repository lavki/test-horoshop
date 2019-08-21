<?php

namespace Horoshop;

/**
 * Class DiscountManager
 * @package Horoshop
 */
trait DiscountManager
{
    /**
     * Get price with discount
     * @param string $type
     * @param float $amount
     * @param float $discount
     * @return float
     */
    private function getDiscountPrice( string $type, float $amount, float $discount ): float
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
}