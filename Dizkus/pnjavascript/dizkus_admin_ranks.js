/**
 *
 * $Id$
 *
 */

function ShowRankImage(num, path) {

    $('image' + num).src = path + '/' + $F('rank_image' + num);
    return;
}

function ShowNewRankImage(path) {

    $('newimage').src = path + '/' + $F('newrank_image');
    return;
}
