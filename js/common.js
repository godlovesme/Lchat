/*调试*/
function debug(str){
    window.console.log(str);
}

/*cookie 获取*/
function get_cookie(name){
    var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
    if(arr=document.cookie.match(reg))
        return unescape(arr[2]);
    else
        return null;
}























