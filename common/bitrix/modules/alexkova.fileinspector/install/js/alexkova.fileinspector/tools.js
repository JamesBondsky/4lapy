var stepCont = 2;

var sessionIDS = new Array();

var dialogIDS = 0;

var oCalculateDialog = new BX.CDialog({
        title : 'Dir Size Calculation',
        height: 150,
        width: 600,
        resizable: false
});

oCalculateDialog.SetButtons([
        new BX.CWindowButton(
        {
                title: "Close",
                id: 'b_stop',
                name: 'b_stop',
                action: function(){stopCalculate(0);this.parentWindow.Close();}
        }),
]);

//oCopyDialog1.Show();

window.calculateDirSize = function(sPath)
{
       
        oCalculateDialog.SetContent('<div>Please wait...</div>'); 
        oCalculateDialog.Show();
        
        usid = getUnicumSeanceID();
        
        dialogIDS = usid;
        
        //usid = 777;
        
        window.setTimeout('calculateStep('+usid+','+"'"+sPath+"'"+')', 100);
        
}


function getUnicumSeanceID(){
    newIDS = Math.random();
    sessionIDS.push(newIDS);
    return newIDS;
}


Array.prototype.KZN_in_array = function(p_val, returnIndex) {
	for(var i = 0, l = this.length; i < l; i++)	{
		if(this[i] == p_val) {
			if (returnIndex)
                            return i;
                        else
                            return true;
		}
	}
	return false;
}


function stopCalculate(usid){
    
    if (usid == 0)
    {
        usid = dialogIDS;
    }
    
    var rt = sessionIDS.KZN_in_array(usid, true);

    if (rt>0)
    {
        sessionIDS.splice(rt,1);
    }
}

function calculateStep(usid, startpath,  bpoint, fcount, fsize){
    
    //console.log(sessionIDS);
    var res;
    BX.ajax.get('/bitrix/admin/alexkova.dirsizer_calculate.php?rmn='+Math.random()+'&usid='+usid+'&start_path='+startpath+'&fcount='+fcount+'&fsize='+fsize+'&break_point='+bpoint, '', function(res) {
        //console.log(res);
        t = JSON.parse(res);
        //console.log(t);
        fcount = t.RESULT.FCOUNT;
        fsize = t.RESULT.FSIZE;
       
       
       
        //oCalculateDialog.SetContent('<div><img src="/bitrix/images/alexkova.dirsizer/progress.gif"><div>'+t.RESULT.BREAK_POINT_TEXT+'</div>'
        //+'<div>'+t.RESULT.FCOUNT_TEXT+'</div><div>'+t.RESULT.FSIZE_TEXT+'</div><div>'+t.RESULT.TSTATE+'</div></div>');
        
        oCalculateDialog.SetContent(t.RESULT.RESULT_TEXT);
        
        if (t.RESULT.TSTATE == 'progress' && sessionIDS.KZN_in_array(usid) && window.oCalculateDialog.isOpen) 
            window.setTimeout('calculateStep('+usid+','+"'"+startpath+"','"+t.RESULT.BREAK_POINT+"'"+','+fcount+','+fsize+')', 500);
        else{
            stopCalculate(usid);
            
            oCalculateDialog.SetContent(t.RESULT.RESULT_TEXT);
        }
    });
}