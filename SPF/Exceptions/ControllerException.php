<?php
/**
 * SPF/Exceptions/ControllerException.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Exceptions;

class ControllerException extends \Exception
{
    public $code = 'SPF200';
}