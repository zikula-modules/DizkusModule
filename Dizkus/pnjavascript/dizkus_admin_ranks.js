/**
 *
 * $Id:	dizkus_admin_ranks.js 929 2008-10-25 16:14:11Z Landseer	$
 *
 */

function ShowRankImage(num,	path) 
{
	$('image' +	num).src = path	+ '/' +	$F('rank_image'	+ num);
	return;
}

function ShowNewRankImage(path)	
{
	$('newimage').src =	path + '/' + $F('newrank_image');
	return;
}
