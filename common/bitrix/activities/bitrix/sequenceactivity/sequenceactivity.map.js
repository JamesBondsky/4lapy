{"version":3,"file":"sequenceactivity.min.js","sources":["sequenceactivity.js"],"names":["_SequenceActivityCurClick","_SequenceActivityClick","act_i","i","AddActivity","CreateActivity","Properties","Title","HTMLEncode","arAllActivities","Type","Children","_SequenceActivityMyActivityClick","isn","arUserParams","BX","type","isArray","SequenceActivity","ob","BizProcActivity","childsContainer","iHead","LineMouseOver","e","this","parentNode","style","backgroundImage","LineMouseOut","OnClick","jsMnu_WFAct","groupId","oSubMenu","arAllActGroups","activityGroupId","rootActivity","push","ICON","TEXT","ONCLICK","ind","length","MENU","hasOwnProperty","icon","name","BPMESS","window","jsPopup_WFAct","PopupHide","PopupMenu","ShowMenu","lastDrop","ondragging","X","Y","childActivities","arrow","rows","cells","childNodes","pos","left","right","top","bottom","onmouseover","onmouseout","h1id","DragNDrop","AddHandler","ondrop","oActivity","obj","parentActivity","ctrlKey","pa","Name","d","s","alert","deleteRow","j","pop","h2id","ActivityRemoveChild","RemoveChild","ch","onclick","RemoveResources","self","RemoveHandler","removeChild","c","insertRow","insertCell","align","vAlign","Draw","CreateLine","BPTemplateIsModified","height","background","appendChild","document","createElement","src","width","ActivityDraw","container","_crt","className","id","parseInt","AfterSDraw"],"mappings":"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"}