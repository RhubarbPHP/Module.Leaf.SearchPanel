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


```php

```