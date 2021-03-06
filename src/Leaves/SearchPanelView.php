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

use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Leaf\Controls\Common\Buttons\Button;
use Rhubarb\Leaf\Leaves\UrlStateView;

class SearchPanelView extends UrlStateView
{
    /**
     * @var SearchPanelModel
     */
    protected $model;

    protected function getViewBridgeName()
    {
        return 'SearchPanelViewBridge';
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . '/SearchPanelViewBridge.js';
        return $package;
    }

    protected function createSubLeaves()
    {
        parent::createSubLeaves();

        $controls = $this->model->searchControls;

        $this->registerSubLeaf(...$controls);

        $searchButton = new Button($this->model->searchButtonLeafName, "Search", function () {
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

    public function parseUrlState(WebRequest $request)
    {
	/**
	* To ensure child leaves process their raw request values in the normal way we mutate the 
	* post data to change our shortened keys to those that match the leaf path of the child
	* leaf.
	*/
        foreach ($this->model->urlStateNames as $controlName => $paramName) {
            if ($request->get($paramName) !== null) {
                foreach ($this->model->searchControls as $control) {
                    if ($control->getName() == $controlName) {
                        $path = $control->getPath();
                        if (!isset($request->postData[$path])) {
                            $request->postData[$path] = $request->get($paramName);
                        }
                        break;
                    }
                }
            }
        }
    }
}
