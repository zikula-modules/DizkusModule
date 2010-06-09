// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

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

