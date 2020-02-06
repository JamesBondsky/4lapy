{"version":3,"sources":["spotlight.js"],"names":["BX","namespace","SpotLight","options","this","container","popup","type","isDomNode","renderTo","Error","top","isNumber","left","lightMode","isBoolean","remind","content","prototype","getRenderTo","getDomElement","pos","getPopup","PopupWindow","util","getRandomString","className","angle","position","offset","autoHide","overlay","events","onPopupClose","close","onCustomEvent","bind","buttons","PopupWindowCustomButton","text","message","click","createContainer","create","attrs","style","mouseenter","show","adjustPosition","x","y","window","addCustomEvent","document","body","appendChild","destroy","remove"],"mappings":"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","file":""}