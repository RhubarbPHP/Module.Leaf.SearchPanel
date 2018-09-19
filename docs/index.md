Search Panels
=============

A search panel is a group of controls in conjunction with a Search button that raises an event
to say when the user has changed the search criteria.

This can be used to re-filter and re-render other elements on the page. It is most commonly
combined with a `Table` component.

## Creating the control set

Unlike some Application components `SearchPanel` can't be configured it must be extended.
Once extended you can override the `createSearchControls()` function and return an array
of the Leaf control objects you want to render in the UI. The names of the controls are
important as the values of the search criteria will be keyed using these names.

```php
class JobsSearchPanel extends SearchPanel
{
    protected function createSearchControls()
    {
        $status = new DropDown("Status");
        $status->setSelectionItems([
            ["", "Any Status"],
            ["Incoming"],
            ["Outgoing"],
            ["Stale"]
        ]);

        return [
            $status,
            new Checkbox("Sent")
        ];
    }
}
```

This example creates a DropDown and a Checkbox.

## Handling the searchedEvent directly

The `searchedEvent` is raised when the values of the search controls change. It is possible
to handle this event manually:

```php
class MyPageView extends View
{
    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            $this->search = new JobSearchPanel(),         
        );
        
        $this->search->searchedEvent->attachHandler(function(){
            $searchCriteria = $this->search->getSearchControlValues();
            
            // Do something exciting with the search criteria.
            // ...
        });
    }

    protected function printViewContent()
    {
        print $this->search;
    }
}
```

`getSearchControlValues()` returns an array of key value pairs with each control's 
current value.

This event is normally handled by other Application components designed to inter-operate
with it, for example the `Table`.

## Inter-operating with other Application Components

SearchPanels include a pattern for filtering Stem Collections. To use the SearchPanel
with other application components you will need to override the `populateFilterGroup()` function.


This is passed an empty Group filter which you should populate by calling `addFilter()`:

```php
class JobsSearchPanel extends SearchPanel
{
    protected function createSearchControls()
    {
        $status = new DropDown("Status");
        $status->setSelectionItems([
            ["", "Any Status"],
            ["Incoming"],
            ["Outgoing"],
            ["Stale"]
        ]);

        return [
            $status,
            new Checkbox("OnlySentItems")
        ];
    }
    
    public function populateFilterGroup(Group $group)
    {
        $values = $this->getSearchControlValues();
        
        if ($values["Status"] != ""){
            $group->addFilter(new Equals("Status", $values["Status"]));
        }
        
        if ($values["OnlySentItems"]){
            $group->addFilter(new Equals("Sent", true));
        }
    }
}
```

This is the most common way for using a Search Panel

## Customising the view

Many customisations are achievable by simplying configuring the controls:

```php
class JobsSearchPanel extends SearchPanel
{
    protected function createSearchControls()
    {
        $status = new DropDown("Status");
        $status->setSelectionItems([
            ["", "Any Status"],
            ["Incoming"],
            ["Outgoing"],
            ["Stale"]
        ]);
        
        $sent = new Checkbox("OnlySentItems");
        
        $status->addCssClassNames('extra-padding');
        $sent->setLabel("Show sent items only");

        return [
            $status,
            $sent
        ];
    }
}
```

For more control simply create your own extended `SearchPanelView` and register it with the
DI container or set your individual `SearchPanel` to use it.

## Auto submit/As-you-type searching

A common feature is making the search panel submit a search as the changes the search criteria
without requiring them to click "Search". This also enables "as-you-type" searching in text
controls.

To enable this simply set the $autoSubmit value in the model to true:

```php
class JobsSearchPanel extends SearchPanel
{
    protected function onModelCreated()
    {
        $this->model->autoSubmit = true;
        
        parent::onModelCreated();
    }
}
```

When a search control value is changed a timer is started after which the search is preformed.
The timer is a very short interval but if other changes are made the timer is restarted to avoid
'flicker' in a result set.

You should make every effort to ensure there is good visual feedback of when the panel is and is
not searching.