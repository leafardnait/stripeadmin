<?php

/**
 * @Author: nguyen
 * @Date:   2020-12-15 14:01:01
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2023-08-19 16:25:12
 * https://github.com/magento/magento2-samples/tree/master/sample-module-command/Console/Command
 */

namespace Magiccart\Alothemes\Console\Command;

class RandomCrosssell extends RandomRelated
{
    protected $linkType = 'crosssell';

    protected $commandName = 'RandomCrosssell';
}