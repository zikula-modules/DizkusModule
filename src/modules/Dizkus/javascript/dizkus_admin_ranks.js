/**
 * dizkus_admin_ranks.js
 */
Zikula.define('Dizkus');

Zikula.Dizkus.ShowRankImage = function(num,	path) 
{
	$('image' +	num).src = path	+ '/' +	$F('rank_image'	+ num);}

Zikula.Dizkus.ShowNewRankImage = function(path)	
{
	$('newimage').src =	path + '/' + $F('newrank_image');
}

