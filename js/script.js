jQuery(document).ready(function(){
	jQuery('#pn-table-main').tablesorter({sortList:[[9,1]], widgets: ['zebra']}).tablesorterPager({container: jQuery("#pager")});
	
	jQuery('#search_item').focus(function(){
		var get_term = jQuery('#search_item').val();
		if(get_term == "Filter By Keyword")
			jQuery('#search_item').val('');
	});	
	jQuery('#search_item').blur(function(){
		var get_term = jQuery('#search_item').val();
		if(get_term == "")
			jQuery('#search_item').val('Filter By Keyword');
	});
	jQuery('#your_name').focus(function(){
		var get_term = jQuery('#your_name').val();
		if(get_term == "Your Name")
			jQuery('#your_name').val('');
	});	
	jQuery('#your_name').blur(function(){
		var get_term = jQuery('#your_name').val();
		if(get_term == "")
			jQuery('#your_name').val('Your Name');
	});
	jQuery('#your_email').focus(function(){
		var get_term = jQuery('#your_email').val();
		if(get_term == "Your Email")
			jQuery('#your_email').val('');
	});	
	jQuery('#your_email').blur(function(){
		var get_term = jQuery('#your_email').val();
		if(get_term == "")
			jQuery('#your_email').val('Your Email');
	});	
	jQuery('#html_tmp').focus(function(){
		var get_term = jQuery('#html_tmp').val();
		if(get_term == "Insert Your HTML Code Here")
			jQuery('#html_tmp').val('');
	});	
	jQuery('#html_tmp').blur(function(){
		var get_term = jQuery('#html_tmp').val();
		if(get_term == "")
			jQuery('#html_tmp').val('Insert Your HTML Code Here');
	});
	jQuery('#email_tmp').focus(function(){
		var get_term = jQuery('#email_tmp').val();
		if(get_term == "Insert Your Notification Email Template Here")
			jQuery('#email_tmp').val('');
	});	
	jQuery('#email_tmp').blur(function(){
		var get_term = jQuery('#email_tmp').val();
		if(get_term == "")
			jQuery('#email_tmp').val('Insert Your Notification Email Template Here');
	});	
	jQuery('#pn_email_tmp').focus(function(){
		var get_term = jQuery('#pn_email_tmp').val();
		if(get_term == "Insert Your Registration Email Template Here")
			jQuery('#pn_email_tmp').val('');
	});	
	jQuery('#pn_email_tmp').blur(function(){
		var get_term = jQuery('#pn_email_tmp').val();
		if(get_term == "")
			jQuery('#pn_email_tmp').val('Insert Your Registration Email Template Here');
	});
	jQuery('#tpl_subject_text').focus(function(){
		var get_term = jQuery('#tpl_subject_text').val();
		if(get_term == "Insert Your Registration Email Subject Here")
			jQuery('#tpl_subject_text').val('');
	});	
	jQuery('#tpl_subject_text').blur(function(){
		var get_term = jQuery('#tpl_subject_text').val();
		if(get_term == "")
			jQuery('#tpl_subject_text').val('Insert Your Registration Email Subject Here');
	});
	jQuery('img#info_image').hover(function(){
		jQuery('#test_info').css('display', 'inline-block');
	});
	jQuery('img#info_image').mouseout(function(){
		setTimeout(function(){jQuery('#test_info').fadeOut(50);}, 1000);
	});
	jQuery("#clear_all").click(function(){
		var clear_db = window.confirm("This will erase all notification data from database! Proceed?");
		if(clear_db){return true}
			return false;
	});
	
});
function ofc_resize(left, width, top, height){
    var tmp = new Array(
    'left:'+left,
    'width:'+ width,
    'top:'+top,
    'height:'+height );
}
var index = 10;
function showMoreInfo(table_row){
	jQuery('#mi-'+table_row).show();
	index = index + 1;
	jQuery('#mi-'+table_row).css("z-index", index);
	jQuery('#more-info-'+table_row).html("<a href='javascript: void(0)' title='Hide Info' class='green' onclick='hideMoreInfo("+table_row+")'>Hide</a>");
}
function hideMoreInfo(table_row){
	jQuery('#mi-'+table_row).hide();
	jQuery('#more-info-'+table_row).html("<a href='javascript: void(0)' title='More Info' onclick='showMoreInfo("+table_row+")'>More</a>");
}
	