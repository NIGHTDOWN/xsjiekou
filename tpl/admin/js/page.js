$(function ()
{
    $oe_table_obj = $(".table_cs tr");
    $oe_tr_len = $oe_table_obj.length;
    if ($oe_tr_len > 0)
    {
        for (i = 0; i < $oe_tr_len; i++)
        {
            if (i > 0)
            {
                if (i % 2 == 0)
                {
                    $($oe_table_obj).eq(i).addClass("tr_b");
                }
            }
        }
    }
});
function lbox($title)
{
    msgbox($title, $('#searchbox'),null,null,null,null,'99');
};
function showcall($url, $title)
{
    $html = "<iframe src='" + $url + "' width='600px' height='400px'></iframe>";
    msgbox($title, $html, 700, null, null, null, '99');
}
function framechoose($val)
{
    $p = parent.window.document;
    $jqp=$($p);
    $jqp.find('[name=muid]').val($val);

    parent.window.closebox();

}
//IFrame自动适应内容高度，不出现滚动条onload="SetCwinHeight(this)" 
function SetCwinHeight(iframeObj)
{
    if (document.getElementById)
    {
        if (iframeObj && !window.opera)
        {
            if (iframeObj.contentDocument && iframeObj.contentDocument.body.offsetHeight)
            {
                iframeObj.height = iframeObj.contentDocument.body.offsetHeight;
            } else if (document.frames[iframeObj.name].document && document.frames[iframeObj.name].document.body.scrollHeight)
            {
                iframeObj.height = document.frames[iframeObj.name].document.body.scrollHeight;
            }
        }
    }
}
