/**
*
* Copyright 2007
*
* Paulius Uza
* http://www.uza.lt
*
* Dan Florio
* http://www.polygeek.com
*
* Project website:
* http://code.google.com/p/custom-context-menu/
*
* --
* RightClick for Flash Player.
* Version 0.6.2
*
*/
var RightClick = {
    /**
     * Constructor
     * @param {String} flashObjectId the id of tag <code>&lt;object&gt;</code>
     * @param {String} flashObjectContainerDivId the id of the div contains the <code>&lt;object&gt;</code>
     */
    init: function(flashObjectId, flashObjectContainerDivId) {
        this.FlashObjectID = flashObjectId;
        this.FlashContainerID = flashObjectContainerDivId;
        this.Cache = this.FlashObjectID;
        if (window.addEventListener) {
            if ((/tencenttraveler/.test(navigator.userAgent.toLowerCase()))) {
                window.addEventListener("mousedown", this.onTencentTravelerMouse, true);
            } else {
                window.addEventListener("mousedown", this.onGeckoMouse, true);
            }
        } else {
            document.getElementById(this.FlashContainerID).onmouseup = function() {
                document.getElementById(RightClick.FlashContainerID).releaseCapture();
            };
            document.oncontextmenu = function() {
                if (window.event.srcElement.id == RightClick.FlashObjectID) {
                    return false;
                } else {
                    RightClick.Cache = "nan";
                    return true;
                }
            };
            document.getElementById(this.FlashContainerID).onmousedown = RightClick.onIEMouse;
        }
    },
    /**
     * GECKO / WEBKIT event overkill
     * @param {Object} eventObject
     */
    killEvents: function(eventObject) {
        if (eventObject) {
            if (eventObject.stopPropagation) eventObject.stopPropagation();
            if (eventObject.preventDefault) eventObject.preventDefault();
            if (eventObject.preventCapture) eventObject.preventCapture();
            if (eventObject.preventBubble) eventObject.preventBubble();
        }
    },
    /**
     * GECKO / WEBKIT call right click
     * @param {Object} ev
     */
    onGeckoMouse: function(ev) {
        return function(ev) {
            if (ev.button != 0) {
                RightClick.killEvents(ev);
                if(ev.target.id == RightClick.FlashObjectID && RightClick.Cache == RightClick.FlashObjectID) {
                    RightClick.call();
                }
                RightClick.Cache = ev.target.id;
            }
        };
    },
    /**
     * TencentTraveler call right click
     * @param {Object} ev
     */
    onTencentTravelerMouse: function(ev) {
        return function(ev) {
            if (ev.button == 0) {
                RightClick.killEvents(ev);
                if(ev.target.id == RightClick.FlashObjectID && RightClick.Cache == RightClick.FlashObjectID) {
                    RightClick.call();
                }
                RightClick.Cache = ev.target.id;
            }
        };
    },
    /**
     * IE call right click
     * @param {Object} ev
     */
    onIEMouse: function(ev) {
        if (ev.button != 1) {
            if(window.event.srcElement.id == RightClick.FlashObjectID && RightClick.Cache == RightClick.FlashObjectID) {
                RightClick.call();
            }
            document.getElementById(RightClick.FlashContainerID).setCapture();
            if(window.event.srcElement.id)
            RightClick.Cache = window.event.srcElement.id;
        }
    },
    /**
     * Main External Interface for Flash to call.
     * <pre>
     * There have to be an open API "rightClick" registered in FLASH for javascript to call.
     * JavaScript will
     * </pre>
     */
    call: function() {
        var getMovie = function(movieName) {
            if (navigator.appName.indexOf("Microsoft") != -1) {
                return window[movieName];
            } else {
                return document[movieName];
            }
        };
        try {
            getMovie(this.FlashObjectID).rightClick();
        } catch(err) {
            /* stop error message when flash hasn't loaded */
            if (typeof console != 'undefined') {
                console.log(err);
            }
        }
    }
};
