/**
 * pnforum.js
 *
 * $Id$
 *
 */

function ShowRankImage(num, path) {

    $('image' + num).src = path + '/' + $F('rank_image' + num);
    return;
}

