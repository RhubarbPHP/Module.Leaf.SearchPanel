<?php

/*
 *	Copyright 2015 RhubarbPHP
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Rhubarb\Leaf\SearchPanel\Leaves;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Stem\Filters\Group;

/**
 * A search interface that raises search events on behalf of the contained search controls.
 *
 * @property bool $AutoSubmit True if searching should happen as you type.
 */
class SearchPanel extends Leaf
{
    /**
     * @var Event
     */
    public $searchedEvent;

    private $defaultControlValues = [];

    /**
     * @var SearchPanelModel
     */
    protected $model;

    public function __construct($name = "")
    {
        parent::__construct($name);

        $this->snapshotDefaultControlValues();
    }

    protected final function snapshotDefaultControlValues()
    {
        $controls = $this->model->searchControls;
        $defaultValues = $this->getDefaultControlValues();

        foreach ($controls as $control) {
            if (!isset($defaultValues[$control->getName()])) {
                $defaultValues[$control->getName()] = "";
            }
        }

        $this->model->searchValues = $defaultValues;
        $this->defaultControlValues = $this->getSearchControlValues();
    }

    /**
     * Override to set default control values on the model.
     */
    protected function getDefaultControlValues()
    {
        return [];
    }

    /**
     * You should implement this to return an ordered collection of control presenters to use in the search.
     *
     * @return array
     */
    protected function createSearchControls()
    {
        return [];
    }

    protected function setSearchControlsColumnCount($columns = 6)
    {
        $this->model->searchControlsColumnCount = $columns;
    }

    /**
     * Returns a key value pair array of each control name and it's value
     *
     * @return string[]
     */
    public function getSearchControlValues()
    {
        $data = $this->model->searchValues;
        $controlData = [];

        foreach ($this->model->searchControls as $control) {
            $controlName = $control->getName();

            if (isset($data[$controlName])) {
                $controlData[$controlName] = $data[$controlName];
            }
        }

        return $controlData;
    }

    /**
     * Sets the values of the search controls.
     *
     * @param array $controlValues A key value pair array.
     */
    public function setSearchControlValues($controlValues = [])
    {
        $controlValues = array_merge($this->defaultControlValues, $controlValues);

        foreach ($this->model->searchControls as $control) {
            $controlName = $control->getName();

            if (isset($controlValues[$controlName])) {
                $this->model->searchValues->$controlName = $controlValues[$controlName];
            }
        }

        $this->reRender();

        $this->searchedEvent->raise();
    }

    protected function bindEvents(Leaf $leaf)
    {
        if (property_exists($leaf, "getFilterEvent")){
            $leaf->getFilterEvent->attachHandler(function(){
                return $this->onGetFilter();
            });
        }

        if (property_exists($leaf, "getSearchControlValuesEvent")){
            $leaf->getSearchControlValuesEvent->attachHandler(function(){
                return $this->getSearchControlValues();
            });
        }
    }

    /**
     * Override this method to create any filters that are required.
     *
     * @param \Rhubarb\Stem\Filters\Group $filterGroup
     */
    public function populateFilterGroup(Group $filterGroup)
    {

    }

    protected function onGetFilter()
    {
        $group = new Group("AND");

        $this->populateFilterGroup($group);

        $filters = $group->getFilters();

        if (sizeof($filters) == 0) {
            // The search doesn't want to filter anything.
            return null;
        }

        return $group;
    }

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return SearchPanelView::class;
    }

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        $model = new SearchPanelModel();

        // Pass through of searchedEvent
        $model->searchControls = $this->createSearchControls();
        $this->searchedEvent = $model->searchedEvent;

        return $model;
    }
}
