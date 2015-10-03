<?php
/**
 * SPF/Exceptions/DependencyResolutionException.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Exceptions;

class DependencyResolutionException extends \Exception
{
    public $code = 'SPF100';
}