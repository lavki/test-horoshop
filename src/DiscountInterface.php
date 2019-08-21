<?php

namespace Horoshop;

/**
 * Class DiscountInterface
 * @package Horoshop
 */
abstract class DiscountInterface
{
    /**
     * @param string $type
     * @param float $amount
     * @param float $discount
     * @return float
     */
    private function getDiscountPrice( string $type, float $amount, float $discount ): float
    {

    }
}