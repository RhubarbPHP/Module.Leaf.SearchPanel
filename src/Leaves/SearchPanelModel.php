<?php

namespace Rhubarb\Leaf\SearchPanel\Leaves;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Leaves\Controls\Control;
use Rhubarb\Leaf\Leaves\UrlStateLeafModel;

class SearchPanelModel extends UrlStateLeafModel
{
    /**
     * @var Event
     */
    public $searchedEvent;

    /**
     * True if the search should be submitted instantly without pressing the search button.
     *
     * @var bool
     */
    public $autoSubmit = false;

    /**
     * @var string The name of the Search button leaf. Used by the ViewBridge to bind events to the button.
     */
    public $searchButtonLeafName = "Search";

    /**
     * The binding source for controls in the panel
     *
     * @var string[]
     */
    public $searchValues = [];

    /**
     * The number of columns to use in the panel.
     *
     * @var int
     */
    public $searchControlsColumnCount = 6;

    /**
     * An array of control leaves.
     *
     * @var Control[]
     */
    public $searchControls = [];

    /**
     * An array with keys matching search control names and values defining what URL GET param names they should have
     *
     * @var string[]
     */
    public $urlStateNames = [];

    /**
     * Data from URL GET params matching controls based on their names in $urlStateNames
     *
     * @var array
     */
    public $urlStateValues = [];

    public function __construct()
    {
        parent::__construct();

        $this->searchedEvent = new Event();
    }

    public function getSearchValue($name, $defaultValue = false)
    {
        if (isset($this->searchValues[$name])) {
            return $this->searchValues[$name];
        }

        return $defaultValue;
    }

    /**
     * Return the list of properties that can be exposed publicly
     *
     * @return array
     */
    protected function getExposableModelProperties()
    {
        $list = parent::getExposableModelProperties();
        $list[] = 'autoSubmit';
        $list[] = 'searchButtonLeafName';
        $list[] = 'urlStateNames';

        return $list;
    }

    public function getBoundValue($propertyName, $index = null)
    {
        if ($index !== null) {
            if (isset($this->searchValues[$propertyName][$index])) {
                return $this->searchValues[$propertyName][$index];
            } else {
                return null;
            }
        } else {
            return isset($this->searchValues[$propertyName]) ? $this->searchValues[$propertyName] : null;
        }
    }

    public function setBoundValue($propertyName, $propertyValue, $index = null)
    {
        if ($index !== null) {
            if (!isset($this->searchValues[$propertyName]) || !is_array($this->searchValues[$propertyName])) {
                $this->searchValues[$propertyName] = [];
            }

            $this->searchValues[$propertyName][$index] = $propertyValue;
        } else {
            $this->searchValues[$propertyName] = $propertyValue;
        }
    }
}
