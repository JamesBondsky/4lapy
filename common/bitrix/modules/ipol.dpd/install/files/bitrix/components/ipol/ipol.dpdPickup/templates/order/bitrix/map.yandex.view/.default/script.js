if (!window.BX_YMapAddPlacemark)
{
	window.BX_YMapAddPlacemarkDPD = function(map, arPlacemark)
	{
		if (null == map)
			return false;

		if(!arPlacemark.LAT || !arPlacemark.LON)
			return false;

		var props = {};

		if (null != arPlacemark.TEXT && arPlacemark.TEXT.length > 0) {
			props.hintContent    = arPlacemark.TITLE;
			props.balloonContent = arPlacemark.TEXT;
		}

		var obPlacemark = new ymaps.Placemark(
			[arPlacemark.LAT, arPlacemark.LON],
			props,
			{
				balloonCloseButton: true,
				iconLayout: 'default#image',
				iconImageHref: '/bitrix/images/ipol.dpd/pickup_locationmarker.png',
				iconImageSize: [53, 53],
				iconImageOffset: [-25, -53]
			}
		);

		map.geoObjects.add(obPlacemark);

		return obPlacemark;
	}
}

if (!window.BX_YMapAddPolyline)
{
	window.BX_YMapAddPolylineDPD = function(map, arPolyline)
	{
		if (null == map)
			return false;

		if (null != arPolyline.POINTS && arPolyline.POINTS.length > 1)
		{
			var arPoints = [];
			for (var i = 0, len = arPolyline.POINTS.length; i < len; i++)
			{
				arPoints.push([arPolyline.POINTS[i].LAT, arPolyline.POINTS[i].LON]);
			}
		}
		else
		{
			return false;
		}

		var obParams = {clickable: true};
		if (null != arPolyline.STYLE)
		{
			obParams.strokeColor = arPolyline.STYLE.strokeColor;
			obParams.strokeWidth = arPolyline.STYLE.strokeWidth;
		}
		var obPolyline = new ymaps.Polyline(
			arPoints, {balloonContent: arPolyline.TITLE}, obParams
		);

		map.geoObjects.add(obPolyline);

		return obPolyline;
	}
}