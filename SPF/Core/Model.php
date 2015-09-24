<?php
/**
 * SPF/Core/Model.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Core;

use SPF\Annotations\Engine as AnnotationEngine;
use \JsonSerializable;
use \Traversable;

/**
 * Recommended container for modeled data.
 *
 * Models in your project should extend off this base class.
 *     This model will attempt to strictly adhere to the defined model elements when using a Traversable data set
 *     in the contstructor or through the {@link SPF\Core\Model::loadData()} method.
 *     It utilizes the JsonSerializable interface as well as the {@link SPF\Annotations\Engine} class in order to
 *     control JSON serialized output.
 *     You may use the @SPF:JsonIgnore Annoation on model properties in order to hide them from JSON Serialization.
 *
 * @uses SPF\Annotations\Engine
 * @uses JsonSerializable
 */
class Model implements JsonSerializable
{

    /**
     * Constructor.
     *
     * Accepts an Traversable data set and feeds it into the {@link SPF\Core\Model::loadData()} method.
     *
     * @param array $data Traversable data set to feed into {@link SPF\Core\Model::loadData()} method
     *
     * @return void
     */
    public function __construct($data = array())
    {
        $this->loadData($data);
    }

    /**
     * Initilizes or updates model property values
     *
     * Takes an Traversable data set, and will attempt to update model properties that correspond to the key names
     *     of the array.  This will prioritize using 'set' methods on the model instance, which are methods that have
     *     the same key value prepended with 'set'.
     *     _Example_: the method 'setUserName()' would be called when encountering the array key 'userName'.  If no
     *     'set' method is defined in the model, it will double check for that the named property exists.
     *     If it doesn't, the array value will be skipped.
     *
     * @param array $data Array data to load into the model
     *
     * @return void
     */
    public function loadData($data)
    {
        if (is_array($data) || $data instanceof Traversable) {
            foreach ($data as $key => $value) {
                $method = 'set' . ucfirst($key);
                if (method_exists($this, $method)) {
                    $this->{$method}($value);
                    continue;
                }

                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                    continue;
                }
            }
        }
    }

    /**
     * __toString will json_encode the moddel
     *
     * @return string json formatted string representation of the current model.
     */
    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * JsonSerializable interface
     *
     * Loops over teh properties in the model and inspects their annotations.  If the SPF:JsonIgnore annotation is
     *     present, the propery is skipped.
     *
     * @return array Array of property values as per JsonSerializable interface.
     */
    public function jsonSerialize()
    {
        $out = array();
        foreach ($this as $key => $value) {
            $annotation = AnnotationEngine::get($this, 'property', $key);
            if ($annotation->has('JsonIgnore')) {
                continue;
            }

            $out[$key] = $value;
        }

        return $out;
    }
}