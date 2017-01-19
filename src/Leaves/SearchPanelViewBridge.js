var bridge = function (leafPath) {
    window.rhubarb.viewBridgeClasses.ViewBridge.apply(this, arguments);

    this.searchTimer = null;
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.ViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.onRegistered = function () {
    window.rhubarb.viewBridgeClasses.ViewBridge.prototype.onRegistered.apply(this, arguments);

    if (this.model.autoSubmit) {
        var subPresenters = this.getSubLeaves();

        for (var i in subPresenters) {
            if (subPresenters.hasOwnProperty(i)) {
                // If the sub presenter is emitting key press events we need to know.
                subPresenters[i].onKeyPress = function () {
                    this.startAutoSubmitTimer();
                }.bind(this);
            }
        }
    }

    if (this.model.searchButtonLeafName) {
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
    if (this.model.autoSubmit) {
        this.startAutoSubmitTimer();
    }
};

window.rhubarb.viewBridgeClasses.SearchPanelViewBridge = bridge;
