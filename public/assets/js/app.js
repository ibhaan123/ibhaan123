!function(e){var t={};function a(n){if(t[n])return t[n].exports;var o=t[n]={i:n,l:!1,exports:{}};return e[n].call(o.exports,o,o.exports,a),o.l=!0,o.exports}a.m=e,a.c=t,a.d=function(e,t,n){a.o(e,t)||Object.defineProperty(e,t,{configurable:!1,enumerable:!0,get:n})},a.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return a.d(t,"a",t),t},a.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},a.p="/",a(a.s=0)}({0:function(e,t,a){a("89Mp"),e.exports=a("QOIY")},"89Mp":function(e,t){$(document).ready(function(){var e=function(e,t){return void 0===t&&(t="/twofa"),new Promise(function(a,n){$.ajax({type:"POST",url:base_url+t,data:e,headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},success:function(e){"SUCCESS"==e.status?a(e.message):"ERROR"==e.status&&n(e.message)},error:function(e,t,a){var o=$.parseJSON(e.responseText);void 0!==o.message?n(o.message):n("unknown Error")}})})},t=function(t){return new Promise(function(a,n){$.sweetModal({blocking:!0,theme:$.sweetModal.THEME_LIGHT,content:'<h3 id="msgx">Two Factor Auth</h3><input class="form-control" id="code" name="code" placeholder="'+t.message+'" type="text" />',buttons:{cancel:{label:"Cancel",classes:"redB bordered flat",action:function(){n("Action Cancelled")}},verify:{label:"Verify",classes:"blueB",action:function(){var n=$("#code").val();if(""==n)return $("#msgx").text("Code is Empty"),!1;$("#msgx").text("checking....");var o={password:t.password};return o[t.twofactorAuth]=n,e(o,"/tfa").then(function(e){$("#msgx").text(e),setTimeout(function(){return $("a.sweet-modal-close-link").trigger("click"),a()},1e3)}).catch(function(e){$("#msgx").text(e)}),!1}}}})})},a=function(e,t,a){var n={content:t,theme:$.sweetModal.THEME_LIGHT};if(void 0!==e&&""!=e&&(n.title=e),void 0!==a)switch(a){case"error":n.icon=$.sweetModal.ICON_ERROR;break;case"warning":n.icon=$.sweetModal.ICON_WARNING;break;case"sucess":n.icon=$.sweetModal.ICON_SUCCESS;break;default:n.icon=$.sweetModal.ICON_SUCCESS}$.sweetModal(n)};$(".scrollbar").each(function(e,t){$(t).slimScroll({height:$(t).data("height")})});var n=function(){return new Promise(function(e,t){$.sweetModal({theme:$.sweetModal.THEME_LIGHT,content:'<h4>Login Password</h4> <br> <input class="form-control" id="authorize_password" name="password" placeholder="password" type="password" />',buttons:{cancel:{label:"Cancel",classes:"redB bordered flat"},authorize:{label:"Authorize Action",classes:"",action:function(){var a=$("#authorize_password").val();""==a?t("Password Cannot Be Empty"):e(a)}}}})})};$(document).on("click","a.ajax_link",function(t){t.preventDefault();var a=$(this);if(a.hasClass("verify_phone"))return new Promise(function(t,a){$.sweetModal({blocking:!0,theme:$.sweetModal.THEME_LIGHT,content:'<h6 id="msgx">Verify Your Phone</h6><input class="form-control" id="phone_number" name="phone_number" placeholder="Enter Phone Number" type="text" /><br><input class="form-control" id="code" name="code" placeholder="SMS CODE" type="text" />',buttons:{cancel:{label:"Cancel",classes:"redB bordered flat",action:function(){a("Action Cancelled")}},request:{label:"Get SMS",classes:"greenB code",action:function(){var t=$("#phone_number").val();return""==t?($("#msgx").text("Phone is Empty"),!1):($("#msgx").text("sending...."),e({verify_phone:t},"/verify_phone").then(function(e){$("#msgx").text(e)},function(e){$("#msgx").text(message)}),!1)}},verify:{label:"Verify",classes:"blueB",action:function(){var a=$("#code").val();return""==a?($("#msgx").text("Code is Empty"),!1):($("#msgx").text("checking...."),e({verify_code:a},"/verify_code").then(function(e){$("#msgx").text(e),setTimeout(function(){return $("a.sweet-modal-close-link").trigger("click"),t()},1e3)}).catch(function(e){return $("#msgx").text(e),!1}),!1)}}}})});if(a.hasClass("confirm")){var n=a.data("confirm");a.removeAttr("data-confirm"),$.sweetModal.confirm(n,function(){i(a)})}else i(a);return!0});var o=function(e){for(var t=$(e).serializeArray(),a={},n=0;n<t.length;n++)void 0===a[t[n].name]?a[t[n].name]=t[n].value:(a[t[n].name]instanceof Array||(a[t[n].name]=[a[t[n].name]]),a[t[n].name].push(t[n].value));return a},r=function(e,t){var n=e.attr("inputs");void 0!==n?function(e){e=e.split("::");var t="";return $.each(e,function(e,a){var n=a.split("|"),o="";void 0!==n[2]&&(o=n[2]),t+=" <br><h6>"+n[1]+'</h6><input class="form-control" id="_'+n[0]+'" name="password" placeholder="'+n[1]+'" value="'+o+'" type="text" />'}),new Promise(function(a,n){$.sweetModal({theme:$.sweetModal.THEME_LIGHT,content:t,buttons:{cancel:{label:"Take Me Back",classes:"redB bordered flat"},authorize:{label:"Submit",classes:"",action:function(){var t={};$.each(e,function(e,a){var o=a.split("|");if(t[o[0]]=$("#_"+o[0]).val(),""==$("#_"+o[0]).val())return n(o[1]+" Cannot Be Empty"),!1}),a(t)}}}})})}(n).then(function(a){s(e,$.extend(a,t))}).catch(function(e){""!==e&&setTimeout(function(){a("",e,"error")},300)}):s(e,t)},s=function(e,t){var r;e.hasClass("gateway")?(r=e,new Promise(function(e,t){var a=r.data("gates"),n="",s="",i="Please Credit The Address Below",c={cancel:{label:"Done",classes:"redB bordered flat"}};if(r.hasClass("deposit"))if("App\\Models\\Token"==r.data("ttype")){var l=r.data("address"),u=r.data("amount");s='<span style="font-weight:500; font-size:2.88rem; line-height:1.1">'+u+" "+r.data("symbol")+"</span><p>"+l+'</p><img src="'+base_url+"/qr-code/"+l+"?amount="+u+'">',a.length>0&&(c.authorize={label:"Process Withdrawal",classes:"",action:function(){var t=$("form#gatewayform"),a=o(t);a.password=null,e(a)}})}else"App\\Models\\Country"==r.data("ttype")&&(a.length<1&&t("There Are NO Payment Gatways Specified For this Country. <br> Please Contact Admin"),c.authorize={label:"Process Deposit",classes:"",action:function(){if(a.length>0){var t=$("form#gatewayform"),n=o(t);e(n)}}});else"App\\Models\\Country"==r.data("ttype")&&a.length<1&&t("There Are NO Payment Gatways Specified For Currency. <br> Please Contact Admin"),s='<input class="withdrawto form-control" type="text" name="address" placeholder="Withdraw To Address or Account">',c.authorize={label:"Process Withdrawal",classes:"",action:function(){var t=$("form#gatewayform"),n=o(t);"App\\Models\\Token"==r.data("ttype")&&a.length<1&&(n.gateway="blockchain"),e(n)}};1==a.length?(i="",n+='<input  id="g'+a[0].name+'"  class="'+a[0].name+'" type="hidden" name="gateway" value="'+a[0].name+'" />'):a.length>1&&(i="Select a Payment Method",$.each(a,function(e,t){n+='<input  id="g'+t.name+'"  class="card '+t.name+'" type="radio" name="gateway" value="'+t.name+'" /><label title ="'+t.name+'" class="gateway-cc '+t.name+" img-"+t.name+'" for="g'+t.name+'"></label>'}));var d='<h5 id="gateway_name">'+i+'</h5><form id="gatewayform" name="isaac"><div class=" col-md-12 cc-selector-2">'+n+"</div></form><br>"+s;$.sweetModal({theme:$.sweetModal.THEME_LIGHT,content:d,buttons:c})})).then(function(o){void 0!==o.password&&null==o.password?setTimeout(function(){n().then(function(a){o.password=a,c(e,$.extend(o,t))}).catch(function(e){setTimeout(function(){a("",e,"error")},300)})},300):c(e,$.extend(o,t))}).catch(function(e){setTimeout(function(){a("",e,"error")},300)}):c(e,t)},i=function(e){var t=e.data();$.ajaxSetup({headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")}}),e.hasClass("authorize")?n().then(function(a){t.password=a,setTimeout(function(){r(e,t)},300)}).catch(function(e){setTimeout(function(){a("",e,"error")},300)}):r(e,t)},c=function n(o,r){void 0!==r.toggle&&delete r.toggle;var s=o.attr("href");f(),$.ajaxSetup({headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")}}),$.ajax({type:"POST",url:s,data:r,success:function(s){if(m(),"ERROR"==s.status)return a("",s.message,"error"),!1;if("2FASETUP"==s.status)(function(t){return new Promise(function(a,n){1!=t.verify_twofa&&n(t.message),$.sweetModal({blocking:!0,theme:$.sweetModal.THEME_LIGHT,content:'<p><img src="'+t.inlineUrl+'"></p><p>Scan Qr  with Authenticator. </p><input class="form-control" id="auth_code" name="code" placeholder="Enter Two Factor Authenticator Code" type="text" />',buttons:{cancel:{label:"Cancel",classes:"redB bordered flat",action:function(){n("Action Cancelled")}},verify:{label:"Verify",classes:"blueB",action:function(){var n=$("#auth_code").val();if(""==n)return $("#msgx").text("Auth code is Empty"),!1;$("#msgx").text("checking....");var o={secret:t.secret,code:n};return e(o,"/save_secret").then(function(e){$("#msgx").text(e),setTimeout(function(){return $("a.sweet-modal-close-link").trigger("click"),a()},3e3)}).catch(function(e){$("#msgx").text(e)}),!1}}}})})})(s).then(function(e){setTimeout(function(){return a("",e,"success")},300)}).catch(function(e){setTimeout(function(){return a("",e,"error")},300)});else{if("2FA"==s.status)return s.password=r.password,t(s).then(function(){return n(o,r)}).catch(function(e){setTimeout(function(){return a("",e,"error")},300)});if("OK"==s.status||"SUCCESS"==s.status){if(void 0!==s.file){var i=s.file,c=s.filename,l=new Blob([i],{type:"text/plain;charset=utf-8"});saveAs(l,c+".txt")}if(void 0!==s.html&&"undefined"!==o.data("target")){var u=o.data("target");return $(u).html(s.html),$('a[href="'+u+'"]').tab("show"),!0}if(void 0!==s.update&&$.each(s.update,function(e,t){$("."+e).text(t).val(t)}),void 0!==s.toggle){var d=o.attr("on");d=void 0===d?"ON":d;var f=o.attr("off");f=void 0===f?"OFF":f;var h=o.attr("offclass");h=void 0===h?"btn-danger":h;var p=o.attr("onclass");if(p=void 0===p?"btn-success":p,"Activated"==s.toggle)o.text(d),o.removeAttr("inputs"),o.removeClass(h).removeClass(p).addClass(p);else{o.text(f),o.removeClass(h).removeClass(p).addClass(h);var v=o.data("inputs");void 0!==v&&o.attr("inputs",v)}}if(void 0!==s.URL&&window.setTimeout(function(){window.location.href=s.URL},3e3),void 0!==o.attr("table")&&""!=o.attr("table")){var g=o.attr("table");g=g.split("|"),$.each(g,function(e,t){"undefined"!==window[t]&&window[t].DataTable().draw()})}a("",s.message,"success")}}},error:function(e,t,n){var o=$.parseJSON(e.responseText);if(m(),void 0!==o.message){var r="<span>"+o.message+"</span>";void 0!==o.errors&&$.each(o.errors,function(e,t){t=$.isArray(t)?t[0]:t,r+='<p class="text-danger">'+t+"</p>"});var s={content:r,icon:$.sweetModal.ICON_ERROR,theme:$.sweetModal.THEME_LIGHT};return $.sweetModal(s)}return a("Process Terminated","Indeterminate Error. Internet connection??","error")}})};$("select.leverage").change(function(e){var t=$(this).data();t.leverage=$(this).val();var n=$(this).attr("url");f(),$.ajax({headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},type:"POST",url:n,data:t,success:function(e){if(m(),"ERROR"==e.status)return a("",e.message,"error"),!1;"OK"!=e.status&&"SUCCESS"!=e.status||($(".acc_margin_balance").val(e.margin_balance),a("",e.message,"success"))},error:function(e,t,n){var o=$.parseJSON(e.responseText);if(m(),void 0!==o.message){var r={content:o.message,icon:$.sweetModal.ICON_ERROR,theme:$.sweetModal.THEME_LIGHT};return $.sweetModal(r)}return a("Process Terminated","Indeterminate Error. Internet connection??","error")}})}),$("select.token").change(function(){var e=$(this).children(":selected").attr("fee");"gas"==e?($(".gaslimit").show(),$(".gasprice").attr("placeholder","Gas Price (Optional)")):"txfee"==e&&($(".gaslimit").hide(),$(".gasprice").attr("placeholder","Tx fee (Optional)"))}),$(document).on("submit","form.ajax_form",function(e){e.preventDefault();var t=this;if(!$(t).hasClass("authorize"))return l(t);n().then(function(e){$("#password,.password",$(t)).val(e),setTimeout(function(){return l(t)},300)}).catch(function(e){setTimeout(function(){return a("",e,"error")},300)})});var l=function(e){if(!$(e).hasClass("confirm"))return u(e);(function(e){var t='<h5 class="text-danger">'+$(e).attr("data-confirm")+"</h5>";return $("input.item",e).each(function(e,a){var n=$(this).data("name"),o=$(this).val();t+='<h6 class="text-info">'+n+':&nbsp;&nbsp;&nbsp; <strong class="text-bright">'+o+"</strong></h6>"}),new Promise(function(e,a){$.sweetModal({theme:$.sweetModal.THEME_LIGHT,content:t,buttons:{cancel:{label:"Take Me Back",classes:"redB bordered flat",action:function(){a("Transaction has been cancelled.")}},authorize:{label:"Submit",classes:"",action:function(){e()}}}})})})(e).then(function(){return u(e)}).catch(function(e){setTimeout(function(){return a("",e,"error")},300)})},u=function e(n){var o=$(n),r=o.attr("action");f();var s=o.find(":submit");s.button("loading");var i=new FormData(n);$.ajaxSetup({headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")}}),$.ajax({url:r,type:"POST",data:i,mimeTypes:"multipart/form-data",contentType:!1,cache:!1,processData:!1,success:function(r){if(m(),"ERROR"==r.status)return s.button("reset"),a("",r.message,"error"),!1;if("2FASETUP"==r.status)setup2fa(r).then(function(){return e(n)}).catch(function(e){setTimeout(function(){return a("",e,"error")},300)});else{if("2FA"==r.status)return r.password=i.get("password"),t(r).then(function(){return e(n)}).catch(function(e){setTimeout(function(){return a("",e,"error")},300)});if("OK"==r.status||"SUCCESS"==r.status){if(s.button("reset"),void 0!==r.file){var c=r.file,l=r.filename,u=new Blob([c],{type:"text/plain;charset=utf-8"});saveAs(u,l+".txt")}if(void 0!==r.URL&&window.setTimeout(function(){window.location.href=r.URL},3e3),void 0!==o.data("table")&&""!=o.data("table")){var f=o.data("table");f=f.split("|"),$.each(f,function(e,t){"undefined"!==window[t]&&window[t].DataTable().draw()})}return"tradeForm"==o.attr("id")&&d(),void 0===o.data("edit")&&o.find(":input").not(":button, :submit, :reset, :hidden").removeAttr("checked").removeAttr("selected").not("‌​:checkbox, :radio, select").val(""),a("",r.message,"success")}}},error:function(e,t,n){var r=$.parseJSON(e.responseText);if(m(),s.button("reset"),void 0!==r.message){var i={content:r.message,icon:$.sweetModal.ICON_ERROR,theme:$.sweetModal.THEME_LIGHT,onClose:function(){if(void 0!==r.errors)return $.each(r.errors,function(e,t){t=$.isArray(t)?t[0]:t,$("#"+e,o).notify(t,{position:"bottom"})}),window.grecaptcha&&grecaptcha.reset(),!1}};return $.sweetModal(i)}return void 0!==r.errors?($.each(r.errors,function(e,t){t=$.isArray(t)?t[0]:t,$("#"+e,o).notify(t,{position:"bottom"})}),window.grecaptcha&&grecaptcha.reset(),!1):a("Process Terminated","Indeterminate Error. Internet connection??","error")}})},d=function(){$.ajax({url:$("#myledgerstable").data("url"),cache:!1,type:"POST",headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},success:function(e){$("tbody","#myledgerstable").html(e.html)}})},f=function(e){var t="";if(t=(e=$.extend(!0,{boxed:!0},e)).animate?'<div class="loading-message '+(e.boxed?"loading-message-boxed":"")+'"><div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>':e.iconOnly?'<div class="loading-message '+(e.boxed?"loading-message-boxed":"")+'"><img src="/assets/img/loading-spinner-grey.gif" align=""></div>':e.textOnly?'<div class="loading-message '+(e.boxed?"loading-message-boxed":"")+'"><span>&nbsp;&nbsp;'+(e.message?e.message:"LOADING...")+"</span></div>":'<div class="loading-message '+(e.boxed?"loading-message-boxed":"")+'"><img src="/assets/img/loading-spinner-grey.gif" align=""><span>&nbsp;&nbsp;'+(e.message?e.message:"LOADING...")+"</span></div>",e.target){var a=$(e.target);a.height()<=$(window).height()&&(e.cenrerY=!0),a.block({message:t,baseZ:e.zIndex?e.zIndex:1e3,centerY:void 0!==e.cenrerY&&e.cenrerY,css:{top:"10%",border:"0",padding:"0",backgroundColor:"none"},overlayCSS:{backgroundColor:e.overlayColor?e.overlayColor:"#555",opacity:e.boxed?.05:.1,cursor:"wait"}})}else $.blockUI({message:t,baseZ:e.zIndex?e.zIndex:1e3,css:{border:"0",padding:"0",backgroundColor:"none"},overlayCSS:{backgroundColor:e.overlayColor?e.overlayColor:"#555",opacity:e.boxed?.05:.1,cursor:"wait"}})},m=function(e){e?$(e).unblock({onUnblock:function(){$(e).css("position",""),$(e).css("zoom","")}}):$.unblockUI()};$("#coins").length&&$("#coins").DataTable({ordering:!0,searching:!0,dom:"lfrti",lengthChange:!1,scrollY:450,scroller:{loadingIndicator:!0}}),$("#contract").change(function(e){var t=$(this).val();$(".tokendetail").fadeOut(),$("#d"+t).fadeIn()}),$("#amount").keyup(function(){var e=$(this).val(),t=$("#bonus").attr("bonus"),a=$("#ethereum").attr("rate"),n=parseFloat(e)/parseFloat(a);$("#ethereum").text("ETH "+n.toFixed(8));var o=t*e;$("#bonus").text(o.toFixed(2)),$("a.buynow").attr("data-amount",n),$("a.usewallet").attr("data-amount",n),$("#eth").val(n)}),$(".metamask.buynow").click(function(e){if(e.preventDefault(),"undefined"==typeof web3)return a("Install Metamask","You need to install MetaMask to use this feature.  https://metamask.io","error"),!1;web3.eth.getAccounts(function(e,t){null!=e?console.error("An error occurred: "+e):0==t.length?a("Login in Metamask","You need to Login into MetaMask to use this feature.","error"):l=t[0]});var t=web3.currentProvider,n=new Web3(t),o=$(this).data("chainid"),r=$(this).data("amount"),s=$(this).data("to"),i=$(this);console.log(o,r,s);var l=web3.eth.accounts[0];n.eth.sendTransaction({to:s,from:l,value:web3.toWei(r,"ether"),chainId:o},function(e,t){if(e)return console.log(e.message),a("Tx Failed","Metamask Tx Failed Or Rejected.","error");var n={};n.amount=r,n.token_id=i.data("token_id"),n.tx_hash=t,c(i,n)})}),$(".copy").hover(function(){$(".copy-text",$(this).closest("div")).fadeIn()},function(){$(".copy-text",$(this).closest("div")).fadeOut()}).click(function(){var e=$(".copy-text",$(this).closest("div"));e.attr("data-text","copied"),e.addClass("flash"),clipboard.writeText($(this).data("copythis")),setTimeout(function(){e.removeClass("flash").removeAttr("data-text")},1e3)})})},QOIY:function(e,t){}});