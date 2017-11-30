if (typeof ymaps !== 'undefined') {
    ymaps.ready(function () {
        ymaps.geolocation.get().then(function (result) {
            var geoObject = result.geoObjects.get(0);
            var city = 'Москва';
            if (!geoObject) {
                return;
            }

            var cityData = geoObject.getLocalities();
            if (!cityData || !cityData.length) {
                return;
            }

            var city = cityData[0];
            if (window.selectedCity != city) {
                setCity(city);
            }
            console.log('city found: ' + city);
        });
    });
}

function setCity(name, code) {
    var found = false;
    var $city;
    if (name) {
        $city = $('.js-city-list [data-name=' + name + ']');
    } else if (code) {
        $city = $('.js-city-list [data-code=' + code + ']');
    }

    found = !!($city && $city.length);
    if (!found) {
        return;
    }

    name = $city.attr('data-name');
}

function selectCity(name, code) {

}
