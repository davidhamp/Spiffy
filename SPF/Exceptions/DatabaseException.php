<?php
/**
 * SPF/Exceptions/DatabaseException.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Exceptions;

class DatabaseException extends \Exception
{
    public $code = 'SPF300';
}