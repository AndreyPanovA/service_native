(function(e){function q(){this.regional=[];this.regional[""]={currentText:"Now",closeText:"Done",amNames:["AM","A"],pmNames:["PM","P"],timeFormat:"HH:mm",timeSuffix:"",timeOnlyTitle:"Choose Time",timeText:"Time",hourText:"Hour",minuteText:"Minute",secondText:"Second",millisecText:"Millisecond",timezoneText:"Time Zone",isRTL:!1};this._defaults={showButtonPanel:!0,timeOnly:!1,showHour:!0,showMinute:!0,showSecond:!1,showMillisec:!1,showTimezone:!1,showTime:!0,stepHour:1,stepMinute:1,stepSecond:1,stepMillisec:1,
hour:0,minute:0,second:0,millisec:0,timezone:null,useLocalTimezone:!1,defaultTimezone:"+0000",hourMin:0,minuteMin:0,secondMin:0,millisecMin:0,hourMax:23,minuteMax:59,secondMax:59,millisecMax:999,minDateTime:null,maxDateTime:null,onSelect:null,hourGrid:0,minuteGrid:0,secondGrid:0,millisecGrid:0,alwaysSetTime:!0,separator:" ",altFieldTimeOnly:!0,altTimeFormat:null,altSeparator:null,altTimeSuffix:null,pickerTimeFormat:null,pickerTimeSuffix:null,showTimepicker:!0,timezoneIso8601:!1,timezoneList:null,
addSliderAccess:!1,sliderAccessArgs:null,controlType:"slider",defaultValue:null,parse:"strict"};e.extend(this._defaults,this.regional[""])}e.ui.timepicker=e.ui.timepicker||{};if(!e.ui.timepicker.version){e.extend(e.ui,{timepicker:{version:"1.1.2"}});e.extend(q.prototype,{$input:null,$altInput:null,$timeObj:null,inst:null,hour_slider:null,minute_slider:null,second_slider:null,millisec_slider:null,timezone_select:null,hour:0,minute:0,second:0,millisec:0,timezone:null,defaultTimezone:"+0000",hourMinOriginal:null,
minuteMinOriginal:null,secondMinOriginal:null,millisecMinOriginal:null,hourMaxOriginal:null,minuteMaxOriginal:null,secondMaxOriginal:null,millisecMaxOriginal:null,ampm:"",formattedDate:"",formattedTime:"",formattedDateTime:"",timezoneList:null,units:["hour","minute","second","millisec"],control:null,setDefaults:function(c){r(this._defaults,c||{});return this},_newInst:function(c,b){var a=new q,d={},f={},g,j;for(g in this._defaults)if(this._defaults.hasOwnProperty(g)){var h=c.prop("time:"+g);if(h)try{d[g]=
eval(h)}catch(l){d[g]=h}}g={beforeShow:function(b,d){if(e.isFunction(a._defaults.evnts.beforeShow))return a._defaults.evnts.beforeShow.call(c[0],b,d,a)},onChangeMonthYear:function(b,d,f){a._updateDateTime(f);e.isFunction(a._defaults.evnts.onChangeMonthYear)&&a._defaults.evnts.onChangeMonthYear.call(c[0],b,d,f,a)},onClose:function(b,d){!0===a.timeDefined&&""!==c.val()&&a._updateDateTime(d);e.isFunction(a._defaults.evnts.onClose)&&a._defaults.evnts.onClose.call(c[0],b,d,a)}};for(j in g)g.hasOwnProperty(j)&&
(f[j]=b[j]||null);a._defaults=e.extend({},this._defaults,d,b,g,{evnts:f,timepicker:a});a.amNames=e.map(a._defaults.amNames,function(a){return a.toUpperCase()});a.pmNames=e.map(a._defaults.pmNames,function(a){return a.toUpperCase()});"string"===typeof a._defaults.controlType?(void 0===e.fn[a._defaults.controlType]&&(a._defaults.controlType="select"),a.control=a._controls[a._defaults.controlType]):a.control=a._defaults.controlType;null===a._defaults.timezoneList&&(d="-1200 -1100 -1000 -0930 -0900 -0800 -0700 -0600 -0500 -0430 -0400 -0330 -0300 -0200 -0100 +0000 +0100 +0200 +0300 +0330 +0400 +0430 +0500 +0530 +0545 +0600 +0630 +0700 +0800 +0845 +0900 +0930 +1000 +1030 +1100 +1130 +1200 +1245 +1300 +1400".split(" "),
a._defaults.timezoneIso8601&&(d=e.map(d,function(a){return"+0000"==a?"Z":a.substring(0,3)+":"+a.substring(3)})),a._defaults.timezoneList=d);a.timezone=a._defaults.timezone;a.hour=a._defaults.hour<a._defaults.hourMin?a._defaults.hourMin:a._defaults.hour>a._defaults.hourMax?a._defaults.hourMax:a._defaults.hour;a.minute=a._defaults.minute<a._defaults.minuteMin?a._defaults.minuteMin:a._defaults.minute>a._defaults.minuteMax?a._defaults.minuteMax:a._defaults.minute;a.second=a._defaults.second<a._defaults.secondMin?
a._defaults.secondMin:a._defaults.second>a._defaults.secondMax?a._defaults.secondMax:a._defaults.second;a.millisec=a._defaults.millisec<a._defaults.millisecMin?a._defaults.millisecMin:a._defaults.millisec>a._defaults.millisecMax?a._defaults.millisecMax:a._defaults.millisec;a.ampm="";a.$input=c;b.altField&&(a.$altInput=e(b.altField).css({cursor:"pointer"}).focus(function(){c.trigger("focus")}));if(0===a._defaults.minDate||0===a._defaults.minDateTime)a._defaults.minDate=new Date;if(0===a._defaults.maxDate||
0===a._defaults.maxDateTime)a._defaults.maxDate=new Date;void 0!==a._defaults.minDate&&a._defaults.minDate instanceof Date&&(a._defaults.minDateTime=new Date(a._defaults.minDate.getTime()));void 0!==a._defaults.minDateTime&&a._defaults.minDateTime instanceof Date&&(a._defaults.minDate=new Date(a._defaults.minDateTime.getTime()));void 0!==a._defaults.maxDate&&a._defaults.maxDate instanceof Date&&(a._defaults.maxDateTime=new Date(a._defaults.maxDate.getTime()));void 0!==a._defaults.maxDateTime&&a._defaults.maxDateTime instanceof
Date&&(a._defaults.maxDate=new Date(a._defaults.maxDateTime.getTime()));a.$input.bind("focus",function(){a._onFocus()});return a},_addTimePicker:function(c){var b=this.$altInput&&this._defaults.altFieldTimeOnly?this.$input.val()+" "+this.$altInput.val():this.$input.val();this.timeDefined=this._parseTime(b);this._limitMinMaxDateTime(c,!1);this._injectTimePicker()},_parseTime:function(c,b){this.inst||(this.inst=e.datepicker._getInst(this.$input[0]));if(b||!this._defaults.timeOnly){var a=e.datepicker._get(this.inst,
"dateFormat");try{var d=u(a,this._defaults.timeFormat,c,e.datepicker._getFormatConfig(this.inst),this._defaults);if(!d.timeObj)return!1;e.extend(this,d.timeObj)}catch(f){return alert("Error parsing the date/time string: "+f+"\ndate/time string = "+c+"\ntimeFormat = "+this._defaults.timeFormat+"\ndateFormat = "+a),!1}}else{a=e.datepicker.parseTime(this._defaults.timeFormat,c,this._defaults);if(!a)return!1;e.extend(this,a)}return!0},_injectTimePicker:function(){var c=this.inst.dpDiv,b=this.inst.settings,
a=this,d="",f="",g={},j={},h=null;if(0===c.find("div.ui-timepicker-div").length&&b.showTimepicker){for(var h='<div class="ui-timepicker-div'+(b.isRTL?" ui-timepicker-rtl":"")+'"><dl><dt class="ui_tpicker_time_label"'+(b.showTime?"":' style="display:none;"')+">"+b.timeText+'</dt><dd class="ui_tpicker_time"'+(b.showTime?"":' style="display:none;"')+"></dd>",l=0,m=this.units.length;l<m;l++){d=this.units[l];f=d.substr(0,1).toUpperCase()+d.substr(1);g[d]=parseInt(b[d+"Max"]-(b[d+"Max"]-b[d+"Min"])%b["step"+
f],10);j[d]=0;h+='<dt class="ui_tpicker_'+d+'_label"'+(b["show"+f]?"":' style="display:none;"')+">"+b[d+"Text"]+'</dt><dd class="ui_tpicker_'+d+'"><div class="ui_tpicker_'+d+'_slider"'+(b["show"+f]?"":' style="display:none;"')+"></div>";if(b["show"+f]&&0<b[d+"Grid"]){h+='<div style="padding-left: 1px"><table class="ui-tpicker-grid-label"><tr>';if("hour"==d)for(f=b[d+"Min"];f<=g[d];f+=parseInt(b[d+"Grid"],10)){j[d]++;var k=e.datepicker.formatTime(p(b.pickerTimeFormat||b.timeFormat)?"hht":"HH",{hour:f},
b),h=h+('<td data-for="'+d+'">'+k+"</td>")}else for(f=b[d+"Min"];f<=g[d];f+=parseInt(b[d+"Grid"],10))j[d]++,h+='<td data-for="'+d+'">'+(10>f?"0":"")+f+"</td>";h+="</tr></table></div>"}h+="</dd>"}var h=h+('<dt class="ui_tpicker_timezone_label"'+(b.showTimezone?"":' style="display:none;"')+">"+b.timezoneText+"</dt>"),h=h+('<dd class="ui_tpicker_timezone" '+(b.showTimezone?"":' style="display:none;"')+"></dd>"),n=e(h+"</dl></div>");!0===b.timeOnly&&(n.prepend('<div class="ui-widget-header ui-helper-clearfix ui-corner-all"><div class="ui-datepicker-title">'+
b.timeOnlyTitle+"</div></div>"),c.find(".ui-datepicker-header, .ui-datepicker-calendar").hide());l=0;for(m=a.units.length;l<m;l++)d=a.units[l],f=d.substr(0,1).toUpperCase()+d.substr(1),a[d+"_slider"]=a.control.create(a,n.find(".ui_tpicker_"+d+"_slider"),d,a[d],b[d+"Min"],g[d],b["step"+f]),b["show"+f]&&0<b[d+"Grid"]&&(h=100*j[d]*b[d+"Grid"]/(g[d]-b[d+"Min"]),n.find(".ui_tpicker_"+d+" table").css({width:h+"%",marginLeft:b.isRTL?"0":h/(-2*j[d])+"%",marginRight:b.isRTL?h/(-2*j[d])+"%":"0",borderCollapse:"collapse"}).find("td").click(function(){var b=
e(this),c=b.html(),f=parseInt(c.replace(/[^0-9]/g),10),c=c.replace(/[^apm]/ig),b=b.data("for");"hour"==b&&(-1!==c.indexOf("p")&&12>f?f+=12:-1!==c.indexOf("a")&&12===f&&(f=0));a.control.value(a,a[b+"_slider"],d,f);a._onTimeChange();a._onSelectHandler()}).css({cursor:"pointer",width:100/j[d]+"%",textAlign:"center",overflow:"hidden"}));this.timezone_select=n.find(".ui_tpicker_timezone").append("<select></select>").find("select");e.fn.append.apply(this.timezone_select,e.map(b.timezoneList,function(a){return e("<option />").val("object"==
typeof a?a.value:a).text("object"==typeof a?a.label:a)}));"undefined"!=typeof this.timezone&&null!==this.timezone&&""!==this.timezone?e.timepicker.timeZoneOffsetString(new Date(this.inst.selectedYear,this.inst.selectedMonth,this.inst.selectedDay,12))==this.timezone?s(a):this.timezone_select.val(this.timezone):"undefined"!=typeof this.hour&&null!==this.hour&&""!==this.hour?this.timezone_select.val(b.defaultTimezone):s(a);this.timezone_select.change(function(){a._defaults.useLocalTimezone=!1;a._onTimeChange();
a._onSelectHandler()});b=c.find(".ui-datepicker-buttonpane");b.length?b.before(n):c.append(n);this.$timeObj=n.find(".ui_tpicker_time");null!==this.inst&&(c=this.timeDefined,this._onTimeChange(),this.timeDefined=c);if(this._defaults.addSliderAccess){var v=this._defaults.sliderAccessArgs,t=this._defaults.isRTL;v.isRTL=t;setTimeout(function(){if(0===n.find(".ui-slider-access").length){n.find(".ui-slider:visible").sliderAccess(v);var a=n.find(".ui-slider-access:eq(0)").outerWidth(!0);a&&n.find("table:visible").each(function(){var b=
e(this),c=b.outerWidth(),d=b.css(t?"marginRight":"marginLeft").toString().replace("%",""),f=c-a,g={width:f,marginRight:0,marginLeft:0};g[t?"marginRight":"marginLeft"]=d*f/c+"%";b.css(g)})}},10)}}},_limitMinMaxDateTime:function(c,b){var a=this._defaults,d=new Date(c.selectedYear,c.selectedMonth,c.selectedDay);if(this._defaults.showTimepicker){if(null!==e.datepicker._get(c,"minDateTime")&&void 0!==e.datepicker._get(c,"minDateTime")&&d){var f=e.datepicker._get(c,"minDateTime"),g=new Date(f.getFullYear(),
f.getMonth(),f.getDate(),0,0,0,0);if(null===this.hourMinOriginal||null===this.minuteMinOriginal||null===this.secondMinOriginal||null===this.millisecMinOriginal)this.hourMinOriginal=a.hourMin,this.minuteMinOriginal=a.minuteMin,this.secondMinOriginal=a.secondMin,this.millisecMinOriginal=a.millisecMin;c.settings.timeOnly||g.getTime()==d.getTime()?(this._defaults.hourMin=f.getHours(),this.hour<=this._defaults.hourMin?(this.hour=this._defaults.hourMin,this._defaults.minuteMin=f.getMinutes(),this.minute<=
this._defaults.minuteMin?(this.minute=this._defaults.minuteMin,this._defaults.secondMin=f.getSeconds(),this.second<=this._defaults.secondMin?(this.second=this._defaults.secondMin,this._defaults.millisecMin=f.getMilliseconds()):(this.millisec<this._defaults.millisecMin&&(this.millisec=this._defaults.millisecMin),this._defaults.millisecMin=this.millisecMinOriginal)):(this._defaults.secondMin=this.secondMinOriginal,this._defaults.millisecMin=this.millisecMinOriginal)):(this._defaults.minuteMin=this.minuteMinOriginal,
this._defaults.secondMin=this.secondMinOriginal,this._defaults.millisecMin=this.millisecMinOriginal)):(this._defaults.hourMin=this.hourMinOriginal,this._defaults.minuteMin=this.minuteMinOriginal,this._defaults.secondMin=this.secondMinOriginal,this._defaults.millisecMin=this.millisecMinOriginal)}if(null!==e.datepicker._get(c,"maxDateTime")&&void 0!==e.datepicker._get(c,"maxDateTime")&&d){f=e.datepicker._get(c,"maxDateTime");g=new Date(f.getFullYear(),f.getMonth(),f.getDate(),0,0,0,0);if(null===this.hourMaxOriginal||
null===this.minuteMaxOriginal||null===this.secondMaxOriginal)this.hourMaxOriginal=a.hourMax,this.minuteMaxOriginal=a.minuteMax,this.secondMaxOriginal=a.secondMax,this.millisecMaxOriginal=a.millisecMax;c.settings.timeOnly||g.getTime()==d.getTime()?(this._defaults.hourMax=f.getHours(),this.hour>=this._defaults.hourMax?(this.hour=this._defaults.hourMax,this._defaults.minuteMax=f.getMinutes(),this.minute>=this._defaults.minuteMax?(this.minute=this._defaults.minuteMax,this._defaults.secondMax=f.getSeconds(),
this.second>=this._defaults.secondMax?(this.second=this._defaults.secondMax,this._defaults.millisecMax=f.getMilliseconds()):(this.millisec>this._defaults.millisecMax&&(this.millisec=this._defaults.millisecMax),this._defaults.millisecMax=this.millisecMaxOriginal)):(this._defaults.secondMax=this.secondMaxOriginal,this._defaults.millisecMax=this.millisecMaxOriginal)):(this._defaults.minuteMax=this.minuteMaxOriginal,this._defaults.secondMax=this.secondMaxOriginal,this._defaults.millisecMax=this.millisecMaxOriginal)):
(this._defaults.hourMax=this.hourMaxOriginal,this._defaults.minuteMax=this.minuteMaxOriginal,this._defaults.secondMax=this.secondMaxOriginal,this._defaults.millisecMax=this.millisecMaxOriginal)}void 0!==b&&!0===b&&(a=parseInt(this._defaults.hourMax-(this._defaults.hourMax-this._defaults.hourMin)%this._defaults.stepHour,10),d=parseInt(this._defaults.minuteMax-(this._defaults.minuteMax-this._defaults.minuteMin)%this._defaults.stepMinute,10),f=parseInt(this._defaults.secondMax-(this._defaults.secondMax-
this._defaults.secondMin)%this._defaults.stepSecond,10),g=parseInt(this._defaults.millisecMax-(this._defaults.millisecMax-this._defaults.millisecMin)%this._defaults.stepMillisec,10),this.hour_slider&&(this.control.options(this,this.hour_slider,"hour",{min:this._defaults.hourMin,max:a}),this.control.value(this,this.hour_slider,"hour",this.hour-this.hour%this._defaults.stepHour)),this.minute_slider&&(this.control.options(this,this.minute_slider,"minute",{min:this._defaults.minuteMin,max:d}),this.control.value(this,
this.minute_slider,"minute",this.minute-this.minute%this._defaults.stepMinute)),this.second_slider&&(this.control.options(this,this.second_slider,"second",{min:this._defaults.secondMin,max:f}),this.control.value(this,this.second_slider,"second",this.second-this.second%this._defaults.stepSecond)),this.millisec_slider&&(this.control.options(this,this.millisec_slider,"millisec",{min:this._defaults.millisecMin,max:g}),this.control.value(this,this.millisec_slider,"millisec",this.millisec-this.millisec%
this._defaults.stepMillisec)))}},_onTimeChange:function(){var c=this.hour_slider?this.control.value(this,this.hour_slider,"hour"):!1,b=this.minute_slider?this.control.value(this,this.minute_slider,"minute"):!1,a=this.second_slider?this.control.value(this,this.second_slider,"second"):!1,d=this.millisec_slider?this.control.value(this,this.millisec_slider,"millisec"):!1,f=this.timezone_select?this.timezone_select.val():!1,g=this._defaults,j=g.pickerTimeFormat||g.timeFormat,h=g.pickerTimeSuffix||g.timeSuffix;
"object"==typeof c&&(c=!1);"object"==typeof b&&(b=!1);"object"==typeof a&&(a=!1);"object"==typeof d&&(d=!1);"object"==typeof f&&(f=!1);!1!==c&&(c=parseInt(c,10));!1!==b&&(b=parseInt(b,10));!1!==a&&(a=parseInt(a,10));!1!==d&&(d=parseInt(d,10));var l=g[12>c?"amNames":"pmNames"][0],m=c!=this.hour||b!=this.minute||a!=this.second||d!=this.millisec||0<this.ampm.length&&12>c!=(-1!==e.inArray(this.ampm.toUpperCase(),this.amNames))||null===this.timezone&&f!=this.defaultTimezone||null!==this.timezone&&f!=this.timezone;
m&&(!1!==c&&(this.hour=c),!1!==b&&(this.minute=b),!1!==a&&(this.second=a),!1!==d&&(this.millisec=d),!1!==f&&(this.timezone=f),this.inst||(this.inst=e.datepicker._getInst(this.$input[0])),this._limitMinMaxDateTime(this.inst,!0));p(g.timeFormat)&&(this.ampm=l);this.formattedTime=e.datepicker.formatTime(g.timeFormat,this,g);this.$timeObj&&(j===g.timeFormat?this.$timeObj.text(this.formattedTime+h):this.$timeObj.text(e.datepicker.formatTime(j,this,g)+h));this.timeDefined=!0;m&&this._updateDateTime()},
_onSelectHandler:function(){var c=this._defaults.onSelect||this.inst.settings.onSelect,b=this.$input?this.$input[0]:null;c&&b&&c.apply(b,[this.formattedDateTime,this])},_updateDateTime:function(c){c=this.inst||c;var b=e.datepicker._daylightSavingAdjust(new Date(c.selectedYear,c.selectedMonth,c.selectedDay)),a=e.datepicker._get(c,"dateFormat");c=e.datepicker._getFormatConfig(c);var d=null!==b&&this.timeDefined,a=this.formattedDate=e.datepicker.formatDate(a,null===b?new Date:b,c);if(!0===this._defaults.timeOnly)a=
this.formattedTime;else if(!0!==this._defaults.timeOnly&&(this._defaults.alwaysSetTime||d))a+=this._defaults.separator+this.formattedTime+this._defaults.timeSuffix;this.formattedDateTime=a;if(this._defaults.showTimepicker)if(this.$altInput&&!0===this._defaults.altFieldTimeOnly)this.$altInput.val(this.formattedTime),this.$input.val(this.formattedDate);else if(this.$altInput){this.$input.val(a);var a="",d=this._defaults.altSeparator?this._defaults.altSeparator:this._defaults.separator,f=this._defaults.altTimeSuffix?
this._defaults.altTimeSuffix:this._defaults.timeSuffix;(a=this._defaults.altFormat?e.datepicker.formatDate(this._defaults.altFormat,null===b?new Date:b,c):this.formattedDate)&&(a+=d);a=this._defaults.altTimeFormat?a+(e.datepicker.formatTime(this._defaults.altTimeFormat,this,this._defaults)+f):a+(this.formattedTime+f);this.$altInput.val(a)}else this.$input.val(a);else this.$input.val(this.formattedDate);this.$input.trigger("change")},_onFocus:function(){if(!this.$input.val()&&this._defaults.defaultValue){this.$input.val(this._defaults.defaultValue);
var c=e.datepicker._getInst(this.$input.get(0)),b=e.datepicker._get(c,"timepicker");if(b&&b._defaults.timeOnly&&c.input.val()!=c.lastVal)try{e.datepicker._updateDatepicker(c)}catch(a){console.log(a)}}},_controls:{slider:{create:function(c,b,a,d,f,g,j){var h=c._defaults.isRTL;return b.prop("slide",null).slider({orientation:"horizontal",value:h?-1*d:d,min:h?-1*g:f,max:h?-1*f:g,step:j,slide:function(b,d){c.control.value(c,e(this),a,h?-1*d.value:d.value);c._onTimeChange()},stop:function(){c._onSelectHandler()}})},
options:function(c,b,a,d,e){if(c._defaults.isRTL){if("string"==typeof d)return"min"==d||"max"==d?void 0!==e?b.slider(d,-1*e):Math.abs(b.slider(d)):b.slider(d);c=d.min;a=d.max;d.min=d.max=null;void 0!==c&&(d.max=-1*c);void 0!==a&&(d.min=-1*a);return b.slider(d)}return"string"==typeof d&&void 0!==e?b.slider(d,e):b.slider(d)},value:function(c,b,a,d){return c._defaults.isRTL?void 0!==d?b.slider("value",-1*d):Math.abs(b.slider("value")):void 0!==d?b.slider("value",d):b.slider("value")}},select:{create:function(c,
b,a,d,f,g,j){var h='<select class="ui-timepicker-select" data-unit="'+a+'" data-min="'+f+'" data-max="'+g+'" data-step="'+j+'">';for(c._defaults.timeFormat.indexOf("t");f<=g;f+=j)h+='<option value="'+f+'"'+(f==d?" selected":"")+">",h="hour"==a&&p(c._defaults.pickerTimeFormat||c._defaults.timeFormat)?h+e.datepicker.formatTime("hh TT",{hour:f},c._defaults):"millisec"==a||10<=f?h+f:h+("0"+f.toString()),h+="</option>";h+="</select>";b.children("select").remove();e(h).appendTo(b).change(function(){c._onTimeChange();
c._onSelectHandler()});return b},options:function(c,b,a,d,e){a={};var g=b.children("select");if("string"==typeof d){if(void 0===e)return g.data(d);a[d]=e}else a=d;return c.control.create(c,b,g.data("unit"),g.val(),a.min||g.data("min"),a.max||g.data("max"),a.step||g.data("step"))},value:function(c,b,a,d){c=b.children("select");return void 0!==d?c.val(d):c.val()}}}});e.fn.extend({timepicker:function(c){c=c||{};var b=Array.prototype.slice.call(arguments);"object"==typeof c&&(b[0]=e.extend(c,{timeOnly:!0}));
return e(this).each(function(){e.fn.datetimepicker.apply(e(this),b)})},datetimepicker:function(c){c=c||{};var b=arguments;return"string"==typeof c?"getDate"==c?e.fn.datepicker.apply(e(this[0]),b):this.each(function(){var a=e(this);a.datepicker.apply(a,b)}):this.each(function(){var a=e(this);a.datepicker(e.timepicker._newInst(a,c)._defaults)})}});e.datepicker.parseDateTime=function(c,b,a,d,e){c=u(c,b,a,d,e);c.timeObj&&(b=c.timeObj,c.date.setHours(b.hour,b.minute,b.second,b.millisec));return c.date};
e.datepicker.parseTime=function(c,b,a){a=r(r({},e.timepicker._defaults),a||{});var d=function(a,b,c){var d="^"+a.toString().replace(/([hH]{1,2}|mm?|ss?|[tT]{1,2}|[lz]|'.*?')/g,function(a){var b=a.length;switch(a.charAt(0).toLowerCase()){case "h":return 1===b?"(\\d?\\d)":"(\\d{"+b+"})";case "m":return 1===b?"(\\d?\\d)":"(\\d{"+b+"})";case "s":return 1===b?"(\\d?\\d)":"(\\d{"+b+"})";case "l":return"(\\d?\\d?\\d)";case "z":return"(z|[-+]\\d\\d:?\\d\\d|\\S+)?";case "t":a=c.amNames;var b=c.pmNames,d=[];
a&&e.merge(d,a);b&&e.merge(d,b);d=e.map(d,function(a){return a.replace(/[.*+?|()\[\]{}\\]/g,"\\$&")});return"("+d.join("|")+")?";default:return"("+a.replace(/\'/g,"").replace(/(\.|\$|\^|\\|\/|\(|\)|\[|\]|\?|\+|\*)/g,function(a){return"\\"+a})+")?"}}).replace(/\s/g,"\\s?")+c.timeSuffix+"$",f=a.toLowerCase().match(/(h{1,2}|m{1,2}|s{1,2}|l{1}|t{1,2}|z|'.*?')/g);a={h:-1,m:-1,s:-1,l:-1,t:-1,z:-1};if(f)for(var g=0;g<f.length;g++)-1==a[f[g].toString().charAt(0)]&&(a[f[g].toString().charAt(0)]=g+1);f="";
d=b.match(RegExp(d,"i"));b={hour:0,minute:0,second:0,millisec:0};if(d){-1!==a.t&&(void 0===d[a.t]||0===d[a.t].length?(f="",b.ampm=""):(f=-1!==e.inArray(d[a.t].toUpperCase(),c.amNames)?"AM":"PM",b.ampm=c["AM"==f?"amNames":"pmNames"][0]));-1!==a.h&&(b.hour="AM"==f&&"12"==d[a.h]?0:"PM"==f&&"12"!=d[a.h]?parseInt(d[a.h],10)+12:Number(d[a.h]));-1!==a.m&&(b.minute=Number(d[a.m]));-1!==a.s&&(b.second=Number(d[a.s]));-1!==a.l&&(b.millisec=Number(d[a.l]));if(-1!==a.z&&void 0!==d[a.z]){a=d[a.z].toUpperCase();
switch(a.length){case 1:a=c.timezoneIso8601?"Z":"+0000";break;case 5:c.timezoneIso8601&&(a="0000"==a.substring(1)?"Z":a.substring(0,3)+":"+a.substring(3));break;case 6:c.timezoneIso8601?"00:00"==a.substring(1)&&(a="Z"):a="Z"==a||"00:00"==a.substring(1)?"+0000":a.replace(/:/,"")}b.timezone=a}return b}return!1};if("function"===typeof a.parse)return a.parse(c,b,a);if("loose"===a.parse){var f;a:{try{var g=new Date("2012-01-01 "+b);if(isNaN(g.getTime())&&(g=new Date("2012-01-01T"+b),isNaN(g.getTime())&&
(g=new Date("01/01/2012 "+b),isNaN(g.getTime()))))throw"Unable to parse time with native Date: "+b;f={hour:g.getHours(),minute:g.getMinutes(),second:g.getSeconds(),millisec:g.getMilliseconds(),timezone:e.timepicker.timeZoneOffsetString(g)};break a}catch(j){try{f=d(c,b,a);break a}catch(h){console.log("Unable to parse \ntimeString: "+b+"\ntimeFormat: "+c)}}f=!1}return f}return d(c,b,a)};e.datepicker.formatTime=function(c,b,a){a=a||{};a=e.extend({},e.timepicker._defaults,a);b=e.extend({hour:0,minute:0,
second:0,millisec:0,timezone:"+0000"},b);var d=a.amNames[0],f=parseInt(b.hour,10);11<f&&(d=a.pmNames[0]);c=c.replace(/(?:HH?|hh?|mm?|ss?|[tT]{1,2}|[lz]|('.*?'|".*?"))/g,function(c){switch(c){case "HH":return("0"+f).slice(-2);case "H":return f;case "hh":return("0"+w(f)).slice(-2);case "h":return w(f);case "mm":return("0"+b.minute).slice(-2);case "m":return b.minute;case "ss":return("0"+b.second).slice(-2);case "s":return b.second;case "l":return("00"+b.millisec).slice(-3);case "z":return null===b.timezone?
a.defaultTimezone:b.timezone;case "T":return d.charAt(0).toUpperCase();case "TT":return d.toUpperCase();case "t":return d.charAt(0).toLowerCase();case "tt":return d.toLowerCase();default:return c.replace(/\'/g,"")||"'"}});return c=e.trim(c)};e.datepicker._base_selectDate=e.datepicker._selectDate;e.datepicker._selectDate=function(c,b){var a=this._getInst(e(c)[0]),d=this._get(a,"timepicker");d?(d._limitMinMaxDateTime(a,!0),a.inline=a.stay_open=!0,this._base_selectDate(c,b),a.inline=a.stay_open=!1,this._notifyChange(a),
this._updateDatepicker(a)):this._base_selectDate(c,b)};e.datepicker._base_updateDatepicker=e.datepicker._updateDatepicker;e.datepicker._updateDatepicker=function(c){var b=c.input[0];if(!e.datepicker._curInst||!(e.datepicker._curInst!=c&&e.datepicker._datepickerShowing&&e.datepicker._lastInput!=b))if("boolean"!==typeof c.stay_open||!1===c.stay_open)this._base_updateDatepicker(c),(b=this._get(c,"timepicker"))&&b._addTimePicker(c)};e.datepicker._base_doKeyPress=e.datepicker._doKeyPress;e.datepicker._doKeyPress=
function(c){var b=e.datepicker._getInst(c.target),a=e.datepicker._get(b,"timepicker");if(a&&e.datepicker._get(b,"constrainInput")){var d=p(a._defaults.timeFormat),b=e.datepicker._possibleChars(e.datepicker._get(b,"dateFormat")),a=a._defaults.timeFormat.toString().replace(/[hms]/g,"").replace(/TT/g,d?"APM":"").replace(/Tt/g,d?"AaPpMm":"").replace(/tT/g,d?"AaPpMm":"").replace(/T/g,d?"AP":"").replace(/tt/g,d?"apm":"").replace(/t/g,d?"ap":"")+" "+a._defaults.separator+a._defaults.timeSuffix+(a._defaults.showTimezone?
a._defaults.timezoneList.join(""):"")+a._defaults.amNames.join("")+a._defaults.pmNames.join("")+b,d=String.fromCharCode(void 0===c.charCode?c.keyCode:c.charCode);return c.ctrlKey||" ">d||!b||-1<a.indexOf(d)}return e.datepicker._base_doKeyPress(c)};e.datepicker._base_updateAlternate=e.datepicker._updateAlternate;e.datepicker._updateAlternate=function(c){var b=this._get(c,"timepicker");if(b){var a=b._defaults.altField;if(a){var d=this._getDate(c);c=e.datepicker._getFormatConfig(c);var f,g=b._defaults.altSeparator?
b._defaults.altSeparator:b._defaults.separator;f=b._defaults.altTimeSuffix?b._defaults.altTimeSuffix:b._defaults.timeSuffix;f=""+(e.datepicker.formatTime(null!==b._defaults.altTimeFormat?b._defaults.altTimeFormat:b._defaults.timeFormat,b,b._defaults)+f);!b._defaults.timeOnly&&(!b._defaults.altFieldTimeOnly&&null!==d)&&(f=b._defaults.altFormat?e.datepicker.formatDate(b._defaults.altFormat,d,c)+g+f:b.formattedDate+g+f);e(a).val(f)}}else e.datepicker._base_updateAlternate(c)};e.datepicker._base_doKeyUp=
e.datepicker._doKeyUp;e.datepicker._doKeyUp=function(c){var b=e.datepicker._getInst(c.target),a=e.datepicker._get(b,"timepicker");if(a&&a._defaults.timeOnly&&b.input.val()!=b.lastVal)try{e.datepicker._updateDatepicker(b)}catch(d){console.log(d)}return e.datepicker._base_doKeyUp(c)};e.datepicker._base_gotoToday=e.datepicker._gotoToday;e.datepicker._gotoToday=function(c){var b=this._getInst(e(c)[0]),a=b.dpDiv;this._base_gotoToday(c);c=this._get(b,"timepicker");s(c);this._setTime(b,new Date);e(".ui-datepicker-today",
a).click()};e.datepicker._disableTimepickerDatepicker=function(c){var b=this._getInst(c);if(b){var a=this._get(b,"timepicker");e(c).datepicker("getDate");a&&(a._defaults.showTimepicker=!1,a._updateDateTime(b))}};e.datepicker._enableTimepickerDatepicker=function(c){var b=this._getInst(c);if(b){var a=this._get(b,"timepicker");e(c).datepicker("getDate");a&&(a._defaults.showTimepicker=!0,a._addTimePicker(b),a._updateDateTime(b))}};e.datepicker._setTime=function(c,b){var a=this._get(c,"timepicker");if(a){var d=
a._defaults;a.hour=b?b.getHours():d.hour;a.minute=b?b.getMinutes():d.minute;a.second=b?b.getSeconds():d.second;a.millisec=b?b.getMilliseconds():d.millisec;a._limitMinMaxDateTime(c,!0);a._onTimeChange();a._updateDateTime(c)}};e.datepicker._setTimeDatepicker=function(c,b,a){if(c=this._getInst(c)){var d=this._get(c,"timepicker");d&&(this._setDateFromField(c),b&&("string"==typeof b?(d._parseTime(b,a),b=new Date,b.setHours(d.hour,d.minute,d.second,d.millisec)):b=new Date(b.getTime()),"Invalid Date"==b.toString()&&
(b=void 0),this._setTime(c,b)))}};e.datepicker._base_setDateDatepicker=e.datepicker._setDateDatepicker;e.datepicker._setDateDatepicker=function(c,b){var a=this._getInst(c);if(a){var d=b instanceof Date?new Date(b.getTime()):b;this._updateDatepicker(a);this._base_setDateDatepicker.apply(this,arguments);this._setTimeDatepicker(c,d,!0)}};e.datepicker._base_getDateDatepicker=e.datepicker._getDateDatepicker;e.datepicker._getDateDatepicker=function(c,b){var a=this._getInst(c);if(a){var d=this._get(a,"timepicker");
return d?(void 0===a.lastVal&&this._setDateFromField(a,b),(a=this._getDate(a))&&d._parseTime(e(c).val(),d.timeOnly)&&a.setHours(d.hour,d.minute,d.second,d.millisec),a):this._base_getDateDatepicker(c,b)}};e.datepicker._base_parseDate=e.datepicker.parseDate;e.datepicker.parseDate=function(c,b,a){var d;try{d=this._base_parseDate(c,b,a)}catch(f){d=this._base_parseDate(c,b.substring(0,b.length-(f.length-f.indexOf(":")-2)),a),console.log("Error parsing the date string: "+f+"\ndate string = "+b+"\ndate format = "+
c)}return d};e.datepicker._base_formatDate=e.datepicker._formatDate;e.datepicker._formatDate=function(c){var b=this._get(c,"timepicker");return b?(b._updateDateTime(c),b.$input.val()):this._base_formatDate(c)};e.datepicker._base_optionDatepicker=e.datepicker._optionDatepicker;e.datepicker._optionDatepicker=function(c,b,a){var d=this._getInst(c),f;if(!d)return null;if(d=this._get(d,"timepicker")){var g=null,j=null,h=null,l=d._defaults.evnts,m={},k;if("string"==typeof b)if("minDate"===b||"minDateTime"===
b)g=a;else if("maxDate"===b||"maxDateTime"===b)j=a;else if("onSelect"===b)h=a;else{if(l.hasOwnProperty(b)){if("undefined"===typeof a)return l[b];m[b]=a;f={}}}else if("object"==typeof b)for(k in b.minDate?g=b.minDate:b.minDateTime?g=b.minDateTime:b.maxDate?j=b.maxDate:b.maxDateTime&&(j=b.maxDateTime),l)l.hasOwnProperty(k)&&b[k]&&(m[k]=b[k]);for(k in m)m.hasOwnProperty(k)&&(l[k]=m[k],f||(f=e.extend({},b)),delete f[k]);if(k=f)a:{k=f;for(var n in k)if(k.hasOwnProperty(k)){k=!1;break a}k=!0}if(k)return;
g?(g=0===g?new Date:new Date(g),d._defaults.minDate=g,d._defaults.minDateTime=g):j?(j=0===j?new Date:new Date(j),d._defaults.maxDate=j,d._defaults.maxDateTime=j):h&&(d._defaults.onSelect=h)}return void 0===a?this._base_optionDatepicker.call(e.datepicker,c,b):this._base_optionDatepicker.call(e.datepicker,c,f||b,a)};var r=function(c,b){e.extend(c,b);for(var a in b)if(null===b[a]||void 0===b[a])c[a]=b[a];return c},p=function(c){return-1!==c.indexOf("t")&&-1!==c.indexOf("h")},w=function(c){12<c&&(c-=
12);0==c&&(c=12);return String(c)},u=function(c,b,a,d,f){var g;a:{try{var j=f&&f.separator?f.separator:e.timepicker._defaults.separator,h=(f&&f.timeFormat?f.timeFormat:e.timepicker._defaults.timeFormat).split(j).length,l=a.split(j),m=l.length;if(1<m){g=[l.splice(0,m-h).join(j),l.splice(0,h).join(j)];break a}}catch(k){if(console.log("Could not split the date from the time. Please check the following datetimepicker options\nthrown error: "+k+"\ndateTimeString"+a+"\ndateFormat = "+c+"\nseparator = "+
f.separator+"\ntimeFormat = "+f.timeFormat),0<=k.indexOf(":")){g=a.length-(k.length-k.indexOf(":")-2);a.substring(g);g=[e.trim(a.substring(0,g)),e.trim(a.substring(g))];break a}else throw k;}g=[a,""]}c=e.datepicker._base_parseDate(c,g[0],d);if(""!==g[1]){b=e.datepicker.parseTime(b,g[1],f);if(null===b)throw"Wrong time format";return{date:c,timeObj:b}}return{date:c}},s=function(c,b){if(c&&c.timezone_select){c._defaults.useLocalTimezone=!0;var a=e.timepicker.timeZoneOffsetString("undefined"!==typeof b?
b:new Date);c._defaults.timezoneIso8601&&(a=a.substring(0,3)+":"+a.substring(3));c.timezone_select.val(a)}};e.timepicker=new q;e.timepicker.timeZoneOffsetString=function(c){c=-1*c.getTimezoneOffset();var b=c%60;return(0<=c?"+":"-")+("0"+(101*((c-b)/60)).toString()).slice(-2)+("0"+(101*b).toString()).slice(-2)};e.timepicker.timeRange=function(c,b,a){return e.timepicker.handleRange("timepicker",c,b,a)};e.timepicker.dateTimeRange=function(c,b,a){e.timepicker.dateRange(c,b,a,"datetimepicker")};e.timepicker.dateRange=
function(c,b,a,d){e.timepicker.handleRange(d||"datepicker",c,b,a)};e.timepicker.handleRange=function(c,b,a,d){function f(c,d,e){d.val()&&new Date(b.val())>new Date(a.val())&&d.val(e)}function g(a,b,d){e(a).val()&&(a=e(a)[c].call(e(a),"getDate"),a.getTime&&e(b)[c].call(e(b),"option",d,a))}e.fn[c].call(b,e.extend({onClose:function(b){f(this,a,b)},onSelect:function(){g(this,a,"minDate")}},d,d.start));e.fn[c].call(a,e.extend({onClose:function(a){f(this,b,a)},onSelect:function(){g(this,b,"maxDate")}},
d,d.end));"timepicker"!=c&&d.reformat&&e([b,a]).each(function(){var a=e(this)[c].call(e(this),"option","dateFormat"),b=new Date(e(this).val());e(this).val()&&b&&e(this).val(e.datepicker.formatDate(a,b))});f(b,a,b.val());g(b,a,"minDate");g(a,b,"maxDate");return e([b.get(0),a.get(0)])};e.timepicker.version="1.1.2"}})(jQuery);

$.datepicker.regional['ru'] = {
	closeText: 'Закрыть',
	prevText: '<Пред',
	nextText: 'След>',
	currentText: 'Сегодня',
	monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
	'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
	monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
	'Июл','Авг','Сен','Окт','Ноя','Дек'],
	dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
	dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
	dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
	weekHeader: 'Не',
	dateFormat: 'dd.mm.yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['ru']);

$.timepicker.regional['ru'] = {
	timeOnlyTitle: 'Выберите время',
	timeText: 'Время',
	hourText: 'Часы',
	minuteText: 'Минуты',
	secondText: 'Секунды',
	millisecText: 'Миллисекунды',
	timezoneText: 'Часовой пояс',
	currentText: 'Сейчас',
	closeText: 'Закрыть',
	timeFormat: 'HH:mm',
	amNames: ['AM', 'A'],
	pmNames: ['PM', 'P'],
	isRTL: false
};
$.timepicker.setDefaults($.timepicker.regional['ru']);

$.datepicker.set_ts = function(t,input) {
	var $i = $(input),
		next = $i.data('next'),
		$next_input = $i.nextAll('.hasDatepicker:first'),
		val = '',
		defaultDate = null

	if (t.length) {
		val += t[0] + '-' + (t[1]+1) + '-' + t[2]
		defaultDate = t[2]+'.'+(t[1]+1)+'.'+t[0]
		t.length > 3 && (val += ' ' + t[3] + ':' + t[4] + ':' + t[5])
	}

	$i.next().val(val).change()
	next && $(next).select()
	$next_input.length && $next_input.datepicker('option','defaultDate',defaultDate)

}