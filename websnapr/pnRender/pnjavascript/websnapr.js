/*
        A link preview system that uses the rather good snapshot service available
        from websnapr.com to display thumbnail previews.

        References:
        
        Wesnapr: http://www.websnapr.com
        Dustan Diaz: http://www.dustindiaz.com/sweet-titles-finalized
        Arc90: http://lab.arc90.com/2006/07/link_thumbnail.php
*/
var webSnapr = {
        x:0,
        y:0,
        obj:{},
        img:null,
        lnk:null,
        timer:null,
        opacityTimer:null,
        errorTimer:null,
        hidden:true,
        linkPool: {},
        //baseURI: "./",
        imageCache: [],
        init: function() {
                var lnks = document.getElementsByTagName('a');
                var i = lnks.length || 0;
                var cnt = 0;
                while(i--) {
                        //if(lnks[i].className && lnks[i].className.search(/websnapr/) != -1) {
                        if(webSnapr.isexternal(lnks[i])==true) {
                                webSnapr.addEvent(lnks[i], ["focus", "mouseover"], webSnapr.initThumb);
                                webSnapr.addEvent(lnks[i], ["blur",  "mouseout"],  webSnapr.hideThumb);
                                webSnapr.linkPool[lnks[i].href] = cnt++;
                        }
                }
                if(cnt) {
                        webSnapr.preloadImages();
                        webSnapr.obj = document.createElement('div');

                        webSnapr.ind = document.createElement('div');
                        //Safari could require this ? div.appendChild(document.createTextNode(String.fromCharCode(160)));
                        webSnapr.ind.className= "imageLoaded";
                        webSnapr.img = document.createElement('img');
                        webSnapr.img.alt = "preview";
                        webSnapr.addEvent(webSnapr.img, ["load"], webSnapr.imageLoaded);
                        webSnapr.addEvent(webSnapr.img, ["error"], webSnapr.imageError);
                        webSnapr.obj.id = "fdImageThumb";
                        webSnapr.obj.style.visibility = "hidden";
                        webSnapr.obj.style.top = "0";
                        webSnapr.obj.style.left = "0";
                        webSnapr.addEvent(webSnapr.img, ["mouseout"],  webSnapr.hideThumb);
                        webSnapr.obj.appendChild(webSnapr.ind);
                        webSnapr.obj.appendChild(webSnapr.img);
                        document.getElementsByTagName('body')[0].appendChild(webSnapr.obj);
                }
        },
        isexternal: function(host) {
	        if (host == "") return false;
	        var httptest = /^http/i;
	        if(httptest.test(host)) {
	            var expr = new RegExp(webSnapr.baseURL, "i");
	            if (expr.test(host)) return false;
	            return true;
            }
            return false;
        },
        preloadImages: function() {
                var imgList = ["lt.png", "lb.png", "rt.png", "rb.png", "error.gif", "loading.jpg"];
                var imgObj  = document.createElement('img');

                for(var i = 0, img; img = imgList[i]; i++) {
                        webSnapr.imageCache[i] = imgObj.cloneNode(false);
                        webSnapr.imageCache[i].src = webSnapr.baseURI + img;
                }
        },
        imageLoaded: function() {
                if(webSnapr.errorTimer) clearTimeout(webSnapr.errorTimer);
                if(!webSnapr.hidden) webSnapr.img.style.visibility = "visible";
                webSnapr.ind.className= "imageLoaded";
                webSnapr.ind.style.visibility = "hidden";
        },
        imageError: function(e) {
                if(webSnapr.errorTimer) clearTimeout(webSnapr.errorTimer);
                webSnapr.ind.className= "imageError";
                webSnapr.errorTimer = window.setTimeout("webSnapr.hideThumb()",2000);
        },
        initThumb: function(e) {
                e = e || event;

                webSnapr.lnk       = this;
                var positionClass       = "left";

                var heightIndent;
                var indentX = 0;
                var indentY = 0;
                
                if(String(e.type).toLowerCase().search(/mouseover/) != -1) {
                        if (document.captureEvents) {
                                webSnapr.x = e.pageX;
                                webSnapr.y = e.pageY;
                        } else if ( window.event.clientX ) {
                                webSnapr.x = window.event.clientX+document.documentElement.scrollLeft;
                                webSnapr.y = window.event.clientY+document.documentElement.scrollTop;
                        }
                        indentX = 10;
                        heightIndent = parseInt(webSnapr.y-(webSnapr.obj.offsetHeight))+'px';
                } else {
                        var obj = this;
                        var curleft = curtop = 0;
                        if (obj.offsetParent) {
                                curleft = obj.offsetLeft;
                                curtop = obj.offsetTop;
                                while (obj = obj.offsetParent) {
                                        curleft += obj.offsetLeft;
                                        curtop += obj.offsetTop;
                                }
                        }
                        curtop += this.offsetHeight;

                        webSnapr.x = curleft;
                        webSnapr.y = curtop;

                        heightIndent = parseInt(webSnapr.y-(webSnapr.obj.offsetHeight)-this.offsetHeight)+'px';
                }
                
                if ( parseInt(document.documentElement.clientWidth+document.documentElement.scrollLeft) < parseInt(webSnapr.obj.offsetWidth+webSnapr.x) + indentX) {
                        webSnapr.obj.style.left = parseInt(webSnapr.x-(webSnapr.obj.offsetWidth+indentX))+'px';
                        positionClass = "right";
                } else {
                        webSnapr.obj.style.left = (webSnapr.x+indentX)+'px';
                }
                if ( parseInt(document.documentElement.clientHeight+document.documentElement.scrollTop) < parseInt(webSnapr.obj.offsetHeight+webSnapr.y) + indentY ) {
                        webSnapr.obj.style.top = heightIndent;
                        positionClass += "Top";
                } else {
                        webSnapr.obj.style.top = (webSnapr.y + indentY)+'px';
                        positionClass += "Bottom";
                }

                webSnapr.obj.className = positionClass;
                webSnapr.timer = window.setTimeout("webSnapr.showThumb()",500);
        },
        showThumb: function(e) {
                webSnapr.hidden = false;
                webSnapr.obj.style.visibility = webSnapr.ind.style.visibility = 'visible';
                webSnapr.obj.style.opacity = webSnapr.ind.style.opacity = '.1';
                webSnapr.img.style.visibility = "hidden";
                
                var addy = String(webSnapr.lnk.href).replace(/[^:]*:\/\/([^:\/]*)(:{0,1}\/{1}.*)/, '$1');

                webSnapr.errorTimer = window.setTimeout("webSnapr.imageError()",15000);
                webSnapr.img.src = 'http://images.websnapr.com/?url='+ encodeURI(addy)+'&rndm='+parseInt(webSnapr.linkPool[webSnapr.lnk.href]);

                /*@cc_on@*/
                /*@if(@_win32)
                return;
                /*@end@*/
                
                webSnapr.fade(10);
        },
        hideThumb: function(e) {
                webSnapr.hidden = true;
                if(webSnapr.timer) clearTimeout(webSnapr.timer);
                if(webSnapr.errorTimer) clearTimeout(webSnapr.errorTimer);
                if(webSnapr.opacityTimer) clearTimeout(webSnapr.opacityTimer);
                webSnapr.obj.style.visibility = 'hidden';
                webSnapr.ind.style.visibility = 'hidden';
                webSnapr.img.style.visibility = 'hidden';
                webSnapr.ind.className= "imageLoaded";
        },
        fade: function(opac) {
                var passed  = parseInt(opac);
                var newOpac = parseInt(passed+10);
                if ( newOpac < 90 ) {
                        webSnapr.obj.style.opacity = webSnapr.ind.style.opacity = '.'+newOpac;
                        webSnapr.opacityTimer = window.setTimeout("webSnapr.fade('"+newOpac+"')",20);
                } else {
                        webSnapr.obj.style.opacity = webSnapr.ind.style.opacity = '.99';
                }
        },
        addEvent: function( obj, types, fn ) {
                var type;
                for(var i = 0; i < types.length; i++) {
                        type = types[i];
                        if ( obj.attachEvent ) {
                                obj['e'+type+fn] = fn;
                                obj[type+fn] = function(){obj['e'+type+fn]( window.event );}
                                obj.attachEvent( 'on'+type, obj[type+fn] );
                        } else obj.addEventListener( type, fn, false );
                }
        },
        setbaseurl: function(baseurl) {
            webSnapr.baseURL = baseurl;
        },
        setimageuri: function(imageuri) {
            webSnapr.baseURI = imageuri;
        }
}

