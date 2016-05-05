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

use Rhubarb\Leaf\Controls\Common\Buttons\Button;
use Rhubarb\Leaf\Leaves\LeafDeploymentPackage;
use Rhubarb\Leaf\Views\View;

class SearchPanelView extends View
{
    /**
     * @var SearchPanelModel
     */
    protected $model;

    protected function getClientSideViewBridgeName()
    {
        return "SearchPanelViewBridge";
    }

    public function getDeploymentPackage()
    {
        return new LeafDeploymentPackage(__DIR__."/SearchPanelViewBridge.js");
    }

    public function setSearchControlsColumnCount($columns = 6)
    {
        $this->searchControlsColumnCount = $columns;
    }

    protected function createSubLeaves()
    {
        parent::createSubLeaves();

        $controls = $this->model->searchControls;

        $this->registerSubLeaf(...$controls);

        $searchButton = new Button("Search", "Search", function () {
            $this->model->searchedEvent->raise();
        }, true);

        $this->registerSubLeaf($searchButton);
    }

    protected function printViewContent()
    {
        print '<div class="search-panel">
					<table class="grid">
						<tr>';

        $count = 1;
        foreach ($this->model->searchControls as $control) {
            print '<td><label>' . $control->getLabel() . '</label>' . $control . '</td>';

            if ($count % $this->model->searchControlsColumnCount == 0) {
                print "</tr><tr>";
            }

            $count++;
        }

        print '<td>' . $this->leaves["Search"] . '</td>';

        print '</tr></table></div>';
    }

    protected function getBindingValue($propertyName, $index = null)
    {
        if ($index !== null ){
            if (isset($this->model->searchValues->$propertyName[$index])){
                return $this->model->searchValues->$propertyName[$index];
            } else {
                return null;
            }
        } else {
            return isset($this->model->searchValues->$propertyName) ? $this->model->searchValues->$propertyName : null;
        }
    }

    protected function setBindingValue($propertyName, $propertyValue, $index = null)
    {
        if ($index !== null){
            if (!isset($this->model->searchValues->$propertyName) || !is_array($this->model->searchValues->$propertyName)){
                $this->model->searchValues->$propertyName = [];
            }

            $this->model->searchValues->$propertyName[$index] = $propertyValue;
        } else {
            $this->model->searchValues->$propertyName = $propertyValue;
        }
    }
}
