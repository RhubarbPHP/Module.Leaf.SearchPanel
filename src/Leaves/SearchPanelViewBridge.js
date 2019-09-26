var bridge = function (leafPath) {
    window.rhubarb.viewBridgeClasses.UrlStateViewBridge.apply(this, arguments);

    this.searchTimer = null;

    // This flag controls the interaction between keyUp events and onChange events
    // to prevent double firing of search.
    this.submitOnChange = true;
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.UrlStateViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.onRegistered = function () {
    window.rhubarb.viewBridgeClasses.ViewBridge.prototype.onRegistered.apply(this, arguments);

    if (this.model.autoSubmit) {
        var subPresenters = this.getSubLeaves();

        for (var i in subPresenters) {
            if (subPresenters.hasOwnProperty(i)) {
                // If the sub presenter is emitting key press events we need to know.
                subPresenters[i].onKeyUp = function () {
                    // Clearly the input supports key up as it's being used - disabled on change event
                    // which will 'double fire'
                    this.submitOnChange = false;
                    this.startAutoSubmitTimer();
                }.bind(this);
            }
        }
    }

    if (this.model.searchButtonLeafName) {

        this.viewNode.addEventListener('keypress',function(event) {
            if(event.keyCode == 13) {
                event.preventDefault();
                this.findChildViewBridge(this.model.searchButtonLeafName).viewNode.click();
            }
        }.bind(this));

        var button = this.findChildViewBridge(this.model.searchButtonLeafName);
        if (button) {
            button.attachClientEventHandler('OnButtonPressed', function () {
                this.onSearchStarted.apply(this, arguments);
            }.bind(this));
            button.attachClientEventHandler('ButtonPressCompleted', function () {
                this.onSearchFinished.apply(this, arguments);
            }.bind(this));
            button.attachClientEventHandler('ButtonPressFailed', function () {
                this.onSearchFailed.apply(this, arguments);
            }.bind(this));
        }
    }
};

/**
 * A place to update the interface to signal the start of a search
 */
bridge.prototype.onSearchStarted = function () {
    var hasState = false;
    var state = {};

    for (var controlName in this.model.urlStateNames) {
        if (!this.model.urlStateNames.hasOwnProperty(controlName)) {
            continue;
        }

        var control = this.findChildViewBridge(controlName);
        if (control) {
            hasState = true;
            state[this.model.urlStateNames[controlName]] = control.getValue();
        }
    }

    if (hasState) {
        this.updateUrlState(state);
    }
};

/**
 * A place to update the interface to signal the end of a search
 */
bridge.prototype.onSearchFinished = function () {
};

/**
 * A place to update the interface to signal the failure of a search
 */
bridge.prototype.onSearchFailed = function () {
    this.onSearchFinished.apply(this, arguments);
};

bridge.prototype.startAutoSubmitTimer = function () {
    if (this.searchTimer) {
        clearTimeout(this.searchTimer);
    }

    this.searchTimer = setTimeout(function () {
        this.onSearchStarted();
        this.raiseServerEvent(
            "searched",
            function () {
                this.onSearchFinished.apply(this, arguments);
            }.bind(this),
            function () {
                this.onSearchFailed.apply(this, arguments);
            }.bind(this)
        );
    }.bind(this), 300);
};

bridge.prototype.onSubLeafValueChanged = function () {
    if (this.model.autoSubmit && this.submitOnChange) {
        this.startAutoSubmitTimer();
    }
};

window.rhubarb.viewBridgeClasses.SearchPanelViewBridge = bridge;
