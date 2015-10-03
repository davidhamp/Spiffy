<?php
/**
 * SPF/Core/View.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Core;

use Mustache;
use SPF\Dependency\DependencyManager;
use SPF\Dependency\Registry;

/**
 * The bridge between the current application view's model data and the Mustache template parsing engine.
 *
 * When {@link SPF\Core\View::render()} is called, it will take the accepted data and render it through the
 * MustacheEngine required by SPF.
 */
class View
{

    public $template;

    /**
     * Initially accepts a template path string.
     *
     * @param string $template Template file path string.
     */
    public function __construct($template = '')
    {
        $this->setTemplate($template);
    }

    /**
     * Sets the template path location.
     *
     * @param string $template Template file path string.
     *
     * @return SPF\Core\View Returns self to enable chaining.
     */
    public function setTemplate($template)
    {
        if (is_string($template)) {
            $this->template = $template;
        }

        return $this;
    }

    /**
     * Takes the fed in data and runs it through MustacheEngine->render() using the currently defined
     * template file path.
     *
     * @param Mixed[] $data Any parsable data object, but preferably an instance of {@link SPF\Core\Model}
     *
     * @return string
     */
    public function render($data)
    {
        return DependencyManager::get(Registry::MUSTACHE_ENGINE)->render($this->template, $data);
    }

}
