<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Holds information about a non-compiled Twig templates.
 *
 * @final
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Twig_Source
{
    private $code;
    private $name;
    private $path;

    /**
     * @param string $code The templates source code
     * @param string $name The templates logical name
     * @param string $path The filesystem path of the templates if any
     */
    public function __construct($code, $name, $path = '')
    {
        $this->code = $code;
        $this->name = $name;
        $this->path = $path;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }
}
