<?php namespace App\Classes\Jackett\Enums;

/**
 * Class Quality
 * @package App\Classes\Jackett\Enums
 */
class Quality extends AbstractEnum {

    /**
     * Constant SD: Standard Definition
     * @var int
     */
    public const SD = 480;

    /**
     * Constant HD: High Definition
     * @var int
     */
    public const HD = 720;

    /**
     * Constant FHD: Full High Definition
     * @var int
     */
    public const FHD = 1080;

    /**
     * Constant QHD: Quad High Definition
     * @var int
     */
    public const QHD = 1440;

    /**
     * Constant UHD: Ultra High Definition
     * @var int
     */
    public const UHD = 2160;

}
