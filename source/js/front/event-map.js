'use strict';

EventManagerIntegration = EventManagerIntegration || {};
EventManagerIntegration.Event = EventManagerIntegration.Event || {};

EventManagerIntegration.Event.Map = (function() {

    function Map() {
        this.init();
    }

    Map.prototype.init = function() {
        var mapElement,
            position,
            mapOptions,
            map,
            marker,
            infowindow;

        mapElement = document.getElementById('event_map');

        if (!mapElement) {
            return;
        }

        position = {
            lat: parseFloat(mapElement.getAttribute('data-lat')),
            lng: parseFloat(mapElement.getAttribute('data-lng'))
        };

        mapOptions = {
            zoom: 15,
            center: position,
            disableDefaultUI: false
        };

        map = new google.maps.Map(mapElement, mapOptions);

        var locationTitle = mapElement.getAttribute('data-title') ? mapElement.getAttribute('data-title') : '';

        infowindow = new google.maps.InfoWindow({
            content: '<b>' + locationTitle + '</b>'
        });

        marker = new google.maps.Marker({
            position: position,
            map: map
        });

        if (locationTitle) {
            marker.addListener('click', function() {
                infowindow.open(map, marker);
            });
        }
    };

    return new Map();
})();
