<?php
/*
Plugin Name:    Context Post Segmenter
Description:    Wrap H3 segments in div for presentation.
Text-Domain:    cxt-bs-transformer
Version:        1.0
Author:         Robert Andrews
Author URI:     http://www.robertandrews.co.uk
License:        GPL v2 or later
License URI:    https://www.gnu.org/licenses/gpl-2.0.html
*/

/*
Original contributed by @jack,
https://stackoverflow.com/a/10683463/1375163
*/
add_filter( 'the_content', 'segment_post' );
function segment_post( $content ) {

    $d = new DOMDocument;
    libxml_use_internal_errors(true);
    $d->loadHTML($content);
    libxml_clear_errors();

    $segments = array(); $card = null;

    foreach ($d->getElementsByTagName('h3') as $h3) {
        // first collect all nodes
        $card_nodes = array($h3);
        // iterate until another h3 or no more siblings
        for ($next = $h3->nextSibling; $next && $next->nodeName != 'h3'; $next = $next->nextSibling) {
            $card_nodes[] = $next;
        }

        // create the wrapper node
        $card = $d->createElement('div');
        $card->setAttribute('class', 'card p-4 mb-3');

        // replace the h3 with the new card
        $h3->parentNode->replaceChild($card, $h3);
        // and move all nodes into the newly created card
        foreach ($card_nodes as $node) {
            $card->appendChild($node);
        }
        // keep title of the original h3
        $segments[] = $h3->nodeValue;
    }

    //  make sure we have segments (card is the last inserted card in the dom)
    /*
    if ($segments && $card) {
        $ul = $d->createElement('ul');
        foreach ($segments as $title) {
            $li = $d->createElement('li');

            $a = $d->createElement('a', $title);
            $a->setAttribute('href', '#');

            $li->appendChild($a);
            $ul->appendChild($li);
        }

        // add as sibling of last card added
        $card->parentNode->appendChild($ul);
    }
    */

    // TODO: examine https://stackoverflow.com/questions/10703057/wrap-all-html-tags-between-h3-tag-sets-with-domdocument-in-php

    $content = utf8_decode($d->saveHTML($d->documentElement)); // formatting correction contributed by @Greeso, https://stackoverflow.com/a/20675396/1375163    
    return $content;
}
?>